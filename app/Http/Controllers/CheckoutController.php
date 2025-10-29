<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Package;
use App\Models\Organization;
use App\Models\PaymentOrder;
use App\Services\CTEQuotaSyncService;

class CheckoutController extends Controller
{
    protected $quotaSyncService;

    public function __construct(CTEQuotaSyncService $quotaSyncService)
    {
        $this->middleware('auth');
        $this->quotaSyncService = $quotaSyncService;
    }

    public function createCheckoutSession(Request $request)
    {
        $validated = $request->validate([
            'package_id' => 'required|string|exists:packages,id',
            'billing_period' => 'required|in:monthly,yearly'
        ]);

        $user = Auth::user();
        $organization = $user->organization;
        
        if (!$organization) {
            return back()->with('error', 'User must belong to an organization.');
        }

        $package = Package::where('id', $validated['package_id'])
            ->where('is_visible', true)
            ->where('is_selectable', true)
            ->first();
        
        if (!$package) {
            return back()->with('error', 'Invalid or unavailable package selected');
        }

        $packageService = app(\App\Services\PackageService::class);
        $canChange = $packageService->canChangePackage($organization, $package->id);
        
        if (!$canChange['can_change']) {
            $violations = implode(', ', $canChange['violations']);
            return back()->with('error', "Cannot change to this package: {$violations}");
        }

        // Check if Stripe is configured
        if (!env('STRIPE_SECRET')) {
            return back()->with('error', 'Payment system is not configured. Please contact support.');
        }

        $amount = $validated['billing_period'] === 'monthly' 
            ? $package->monthly_selling_price 
            : $package->yearly_selling_price;
        
        if (!$amount || $amount <= 0) {
            return back()->with('error', 'Invalid pricing for selected package');
        }

        try {
            \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
            
            $idempotencyKey = "checkout_{$organization->id}_{$package->id}_{$validated['billing_period']}_" . now()->timestamp;
            
            $session = \Stripe\Checkout\Session::create([
                'customer_email' => $user->email,
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'vnd',
                        'product_data' => [
                            'name' => $package->name,
                            'description' => $package->description,
                        ],
                        'unit_amount' => (int)($amount * 100),
                        'recurring' => [
                            'interval' => $validated['billing_period'] === 'monthly' ? 'month' : 'year',
                        ],
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'subscription',
                'success_url' => route('checkout.success') . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('checkout.cancel'),
                'metadata' => [
                    'organization_id' => $organization->id,
                    'package_id' => $package->id,
                    'billing_period' => $validated['billing_period'],
                    'user_id' => $user->id,
                ]
            ], [
                'Idempotency-Key' => $idempotencyKey
            ]);

            PaymentOrder::create([
                'organization_id' => $organization->id,
                'user_id' => $user->id,
                'package_id' => $package->id,
                'billing_period' => $validated['billing_period'],
                'amount' => $amount,
                'currency' => 'vnd',
                'stripe_session_id' => $session->id,
                'status' => 'pending',
                'metadata' => [
                    'idempotency_key' => $idempotencyKey,
                ]
            ]);

            return redirect($session->url);
            
        } catch (\Exception $e) {
            \Log::error('Stripe checkout error: ' . $e->getMessage(), [
                'organization_id' => $organization->id,
                'package_id' => $package->id,
            ]);
            return back()->with('error', 'Unable to process payment. Please try again or contact support.');
        }
    }

    public function success(Request $request)
    {
        $sessionId = $request->get('session_id');
        
        if (!$sessionId) {
            return redirect()->route('pricing')->with('error', 'Invalid session');
        }

        try {
            \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
            $session = \Stripe\Checkout\Session::retrieve($sessionId);
            
            $user = Auth::user();
            $organization = $user->organization;
            $metadata = $session->metadata;
            
            if (!$organization) {
                return redirect()->route('pricing')->with('error', 'Organization not found.');
            }

            DB::beginTransaction();
            try {
                // Verify organization ownership
                if ($organization->id != $metadata->organization_id) {
                    throw new \Exception('Organization mismatch');
                }

                // Verify package exists and is valid
                $package = Package::where('id', $metadata->package_id)
                    ->where('is_visible', true)
                    ->where('is_selectable', true)
                    ->first();
                
                if (!$package) {
                    throw new \Exception('Invalid package');
                }

                // Update organization package
                $organization->update([
                    'package_id' => $metadata->package_id,
                ]);

                // Sync quotas with new package (atomic within transaction)
                $this->quotaSyncService->syncOrganizationQuota($organization);

                // Update payment order status
                PaymentOrder::where('stripe_session_id', $sessionId)
                    ->update([
                        'status' => 'completed',
                        'completed_at' => now(),
                    ]);

                DB::commit();

                \Log::info('Payment completed and quotas synced', [
                    'organization_id' => $organization->id,
                    'package_id' => $metadata->package_id,
                    'session_id' => $sessionId,
                ]);
                
                return view('checkout.success', compact('package'));
                
            } catch (\Exception $e) {
                DB::rollBack();
                
                // Mark payment order as failed
                PaymentOrder::where('stripe_session_id', $sessionId)
                    ->update([
                        'status' => 'failed',
                        'error_message' => $e->getMessage(),
                    ]);

                \Log::error('Checkout success processing failed: ' . $e->getMessage(), [
                    'organization_id' => $organization->id,
                    'session_id' => $sessionId,
                ]);
                
                throw $e;
            }
            
        } catch (\Exception $e) {
            \Log::error('Checkout success error: ' . $e->getMessage());
            return redirect()->route('pricing')->with('error', 'Unable to confirm payment. Please contact support.');
        }
    }

    public function cancel()
    {
        return redirect()->route('pricing')->with('info', 'Payment cancelled. You can try again anytime.');
    }
}
