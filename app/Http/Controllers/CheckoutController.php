<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckoutController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function createCheckoutSession(Request $request)
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
            return back()->with('error', 'Invalid package selected');
        }

        // Check if Stripe is configured
        if (!env('STRIPE_SECRET')) {
            return back()->with('error', 'Payment system is not configured. Please contact support.');
        }

        try {
            \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
            
            // Determine price based on billing period
            $amount = $validated['billing_period'] === 'monthly' 
                ? $package['monthly_price'] 
                : $package['yearly_price'];
            
            // Create Stripe Checkout Session
            $session = \Stripe\Checkout\Session::create([
                'customer_email' => $user->email,
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'vnd',
                        'product_data' => [
                            'name' => $package['name'],
                            'description' => $package['description'],
                        ],
                        'unit_amount' => $amount * 100, // Convert to cents
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
                    'user_id' => $user->id,
                    'package_id' => $package['id'],
                    'billing_period' => $validated['billing_period'],
                    'max_cte_records' => $package['max_cte_records'],
                    'max_documents' => $package['max_documents'],
                    'max_users' => $package['max_users'],
                ]
            ]);

            return redirect($session->url);
            
        } catch (\Exception $e) {
            \Log::error('Stripe checkout error: ' . $e->getMessage());
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
            
            // Update user's package
            $user = Auth::user();
            $metadata = $session->metadata;
            
            $user->update([
                'package_id' => $metadata->package_id,
                'max_cte_records_monthly' => $metadata->max_cte_records,
                'max_documents' => $metadata->max_documents,
                'max_users' => $metadata->max_users,
                'stripe_customer_id' => $session->customer,
                'stripe_subscription_id' => $session->subscription,
                'subscription_status' => 'active',
                'subscription_ends_at' => null,
            ]);
            
            // Get package details for display
            $packages = $this->getPackages();
            $package = collect($packages)->firstWhere('id', $metadata->package_id);
            
            return view('checkout.success', compact('package'));
            
        } catch (\Exception $e) {
            \Log::error('Checkout success error: ' . $e->getMessage());
            return redirect()->route('pricing')->with('error', 'Unable to confirm payment. Please contact support.');
        }
    }

    public function cancel()
    {
        return redirect()->route('pricing')->with('info', 'Payment cancelled. You can try again anytime.');
    }

    private function getPackages()
    {
        return [
            [
                'id' => 'basic',
                'name' => 'Basic',
                'description' => 'Perfect for small businesses',
                'monthly_price' => 500000,
                'yearly_price' => 5000000,
                'max_cte_records' => 500,
                'max_documents' => 10,
                'max_users' => 1,
            ],
            [
                'id' => 'premium',
                'name' => 'Premium',
                'description' => 'For growing businesses',
                'monthly_price' => 2000000,
                'yearly_price' => 20000000,
                'max_cte_records' => 2500,
                'max_documents' => 0, // unlimited
                'max_users' => 3,
            ],
            [
                'id' => 'enterprise',
                'name' => 'Enterprise',
                'description' => 'For large organizations',
                'monthly_price' => 5000000,
                'yearly_price' => 50000000,
                'max_cte_records' => 5000,
                'max_documents' => 0, // unlimited
                'max_users' => 999999, // unlimited
            ],
        ];
    }
}
