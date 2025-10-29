@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-12">
    <div class="max-w-6xl mx-auto">
        <!-- Updated hero section with emphasis on FDA compliance -->
        <div class="text-center mb-12">
            <h1 class="text-5xl font-bold mb-4 bg-gradient-to-r from-blue-600 to-blue-800 bg-clip-text text-transparent">
                {{ __('messages.first_vietnam_platform_fda_compliant') }}
            </h1>
            <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                {{ __('messages.vexim_global_leading_provider') }}
            </p>
        </div>

        <!-- Added FDA FSMA 204 compliance badges -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-12">
            <div class="bg-blue-50 border-2 border-blue-200 rounded-lg p-6 text-center">
                <div class="text-4xl mb-2">✅</div>
                <div class="font-bold text-blue-800">FDA FSMA 204</div>
                <div class="text-sm text-gray-600">{{ __('messages.fully_compliant') }}</div>
            </div>
            <div class="bg-green-50 border-2 border-green-200 rounded-lg p-6 text-center">
                <div class="text-4xl mb-2">✍️</div>
                <div class="font-bold text-green-800">21 CFR Part 11</div>
                <div class="text-sm text-gray-600">{{ __('messages.e_signatures') }}</div>
            </div>
            <div class="bg-purple-50 border-2 border-purple-200 rounded-lg p-6 text-center">
                <div class="text-4xl mb-2">🌐</div>
                <div class="font-bold text-purple-800">GS1 Standards</div>
                <div class="text-sm text-gray-600">{{ __('messages.global_compatibility') }}</div>
            </div>
            <div class="bg-orange-50 border-2 border-orange-200 rounded-lg p-6 text-center">
                <div class="text-4xl mb-2">🔒</div>
                <div class="font-bold text-orange-800">ISO Certified</div>
                <div class="text-sm text-gray-600">{{ __('messages.security_standards') }}</div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-12">
            <div class="bg-white rounded-lg shadow-md p-8">
                <h2 class="text-2xl font-semibold mb-6 text-blue-800">{{ __('messages.company_information') }}</h2>
                <div class="space-y-4">
                    <div>
                        <p class="text-gray-600 text-sm font-semibold">{{ __('messages.company_name') }}</p>
                        <p class="text-lg font-semibold">{{ $company['name'] }}</p>
                    </div>
                    <div>
                        <p class="text-gray-600 text-sm font-semibold">{{ __('messages.email') }}</p>
                        <p class="text-lg"><a href="mailto:{{ $company['email'] }}" class="text-blue-600 hover:underline">{{ $company['email'] }}</a></p>
                    </div>
                    <div>
                        <p class="text-gray-600 text-sm font-semibold">{{ __('messages.phone') }}</p>
                        <p class="text-lg"><a href="tel:{{ $company['phone'] }}" class="text-blue-600 hover:underline">{{ $company['phone'] }}</a></p>
                    </div>
                    <div>
                        <p class="text-gray-600 text-sm font-semibold">{{ __('messages.address') }}</p>
                        <p class="text-lg">{{ $company['address'] }}</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow-md p-8">
                <h2 class="text-2xl font-semibold mb-6 text-blue-800">{{ __('messages.our_solutions') }}</h2>
                <ul class="space-y-3">
                    <li class="flex items-start">
                        <span class="text-blue-600 mr-3 text-xl">▸</span>
                        <div>
                            <div class="font-semibold">{{ __('messages.fsma_204_traceability_system') }}</div>
                            <div class="text-sm text-gray-600">{{ __('messages.complete_supply_chain_tracking') }}</div>
                        </div>
                    </li>
                    <li class="flex items-start">
                        <span class="text-blue-600 mr-3 text-xl">▸</span>
                        <div>
                            <div class="font-semibold">{{ __('messages.cte_events_management') }}</div>
                            <div class="text-sm text-gray-600">{{ __('messages.receiving_transformation_shipping') }}</div>
                        </div>
                    </li>
                    <li class="flex items-start">
                        <span class="text-blue-600 mr-3 text-xl">▸</span>
                        <div>
                            <div class="font-semibold">{{ __('messages.digital_signatures_21_cfr') }}</div>
                            <div class="text-sm text-gray-600">{{ __('messages.compliant_electronic_signatures') }}</div>
                        </div>
                    </li>
                    <li class="flex items-start">
                        <span class="text-blue-600 mr-3 text-xl">▸</span>
                        <div>
                            <div class="font-semibold">{{ __('messages.data_retention_compliance') }}</div>
                            <div class="text-sm text-gray-600">{{ __('messages.27_month_automatic_retention') }}</div>
                        </div>
                    </li>
                    <li class="flex items-start">
                        <span class="text-blue-600 mr-3 text-xl">▸</span>
                        <div>
                            <div class="font-semibold">{{ __('messages.gs1_barcode_qr_management') }}</div>
                            <div class="text-sm text-gray-600">{{ __('messages.gtin_gln_sscc_support') }}</div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
        
        <!-- Enhanced FSMA 204 explanation section -->
        <div class="bg-gradient-to-r from-blue-50 to-blue-100 border-2 border-blue-300 rounded-lg p-8 mb-8">
            <h2 class="text-3xl font-bold mb-4 text-blue-900">{{ __('messages.about_fsma_204') }}</h2>
            <div class="space-y-4 text-gray-800">
                <p class="text-lg leading-relaxed">
                    {{ __('messages.fsma_204_description_1') }}
                </p>
                <p class="text-lg leading-relaxed">
                    {{ __('messages.fsma_204_description_2') }}
                </p>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6">
                    <div class="bg-white rounded-lg p-4 shadow">
                        <div class="text-2xl mb-2">🔍</div>
                        <div class="font-bold text-blue-800 mb-1">{{ __('messages.complete_traceability') }}</div>
                        <div class="text-sm text-gray-600">{{ __('messages.farm_to_table_tracking') }}</div>
                    </div>
                    <div class="bg-white rounded-lg p-4 shadow">
                        <div class="text-2xl mb-2">⚡</div>
                        <div class="font-bold text-blue-800 mb-1">{{ __('messages.rapid_response') }}</div>
                        <div class="text-sm text-gray-600">{{ __('messages.quick_recall_management') }}</div>
                    </div>
                    <div class="bg-white rounded-lg p-4 shadow">
                        <div class="text-2xl mb-2">📋</div>
                        <div class="font-bold text-blue-800 mb-1">{{ __('messages.regulatory_compliance') }}</div>
                        <div class="text-sm text-gray-600">{{ __('messages.meet_all_fda_requirements') }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Added why choose us section -->
        <div class="bg-white rounded-lg shadow-lg p-8">
            <h2 class="text-3xl font-bold mb-6 text-center text-gray-800">{{ __('messages.why_choose_vexim_global') }}</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="flex items-start gap-4">
                    <div class="bg-blue-100 rounded-full p-3 flex-shrink-0">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-lg mb-1">{{ __('messages.first_in_vietnam') }}</h3>
                        <p class="text-gray-600 text-sm">{{ __('messages.pioneering_fda_compliance_platform') }}</p>
                    </div>
                </div>
                <div class="flex items-start gap-4">
                    <div class="bg-green-100 rounded-full p-3 flex-shrink-0">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-lg mb-1">{{ __('messages.real_time_tracking') }}</h3>
                        <p class="text-gray-600 text-sm">{{ __('messages.instant_traceability_updates') }}</p>
                    </div>
                </div>
                <div class="flex items-start gap-4">
                    <div class="bg-purple-100 rounded-full p-3 flex-shrink-0">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-lg mb-1">{{ __('messages.enterprise_security') }}</h3>
                        <p class="text-gray-600 text-sm">{{ __('messages.bank_level_encryption') }}</p>
                    </div>
                </div>
                <div class="flex items-start gap-4">
                    <div class="bg-orange-100 rounded-full p-3 flex-shrink-0">
                        <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-lg mb-1">{{ __('messages.24_7_support') }}</h3>
                        <p class="text-gray-600 text-sm">{{ __('messages.dedicated_technical_support') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
