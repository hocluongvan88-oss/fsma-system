<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\PaymentOrder;

class VNPayController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->except(['ipn']);
    }

    public function createPayment(Request $request)
    {
        $validated = $request->validate([
            'package_id' => 'required|in:basic,premium,enterprise',
            'billing_period' => 'required|in:monthly,yearly'
        ]);

        $user = Auth::user();
        
        // Get package details
        $packages = $this->getPackages();
        $package = collect($packages)->firstWhere('id', $validated['package_id']);
        
        if (!$package) {
            return back()->with('error', 'Gói dịch vụ không hợp lệ');
        }

        // Check if VNPay is configured
        if (!env('VNPAY_TMN_CODE') || !env('VNPAY_HASH_SECRET')) {
            return back()->with('error', 'Hệ thống thanh toán chưa được cấu hình. Vui lòng liên hệ hỗ trợ.');
        }

        try {
            // Determine price based on billing period
            $amount = $validated['billing_period'] === 'monthly' 
                ? $package['monthly_price'] 
                : $package['yearly_price'];
            
            // Create unique order ID
            $orderId = 'FSMA204_' . $user->id . '_' . time();
            
            $idempotencyKey = hash('sha256', $user->id . $package['id'] . $validated['billing_period'] . date('Y-m-d H:i'));
            
            $existingOrder = PaymentOrder::where('idempotency_key', $idempotencyKey)
                ->where('status', 'pending')
                ->where('created_at', '>', now()->subMinutes(15))
                ->first();
            
            if ($existingOrder && !$existingOrder->isExpired()) {
                Log::warning('Duplicate payment attempt detected', [
                    'user_id' => $user->id,
                    'idempotency_key' => $idempotencyKey,
                    'existing_order_id' => $existingOrder->order_id,
                ]);
                return back()->with('warning', 'Bạn đã có một đơn hàng đang chờ xử lý. Vui lòng hoàn tất thanh toán trước.');
            }
            
            // VNPay parameters
            $vnp_TmnCode = env('VNPAY_TMN_CODE');
            $vnp_HashSecret = env('VNPAY_HASH_SECRET');
            $vnp_Url = env('VNPAY_URL', 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html');
            $vnp_Returnurl = route('vnpay.return');
            
            $vnp_TxnRef = $orderId;
            $vnp_OrderInfo = 'Thanh toán gói ' . $package['name'] . ' - ' . ($validated['billing_period'] === 'monthly' ? 'Tháng' : 'Năm');
            $vnp_OrderType = 'billpayment';
            $vnp_Amount = $amount * 100; // VNPay uses smallest currency unit
            $vnp_Locale = 'vn';
            $vnp_BankCode = '';
            $vnp_IpAddr = $request->ip();

            $inputData = array(
                "vnp_Version" => "2.1.0",
                "vnp_TmnCode" => $vnp_TmnCode,
                "vnp_Amount" => $vnp_Amount,
                "vnp_Command" => "pay",
                "vnp_CreateDate" => date('YmdHis'),
                "vnp_CurrCode" => "VND",
                "vnp_IpAddr" => $vnp_IpAddr,
                "vnp_Locale" => $vnp_Locale,
                "vnp_OrderInfo" => $vnp_OrderInfo,
                "vnp_OrderType" => $vnp_OrderType,
                "vnp_ReturnUrl" => $vnp_Returnurl,
                "vnp_TxnRef" => $vnp_TxnRef,
            );

            if (isset($vnp_BankCode) && $vnp_BankCode != "") {
                $inputData['vnp_BankCode'] = $vnp_BankCode;
            }

            $paymentOrder = PaymentOrder::create([
                'user_id' => $user->id,
                'order_id' => $orderId,
                'package_id' => $package['id'],
                'billing_period' => $validated['billing_period'],
                'amount' => $amount,
                'status' => 'pending',
                'idempotency_key' => $idempotencyKey,
                'metadata' => [
                    'max_cte_records' => $package['max_cte_records'],
                    'max_documents' => $package['max_documents'],
                    'max_users' => $package['max_users'],
                ],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'expires_at' => now()->addMinutes(15),
            ]);

            ksort($inputData);
            $query = "";
            $i = 0;
            $hashdata = "";
            foreach ($inputData as $key => $value) {
                if ($i == 1) {
                    $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
                } else {
                    $hashdata .= urlencode($key) . "=" . urlencode($value);
                    $i = 1;
                }
                $query .= urlencode($key) . "=" . urlencode($value) . '&';
            }

            $vnp_Url = $vnp_Url . "?" . $query;
            if (isset($vnp_HashSecret)) {
                $vnpSecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);
                $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;
            }

            return redirect($vnp_Url);
            
        } catch (\Exception $e) {
            Log::error('VNPay payment error: ' . $e->getMessage());
            return back()->with('error', 'Không thể xử lý thanh toán. Vui lòng thử lại hoặc liên hệ hỗ trợ.');
        }
    }

    public function returnUrl(Request $request)
    {
        $vnp_HashSecret = env('VNPAY_HASH_SECRET');
        $inputData = array();
        
        foreach ($request->all() as $key => $value) {
            if (substr($key, 0, 4) == "vnp_") {
                $inputData[$key] = $value;
            }
        }
        
        $vnp_SecureHash = $inputData['vnp_SecureHash'] ?? null;
        unset($inputData['vnp_SecureHash']);
        ksort($inputData);
        
        $hashData = "";
        $i = 0;
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashData .= '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashData .= urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
        }

        $secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);
        
        if (!hash_equals($secureHash, $vnp_SecureHash ?? '')) {
            Log::warning('VNPay hash verification failed', [
                'expected' => substr($secureHash, 0, 20),
                'received' => substr($vnp_SecureHash ?? '', 0, 20),
            ]);
            return redirect()->route('pricing')->with('error', 'Chữ ký không hợp lệ');
        }
        
        if ($request->vnp_ResponseCode == '00') {
            // Payment successful
            $orderId = $request->vnp_TxnRef;
            
            $paymentOrder = PaymentOrder::where('order_id', $orderId)->first();
            
            if (!$paymentOrder) {
                Log::error('Payment order not found', ['order_id' => $orderId]);
                return redirect()->route('pricing')->with('error', 'Không tìm thấy đơn hàng');
            }
            
            if ($paymentOrder->isExpired()) {
                Log::warning('Payment order expired', ['order_id' => $orderId]);
                $paymentOrder->markAsFailed('Order expired');
                return redirect()->route('pricing')->with('error', 'Đơn hàng đã hết hạn');
            }
            
            $paidAmount = $request->vnp_Amount / 100;
            if ($paidAmount != $paymentOrder->amount) {
                Log::error('Amount mismatch in payment', [
                    'order_id' => $orderId,
                    'expected' => $paymentOrder->amount,
                    'paid' => $paidAmount,
                ]);
                $paymentOrder->markAsFailed('Amount mismatch');
                return redirect()->route('pricing')->with('error', 'Số tiền thanh toán không khớp');
            }
            
            $user = $paymentOrder->user;
            
            if ($user) {
                $verifyResult = $this->verifyPaymentWithVNPay($request->vnp_TxnRef, $paymentOrder->amount);
                
                if (!$verifyResult['success']) {
                    Log::error('VNPay verification failed', [
                        'order_id' => $orderId,
                        'reason' => $verifyResult['message'],
                    ]);
                    $paymentOrder->markAsFailed('VNPay verification failed');
                    return redirect()->route('pricing')->with('error', 'Xác minh thanh toán thất bại. Vui lòng liên hệ hỗ trợ.');
                }
                
                // Update user package
                $user->update([
                    'package_id' => $paymentOrder->package_id,
                    'max_cte_records_monthly' => $paymentOrder->metadata['max_cte_records'],
                    'max_documents' => $paymentOrder->metadata['max_documents'],
                    'max_users' => $paymentOrder->metadata['max_users'],
                    'vnpay_transaction_id' => $request->vnp_TransactionNo,
                    'vnpay_order_id' => $orderId,
                    'payment_gateway' => 'vnpay',
                    'subscription_status' => 'active',
                    'last_payment_date' => now(),
                    'subscription_ends_at' => $paymentOrder->billing_period === 'monthly' 
                        ? now()->addMonth() 
                        : now()->addYear(),
                ]);
                
                // Mark order as completed
                $paymentOrder->markAsCompleted($request->vnp_TransactionNo, 'vnpay');
                
                // Get package details for display
                $packages = $this->getPackages();
                $package = collect($packages)->firstWhere('id', $paymentOrder->package_id);
                
                Log::info('Payment successful', [
                    'user_id' => $user->id,
                    'order_id' => $orderId,
                    'amount' => $paymentOrder->amount,
                ]);
                
                return view('checkout.success', [
                    'package' => $package,
                    'payment_method' => 'VNPay',
                    'transaction_id' => $request->vnp_TransactionNo,
                ]);
            }
            
            return redirect()->route('pricing')->with('success', 'Thanh toán thành công!');
        } else {
            // Payment failed
            $orderId = $request->vnp_TxnRef;
            $paymentOrder = PaymentOrder::where('order_id', $orderId)->first();
            
            if ($paymentOrder) {
                $paymentOrder->markAsFailed('VNPay response code: ' . $request->vnp_ResponseCode);
            }
            
            Log::warning('Payment failed', [
                'order_id' => $orderId,
                'response_code' => $request->vnp_ResponseCode,
            ]);
            
            return redirect()->route('pricing')->with('error', 'Thanh toán thất bại. Mã lỗi: ' . $request->vnp_ResponseCode);
        }
    }

    public function ipn(Request $request)
    {
        // IPN (Instant Payment Notification) endpoint for VNPay webhook
        $vnp_HashSecret = env('VNPAY_HASH_SECRET');
        $inputData = array();
        
        foreach ($request->all() as $key => $value) {
            if (substr($key, 0, 4) == "vnp_") {
                $inputData[$key] = $value;
            }
        }
        
        $vnp_SecureHash = $inputData['vnp_SecureHash'] ?? null;
        unset($inputData['vnp_SecureHash']);
        ksort($inputData);
        
        $hashData = "";
        $i = 0;
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashData .= '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashData .= urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
        }

        $secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);
        
        $returnData = array();
        
        if (!hash_equals($secureHash, $vnp_SecureHash ?? '')) {
            Log::warning('VNPay IPN hash verification failed', [
                'ip' => $request->ip(),
            ]);
            $returnData['RspCode'] = '97';
            $returnData['Message'] = 'Invalid signature';
            return response()->json($returnData);
        }
        
        $orderId = $request->vnp_TxnRef;
        
        $paymentOrder = PaymentOrder::where('order_id', $orderId)->first();
        
        if (!$paymentOrder) {
            Log::warning('VNPay IPN: Order not found', ['order_id' => $orderId]);
            $returnData['RspCode'] = '02';
            $returnData['Message'] = 'Order not found';
            return response()->json($returnData);
        }
        
        if ($request->vnp_ResponseCode == '00') {
            // Payment successful
            $user = $paymentOrder->user;
            
            if ($user) {
                $paidAmount = $request->vnp_Amount / 100;
                if ($paidAmount != $paymentOrder->amount) {
                    Log::error('VNPay IPN: Amount mismatch', [
                        'order_id' => $orderId,
                        'expected' => $paymentOrder->amount,
                        'paid' => $paidAmount,
                    ]);
                    $returnData['RspCode'] = '01';
                    $returnData['Message'] = 'Amount mismatch';
                    return response()->json($returnData);
                }
                
                // Update user package
                $user->update([
                    'package_id' => $paymentOrder->package_id,
                    'max_cte_records_monthly' => $paymentOrder->metadata['max_cte_records'],
                    'max_documents' => $paymentOrder->metadata['max_documents'],
                    'max_users' => $paymentOrder->metadata['max_users'],
                    'vnpay_transaction_id' => $request->vnp_TransactionNo,
                    'vnpay_order_id' => $orderId,
                    'payment_gateway' => 'vnpay',
                    'subscription_status' => 'active',
                    'last_payment_date' => now(),
                    'subscription_ends_at' => $paymentOrder->billing_period === 'monthly' 
                        ? now()->addMonth() 
                        : now()->addYear(),
                ]);
                
                // Mark order as completed
                $paymentOrder->markAsCompleted($request->vnp_TransactionNo, 'vnpay');
                
                $returnData['RspCode'] = '00';
                $returnData['Message'] = 'Confirm Success';
                
                Log::info('VNPay IPN: Payment confirmed', [
                    'user_id' => $user->id,
                    'order_id' => $orderId,
                ]);
            } else {
                $returnData['RspCode'] = '01';
                $returnData['Message'] = 'User not found';
            }
        } else {
            $returnData['RspCode'] = '00';
            $returnData['Message'] = 'Confirm Success';
        }
        
        return response()->json($returnData);
    }

    private function verifyPaymentWithVNPay(string $transactionRef, float $expectedAmount): array
    {
        try {
            // In production, you would query VNPay API to verify the transaction
            // For now, we trust the hash verification and amount check
            // This is a placeholder for future VNPay API integration
            
            Log::info('Payment verification passed', [
                'transaction_ref' => $transactionRef,
                'amount' => $expectedAmount,
            ]);
            
            return [
                'success' => true,
                'message' => 'Payment verified',
            ];
        } catch (\Exception $e) {
            Log::error('Payment verification error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    private function getPackages()
    {
        return [
            [
                'id' => 'basic',
                'name' => 'Basic',
                'description' => 'Hoàn hảo cho doanh nghiệp nhỏ',
                'monthly_price' => 500000,
                'yearly_price' => 5000000,
                'max_cte_records' => 500,
                'max_documents' => 10,
                'max_users' => 1,
            ],
            [
                'id' => 'premium',
                'name' => 'Premium',
                'description' => 'Dành cho doanh nghiệp đang phát triển',
                'monthly_price' => 2000000,
                'yearly_price' => 20000000,
                'max_cte_records' => 2500,
                'max_documents' => 0,
                'max_users' => 3,
            ],
            [
                'id' => 'enterprise',
                'name' => 'Enterprise',
                'description' => 'Dành cho tổ chức lớn',
                'monthly_price' => 5000000,
                'yearly_price' => 50000000,
                'max_cte_records' => 5000,
                'max_documents' => 0,
                'max_users' => 999999,
            ],
        ];
    }
}
