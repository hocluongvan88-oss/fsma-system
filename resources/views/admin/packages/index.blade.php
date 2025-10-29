@extends('layouts.app')

@section('title', __('messages.package_management'))

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    {{-- Modern header with better spacing and typography --}}
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-foreground mb-2">{{ __('messages.package_management') }}</h1>
        <p class="text-muted-foreground">{{ __('messages.manage_pricing_features_packages') }}</p>
    </div>

    @if(session('success'))
    <div class="bg-green-500/10 border border-green-500/20 text-green-600 px-4 py-3 rounded-lg mb-6">
        {{ session('success') }}
    </div>
    @endif

    @if(!isset($packages))
    <div class="bg-red-500/10 border border-red-500/20 text-red-600 px-4 py-3 rounded-lg">
        ERROR: $packages variable is not set!
    </div>
    @elseif($packages->isEmpty())
    <div class="bg-yellow-500/10 border border-yellow-500/20 text-yellow-600 px-4 py-3 rounded-lg">
        No packages found in database. Please seed packages first.
    </div>
    @else
    {{-- Modern grid layout with responsive columns --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        @foreach($packages as $package)
        <div class="bg-card border-2 border-border rounded-xl p-6 hover:border-primary/50 transition-all duration-200 flex flex-col">
            {{-- Package header with badges --}}
            <div class="mb-4">
                <div class="flex items-start justify-between mb-2">
                    <h3 class="text-xl font-bold text-foreground">{{ $package->name }}</h3>
                    @if($package->show_promotion)
                    <span class="bg-primary/10 text-primary text-xs font-semibold px-2 py-1 rounded-full">
                        Popular
                    </span>
                    @endif
                </div>
                
                <div class="flex flex-wrap gap-2 mb-3">
                    @if(!$package->is_visible)
                    <span class="bg-muted text-muted-foreground text-xs font-medium px-2 py-1 rounded">
                        Hidden
                    </span>
                    @endif
                    @if(!$package->is_selectable)
                    <span class="bg-yellow-500/10 text-yellow-600 text-xs font-medium px-2 py-1 rounded">
                        Not Selectable
                    </span>
                    @endif
                </div>

                <p class="text-sm text-muted-foreground line-clamp-2">{{ $package->description }}</p>
            </div>

            {{-- Pricing section with better visual hierarchy --}}
            <div class="mb-6">
                @if($package->monthly_selling_price)
                <div class="flex items-baseline gap-1 mb-1">
                    <span class="text-3xl font-bold text-foreground">${{ number_format($package->monthly_selling_price, 0) }}</span>
                    <span class="text-sm text-muted-foreground">/month</span>
                </div>
                @if($package->monthly_list_price && $package->monthly_list_price > $package->monthly_selling_price)
                <div class="flex items-center gap-2">
                    <span class="text-sm text-muted-foreground line-through">${{ number_format($package->monthly_list_price, 0) }}</span>
                    <span class="text-xs font-semibold text-green-600 bg-green-500/10 px-2 py-0.5 rounded">
                        Save {{ number_format($package->getMonthlyDiscount(), 0) }}%
                    </span>
                </div>
                @endif
                @else
                <div class="text-3xl font-bold text-foreground">Free</div>
                @endif
            </div>

            {{-- Quota limits with icons and better formatting --}}
            <div class="space-y-3 mb-6 flex-grow">
                <div class="flex items-center justify-between text-sm">
                    <span class="text-muted-foreground">CTE Records</span>
                    <span class="font-semibold text-foreground">
                        {{ $package->hasUnlimitedCte() ? '∞' : number_format($package->max_cte_records_monthly) }}
                    </span>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-muted-foreground">Documents</span>
                    <span class="font-semibold text-foreground">
                        {{ $package->hasUnlimitedDocuments() ? '∞' : number_format($package->max_documents) }}
                    </span>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-muted-foreground">Users</span>
                    <span class="font-semibold text-foreground">
                        {{ $package->hasUnlimitedUsers() ? '∞' : $package->max_users }}
                    </span>
                </div>
            </div>

            {{-- Features list with checkmarks --}}
            @if($package->features && count($package->features) > 0)
            <div class="mb-6 pt-4 border-t border-border">
                <ul class="space-y-2">
                    @foreach(array_slice($package->features, 0, 3) as $feature)
                    <li class="flex items-start gap-2 text-sm">
                        <svg class="w-4 h-4 text-green-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="text-muted-foreground">{{ $feature }}</span>
                    </li>
                    @endforeach
                    @if(count($package->features) > 3)
                    <li class="text-xs text-muted-foreground pl-6">
                        +{{ count($package->features) - 3 }} more features
                    </li>
                    @endif
                </ul>
            </div>
            @endif

            {{-- Action button at bottom --}}
            <a href="{{ route('admin.packages.edit', $package) }}" 
               class="w-full bg-primary hover:bg-primary/90 text-primary-foreground font-medium py-2.5 px-4 rounded-lg transition-colors text-center block">
                Edit Package
            </a>
        </div>
        @endforeach
    </div>
    @endif
</div>
@endsection
