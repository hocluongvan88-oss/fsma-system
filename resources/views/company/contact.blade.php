@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-12">
    <div class="max-w-2xl mx-auto">
        <h1 class="text-4xl font-bold mb-8">Contact Us</h1>
        
        <div class="bg-white rounded-lg shadow-md p-8 mb-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div>
                    <h2 class="text-xl font-semibold mb-6">Get in Touch</h2>
                    
                    <div class="space-y-6">
                        <div>
                            <p class="text-gray-600 text-sm font-semibold mb-2">Email</p>
                            <p><a href="mailto:{{ $company['email'] }}" class="text-blue-600 hover:underline text-lg">{{ $company['email'] }}</a></p>
                        </div>
                        
                        <div>
                            <p class="text-gray-600 text-sm font-semibold mb-2">Phone</p>
                            <p><a href="tel:{{ $company['phone'] }}" class="text-blue-600 hover:underline text-lg">{{ $company['phone'] }}</a></p>
                        </div>
                        
                        <div>
                            <p class="text-gray-600 text-sm font-semibold mb-2">Address</p>
                            <p class="text-gray-800">{{ $company['address'] }}</p>
                        </div>
                        
                        <div>
                            <p class="text-gray-600 text-sm font-semibold mb-2">Company</p>
                            <p class="text-gray-800">{{ $company['name'] }}</p>
                        </div>
                    </div>
                </div>
                
                <div>
                    <h2 class="text-xl font-semibold mb-6">Business Hours</h2>
                    <div class="space-y-3 text-gray-700">
                        <p><strong>Monday - Friday:</strong> 8:00 AM - 6:00 PM (Vietnam Time)</p>
                        <p><strong>Saturday:</strong> 9:00 AM - 1:00 PM (Vietnam Time)</p>
                        <p><strong>Sunday:</strong> Closed</p>
                    </div>
                    
                    <div class="mt-8 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                        <p class="text-sm text-gray-700">
                            For urgent matters, please call us during business hours or send an email and we will respond as soon as possible.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
