@extends('emails.layout')

@section('title', 'Cảnh báo dung lượng')

@section('header-title', '⚠️ Cảnh báo dung lượng')

@section('content')
    <div class="greeting">
        Xin chào {{ $userName }},
    </div>
    
    <div class="content">
        <p>Chúng tôi nhận thấy bạn đã sử dụng <strong>{{ $percentage }}%</strong> dung lượng của gói hiện tại.</p>
    </div>
    
    <div class="warning-box">
        <p><strong>⚠️ Lưu ý:</strong> Khi đạt 100% dung lượng, bạn sẽ không thể tải lên thêm file mới.</p>
    </div>
    
    <div class="stats-box">
        <div class="label">Dung lượng đã sử dụng</div>
        <div class="value">{{ $usedQuota }} MB</div>
        <div class="subtext">trên tổng số {{ $totalQuota }} MB</div>
        
        <div class="progress-bar">
            <div class="progress-fill" style="width: {{ $percentage }}%"></div>
        </div>
    </div>
    
    <div class="content">
        <p><strong>Đề xuất:</strong></p>
        <ul style="padding-left: 20px; margin-top: 10px;">
            <li>Xóa các file không cần thiết để giải phóng dung lượng</li>
            <li>Nâng cấp lên gói cao hơn để có thêm dung lượng</li>
            <li>Liên hệ hỗ trợ nếu bạn cần tư vấn</li>
        </ul>
    </div>
    
    <div class="button-container">
        <a href="{{ $upgradeUrl }}" class="button">Nâng cấp ngay</a>
    </div>
    
    <div class="divider"></div>
    
    <div class="content" style="font-size: 14px; color: #888888;">
        <p>Cảm ơn bạn đã sử dụng dịch vụ của chúng tôi!</p>
    </div>
@endsection
