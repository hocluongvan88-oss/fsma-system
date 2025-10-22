@extends('emails.layout')

@section('title', 'Nâng cấp thành công')

@section('header-title', '🎉 Nâng cấp thành công')

@section('content')
    <div class="greeting">
        Xin chào {{ $userName }},
    </div>
    
    <div class="content">
        <p>Chúc mừng! Bạn đã nâng cấp thành công lên gói <strong>{{ $planName }}</strong>.</p>
    </div>
    
    <div class="success-box">
        <p><strong>✅ Thành công:</strong> Tài khoản của bạn đã được nâng cấp và các tính năng mới đã được kích hoạt.</p>
    </div>
    
    <div class="stats-box">
        <div class="label">Dung lượng mới</div>
        <div class="value" style="color: #28a745;">{{ $newQuota }} MB</div>
        <div class="subtext">Gói {{ $planName }}</div>
    </div>
    
    <div class="content">
        <p><strong>Những gì bạn nhận được:</strong></p>
        <ul style="padding-left: 20px; margin-top: 10px;">
            <li>Dung lượng lưu trữ: <strong>{{ $newQuota }} MB</strong></li>
            <li>Tốc độ tải lên nhanh hơn</li>
            <li>Hỗ trợ ưu tiên 24/7</li>
            <li>Các tính năng cao cấp khác</li>
        </ul>
    </div>
    
    <div class="button-container">
        <a href="{{ $dashboardUrl }}" class="button">Truy cập Dashboard</a>
    </div>
    
    <div class="divider"></div>
    
    <div class="content" style="font-size: 14px; color: #888888;">
        <p>Cảm ơn bạn đã tin tưởng và sử dụng dịch vụ của chúng tôi!</p>
        <p style="margin-top: 10px;">Nếu có bất kỳ thắc mắc nào, đừng ngần ngại liên hệ với chúng tôi.</p>
    </div>
@endsection
