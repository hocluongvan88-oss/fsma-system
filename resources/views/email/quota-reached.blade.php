@extends('emails.layout')

@section('title', 'Đã hết dung lượng')

@section('header-title', '🚨 Đã hết dung lượng')

@section('content')
    <div class="greeting">
        Xin chào {{ $userName }},
    </div>
    
    <div class="content">
        <p>Tài khoản của bạn đã sử dụng hết <strong>100%</strong> dung lượng được cấp.</p>
    </div>
    
    <div class="danger-box">
        <p><strong>🚨 Cảnh báo:</strong> Bạn không thể tải lên file mới cho đến khi giải phóng dung lượng hoặc nâng cấp gói.</p>
    </div>
    
    <div class="stats-box">
        <div class="label">Dung lượng hiện tại</div>
        <div class="value" style="color: #dc3545;">{{ $usedQuota }} MB</div>
        <div class="subtext">đã đạt giới hạn {{ $totalQuota }} MB</div>
        
        <div class="progress-bar">
            <div class="progress-fill" style="width: 100%; background: linear-gradient(90deg, #dc3545 0%, #c82333 100%);"></div>
        </div>
    </div>
    
    <div class="content">
        <p><strong>Bạn có thể:</strong></p>
        <ul style="padding-left: 20px; margin-top: 10px;">
            <li><strong>Xóa file cũ:</strong> Giải phóng dung lượng bằng cách xóa các file không cần thiết</li>
            <li><strong>Nâng cấp gói:</strong> Nhận thêm dung lượng và nhiều tính năng cao cấp</li>
            <li><strong>Liên hệ hỗ trợ:</strong> Chúng tôi sẵn sàng tư vấn giải pháp phù hợp</li>
        </ul>
    </div>
    
    <div class="button-container">
        <a href="{{ $upgradeUrl }}" class="button">Nâng cấp ngay để tiếp tục</a>
    </div>
    
    <div class="divider"></div>
    
    <div class="content" style="font-size: 14px; color: #888888;">
        <p>Nếu bạn nghĩ đây là lỗi, vui lòng liên hệ bộ phận hỗ trợ.</p>
    </div>
@endsection
