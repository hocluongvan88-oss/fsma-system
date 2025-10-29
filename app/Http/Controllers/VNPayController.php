<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\PaymentOrder;
use App\Models\Package;
use App\Services\CTEQuotaSyncService;

class VNPayController extends Controller
{
    protected $quotaSyncService;

    public function __construct(CTEQuotaSyncService $quotaSyncService)
    {
        $this->middleware('auth')->except(['ipn']);
        $this->quotaSyncService = $quotaSyncService;
    }

    public function createPayment(Request $request)
    {
        $validated = $request->validate([
            'package_id' => 'required|in:basic,premium,enterprise',
            'billing_period' => 'required|in:monthly,yearly'
        ]);

        $user = Auth::user();
        $organization = $user->organization;
        
        if (!$organization) {
            return back()->with('error', 'Người dùng phải thuộc một tổ chức.');
        }
        
        // Get package details
        $package = Package::where('id', $validated['package_id'])->first();
        
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
                ? $package->monthly_selling_price 
                : $package->yearly_selling_price;
            
            // Create unique order ID
            $orderId = 'FSMA204_' . $organization->id . '_' . time();
            
            $idempotencyKey = hash('sha256', $organization->id . $package->id . $validated['billing_period'] . date('Y-m-d H:i'));
            
            $existingOrder = PaymentOrder::where('idempotency_key', $idempotencyKey)
                ->where('status', 'pending')
                ->where('created_at', '>', now()->subMinutes(15))
                ->first();
            
            if ($existingOrder && !$existingOrder->isExpired()) {
                Log::warning('Duplicate payment attempt detected', [
                    'organization_id' => $organization->id,
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
            $vnp_OrderInfo = 'Thanh toán gói ' . $package->name . ' - ' . ($validated['billing_period'] === 'monthly' ? 'Tháng' : 'Năm');
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
                'package_id' => $package->id,
                'billing_period' => $validated['billing_period'],
                'amount' => $amount,
                'status' => 'pending',
                'idempotency_key' => $idempotencyKey,
                'metadata' => [
                    'organization_id' => $organization->id,
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
            $organization = $user->organization;
            
            if ($user && $organization) {
                $verifyResult = $this->verifyPaymentWithVNPay($request->vnp_TxnRef, $paymentOrder->amount);
                
                if (!$verifyResult['success']) {
                    Log::error('VNPay verification failed', [
                        'order_id' => $orderId,
                        'reason' => $verifyResult['message'],
                    ]);
                    $paymentOrder->markAsFailed('VNPay verification failed');
                    return redirect()->route('pricing')->with('error', 'Xác minh thanh toán thất bại. Vui lòng liên hệ hỗ trợ.');
                }
                
                $organization->update([
                    'package_id' => $paymentOrder->package_id,
                ]);
                
                // Sync quotas with new package
                try {
                    $this->quotaSyncService->syncOrganizationQuota($organization->id);
                    Log::info('Quotas synced after VNPay payment', [
                        'organization_id' => $organization->id,
                        'package_id' => $paymentOrder->package_id,
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to sync quotas after VNPay payment: ' . $e->getMessage(), [
                        'organization_id' => $organization->id,
                    ]);
                }
                
                // Mark order as completed
                $paymentOrder->markAsCompleted($request->vnp_TransactionNo, 'vnpay');
                
                // Get package details for display
                $package = Package::withoutGlobalScope(\App\Traits\HasOrganizationScope::class)
                    ->where('id', $paymentOrder->package_id)
                    ->first();
                
                Log::info('Payment successful', [
                    'organization_id' => $organization->id,
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
            $organization = $user->organization;
            
            if ($user && $organization) {
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
                
                $organization->update([
                    'package_id' => $paymentOrder->package_id,
                ]);
                
                // Sync quotas with new package
                try {
                    $this->quotaSyncService->syncOrganizationQuota($organization->id);
                    Log::info('Quotas synced after VNPay IPN', [
                        'organization_id' => $organization->id,
                        'package_id' => $paymentOrder->package_id,
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to sync quotas after VNPay IPN: ' . $e->getMessage(), [
                        'organization_id' => $organization->id,
                    ]);
                }
                
                // Mark order as completed
                $paymentOrder->markAsCompleted($request->vnp_TransactionNo, 'vnpay');
                
                $returnData['RspCode'] = '00';
                $returnData['Message'] = 'Confirm Success';
                
                Log::info('VNPay IPN: Payment confirmed', [
                    'organization_id' => $organization->id,
                    'order_id' => $orderId,
                ]);
            } else {
                $returnData['RspCode'] = '01';
                $returnData['Message'] = 'User or organization not found';
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
}
