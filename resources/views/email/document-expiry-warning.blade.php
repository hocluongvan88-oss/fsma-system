@extends('email.layout')

@section('header-title', __('messages.document_expiry_warning'))

@section('content')
    <div style="font-size: 16px; font-weight: bold; margin-bottom: 15px; color: #1f2937;">
        {{ __('messages.hello') }} {{ $userName }},
    </div>
    
    <div style="font-size: 14px; line-height: 1.6; color: #4b5563; margin-bottom: 20px;">
        <p style="margin: 10px 0;">{{ __('messages.document_expiring_soon', ['days' => $daysUntilExpiry]) }}</p>
    </div>
    
    @if($daysUntilExpiry <= 7)
        <div style="background-color: #fee2e2; border-left: 4px solid #dc2626; padding: 15px; margin: 20px 0; border-radius: 4px;">
            <p style="margin: 8px 0; color: #7f1d1d;"><strong>{{ __('messages.urgent') }}:</strong> {{ __('messages.document_expires_in_days', ['days' => $daysUntilExpiry]) }}</p>
        </div>
    @else
        <div style="background-color: #fef3c7; border-left: 4px solid #f59e0b; padding: 15px; margin: 20px 0; border-radius: 4px;">
            <p style="margin: 8px 0; color: #92400e;"><strong>{{ __('messages.warning') }}:</strong> {{ __('messages.document_expires_in_days', ['days' => $daysUntilExpiry]) }}</p>
        </div>
    @endif
    
    <div style="background-color: #eff6ff; border-left: 4px solid #1e40af; padding: 15px; margin: 20px 0; border-radius: 4px;">
        <div style="font-weight: bold; color: #1f2937; margin-bottom: 8px;">{{ __('messages.document_details') }}</div>
        <p style="margin: 8px 0;"><strong>{{ __('messages.title') }}:</strong> {{ $documentTitle }}</p>
        <p style="margin: 8px 0;"><strong>{{ __('messages.doc_number') }}:</strong> {{ $docNumber }}</p>
        <p style="margin: 8px 0;"><strong>{{ __('messages.expiry_date') }}:</strong> {{ $expiryDate->format('F d, Y') }}</p>
        <p style="margin: 8px 0;"><strong>{{ __('messages.days_remaining') }}:</strong> <span style="color: #dc2626; font-weight: bold;">{{ $daysUntilExpiry }} {{ __('messages.days') }}</span></p>
    </div>
    
    <div style="font-size: 14px; line-height: 1.6; color: #4b5563; margin-bottom: 20px;">
        <p style="margin: 10px 0;"><strong>{{ __('messages.recommended_actions') }}:</strong></p>
        <ul style="padding-left: 20px; margin-top: 10px; list-style: none;">
            <li style="margin-bottom: 8px; padding-left: 20px; position: relative;">
                <span style="position: absolute; left: 0; color: #1e40af;">▸</span>
                {{ __('messages.review_document') }}
            </li>
            <li style="margin-bottom: 8px; padding-left: 20px; position: relative;">
                <span style="position: absolute; left: 0; color: #1e40af;">▸</span>
                {{ __('messages.extend_expiry_date') }}
            </li>
            <li style="margin-bottom: 8px; padding-left: 20px; position: relative;">
                <span style="position: absolute; left: 0; color: #1e40af;">▸</span>
                {{ __('messages.archive_if_not_needed') }}
            </li>
        </ul>
    </div>
    
    <div style="text-align: center; margin: 30px 0;">
        <a href="{{ $documentUrl }}" style="display: inline-block; padding: 12px 30px; background-color: #1e40af; color: #ffffff; text-decoration: none; border-radius: 4px; font-weight: bold; font-size: 14px; transition: background-color 0.3s;">{{ __('messages.view_document') }}</a>
    </div>
@endsection
