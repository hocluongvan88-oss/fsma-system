@extends('layouts.app')

@section('title', 'Payment Successful')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8 text-center">
        <div>
            <div class="mx-auto flex items-center justify-center h-24 w-24 rounded-full bg-green-100">
                <svg class="h-16 w-16 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            <h2 class="mt-6 text-3xl font-extrabold text-gray-900">
                Payment Successful!
            </h2>
            <p class="mt-2 text-sm text-gray-600">
                Your subscription to <strong>{{ $package['name'] }}</strong> has been activated.
            </p>
            {{-- Add payment method and transaction ID display --}}
            @if(isset($paymentMethod))
            <p class="mt-1 text-xs text-gray-500">
                Payment Method: <strong>{{ $paymentMethod === 'vnpay' ? 'VNPay' : 'Stripe' }}</strong>
                @if(isset($transactionId))
                    | Transaction ID: <strong>{{ $transactionId }}</strong>
                @endif
            </p>
            @endif
        </div>

        <div class="mt-8 bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                    Your New Plan Includes:
                </h3>
                <dl class="space-y-3">
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-500">CTE Records per Month:</dt>
                        <dd class="text-sm font-medium text-gray-900">
                            {{ $package['max_cte_records'] == 0 ? 'Unlimited' : number_format($package['max_cte_records']) }}
                        </dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-500">Document Storage:</dt>
                        <dd class="text-sm font-medium text-gray-900">
                            {{ $package['max_documents'] == 0 ? 'Unlimited' : $package['max_documents'] }}
                        </dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-500">Team Members:</dt>
                        <dd class="text-sm font-medium text-gray-900">
                            {{ $package['max_users'] >= 999999 ? 'Unlimited' : $package['max_users'] }}
                        </dd>
                    </div>
                </dl>
            </div>
        </div>

        <div class="mt-6 space-y-3">
            <a href="{{ route('dashboard') }}" 
               class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                Go to Dashboard
            </a>
            <p class="text-xs text-gray-500">
                A confirmation email has been sent to your email address.
            </p>
        </div>
    </div>
</div>
@endsection
