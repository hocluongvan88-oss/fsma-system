<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\WebhookLog;
use App\Models\Organization;
use App\Models\PaymentOrder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StripeWebhookController extends Controller
{
    public function handleWebhook(Request $request)
    {
        \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
        
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        
        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload, 
                $sigHeader, 
                env('STRIPE_WEBHOOK_SECRET')
            );
        } catch (\Exception $e) {
            Log::error('Stripe webhook signature verification failed: ' . $e->getMessage());
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        $existingLog = WebhookLog::findByEventId($event->id);
        
        if ($existingLog && $existingLog->status === 'processed') {
            Log::info('Stripe webhook already processed', [
                'event_id' => $event->id,
                'event_type' => $event->type,
            ]);
            return response()->json(['status' => 'already_processed']);
        }
        
        if (!$existingLog) {
            $existingLog = WebhookLog::create([
                'gateway' => 'stripe',
                'event_id' => $event->id,
                'event_type' => $event->type,
                'payload' => $event->data,
                'ip_address' => $request->ip(),
                'status' => 'pending',
            ]);
        }

        try {
            // Handle the event
            switch ($event->type) {
                case 'customer.subscription.created':
                case 'customer.subscription.updated':
                    $this->handleSubscriptionUpdate($event->data->object);
                    break;
                    
                case 'customer.subscription.deleted':
                    $this->handleSubscriptionCanceled($event->data->object);
                    break;
                    
                case 'invoice.payment_succeeded':
                    $this->handlePaymentSucceeded($event->data->object);
                    break;
                    
                case 'invoice.payment_failed':
                    $this->handlePaymentFailed($event->data->object);
                    break;
                    
                default:
                    Log::info('Unhandled Stripe webhook event: ' . $event->type);
            }
            
            $existingLog->markAsProcessed([
                'event_type' => $event->type,
                'processed_at' => now(),
            ]);
            
            return response()->json(['status' => 'success']);
            
        } catch (\Exception $e) {
            Log::error('Stripe webhook processing error: ' . $e->getMessage(), [
                'event_id' => $event->id,
                'event_type' => $event->type,
            ]);
            
            $existingLog->markAsFailed($e->getMessage());
            
            return response()->json(['error' => 'Processing failed'], 500);
        }
    }

    private function handleSubscriptionUpdate($subscription)
    {
        $user = User::where('stripe_subscription_id', $subscription->id)->first();
        
        if ($user) {
            $user->update([
                'subscription_status' => $subscription->status,
                'subscription_ends_at' => $subscription->current_period_end 
                    ? date('Y-m-d H:i:s', $subscription->current_period_end) 
                    : null
            ]);
            
            if ($user->organization) {
                try {
                    $quotaSyncService = app(\App\Services\CTEQuotaSyncService::class);
                    $quotaSyncService->syncOrganizationQuota($user->organization);
                    Log::info('Organization quota synced after subscription update', [
                        'organization_id' => $user->organization->id,
                        'subscription_id' => $subscription->id,
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to sync quota after subscription update', [
                        'organization_id' => $user->organization->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
            
            Log::info('Subscription updated for user: ' . $user->email);
        }
    }

    private function handleSubscriptionCanceled($subscription)
    {
        $user = User::where('stripe_subscription_id', $subscription->id)->first();
        
        if ($user && $user->organization) {
            DB::beginTransaction();
            try {
                $user->organization->update([
                    'package_id' => 'free',
                ]);

                // Sync quotas to free package limits
                $quotaSyncService = app(\App\Services\CTEQuotaSyncService::class);
                $quotaSyncService->syncOrganizationQuota($user->organization);

                DB::commit();
                
                Log::info('Subscription canceled and organization downgraded to free', [
                    'organization_id' => $user->organization->id,
                    'subscription_id' => $subscription->id,
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Failed to handle subscription cancellation', [
                    'organization_id' => $user->organization->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    private function handlePaymentSucceeded($invoice)
    {
        $paymentOrder = PaymentOrder::where('stripe_invoice_id', $invoice->id)->first();
        
        if ($paymentOrder) {
            $paymentOrder->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);
            
            Log::info('Payment order marked as completed', [
                'payment_order_id' => $paymentOrder->id,
                'invoice_id' => $invoice->id,
            ]);
        }

        Log::info('Payment succeeded', [
            'invoice_id' => $invoice->id,
            'customer' => $invoice->customer,
            'amount' => $invoice->amount_paid / 100,
        ]);
    }

    private function handlePaymentFailed($invoice)
    {
        $user = User::where('stripe_customer_id', $invoice->customer)->first();
        
        $paymentOrder = PaymentOrder::where('stripe_invoice_id', $invoice->id)->first();
        
        if ($paymentOrder) {
            $paymentOrder->update([
                'status' => 'failed',
                'error_message' => 'Payment failed from Stripe webhook',
            ]);
        }
        
        if ($user) {
            Log::warning('Payment failed for user: ' . $user->email, [
                'invoice_id' => $invoice->id,
                'amount' => $invoice->amount_due / 100,
            ]);
        }
    }
}
