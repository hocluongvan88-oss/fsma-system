<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FSMA 204 - Nền tảng Truy vết Thực phẩm Toàn cầu</title>
    <meta name="description" content="FSMA 204 - Giải pháp truy vết thực phẩm toàn cầu, tuân thủ quy định FDA, bảo đảm an toàn thực phẩm.">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-XXXXXXXXXX"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', 'G-XXXXXXXXXX');
    </script>

    <!-- Facebook Pixel -->
    <script>
        !function(f,b,e,v,n,t,s)
        {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
        n.callMethod.apply(n,arguments):n.queue.push(arguments)};
        if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
        n.queue=[];t=b.createElement(e);t.async=!0;
        t.src=v;s=b.getElementsByTagName(e)[0];
        s.parentNode.insertBefore(t,s)}(window, document,'script',
        'https://connect.facebook.net/en_US/fbevents.js');
        fbq('init', 'YOUR_PIXEL_ID');
        fbq('track', 'PageView');
    </script>
    <noscript><img height="1" width="1" style="display:none" src="https://www.facebook.com/tr?id=YOUR_PIXEL_ID&ev=PageView&noscript=1" /></noscript>

    <!-- reCAPTCHA -->
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --bg-primary: #0a0e27;
            --bg-secondary: #111827;
            --bg-tertiary: #1f2937;
            --text-primary: #ffffff;
            --text-secondary: #d1d5db;
            --text-muted: #9ca3af;
            --accent-primary: #3b82f6;
            --accent-hover: #2563eb;
            --accent-light: #60a5fa;
            --border-color: #374151;
            --success: #10b981;
            --warning: #f59e0b;
            --error: #ef4444;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', sans-serif;
            background: var(--bg-primary);
            color: var(--text-primary);
            line-height: 1.6;
            -webkit-text-size-adjust: 100%;
        }

        /* Added header with logo */
        .header {
            background: var(--bg-secondary);
            border-bottom: 1px solid var(--border-color);
            padding: 15px 20px;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .logo-container {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .logo-container img {
            height: 40px;
            width: auto;
        }

        .logo-text {
            font-size: 0.85rem;
            color: var(--text-muted);
            line-height: 1.3;
        }

        .logo-text .company-name {
            font-weight: 700;
            color: var(--text-primary);
            display: block;
            font-size: 0.9rem;
        }

        .header-nav {
            display: flex;
            gap: 20px;
            align-items: center;
        }

        .header-nav a {
            color: var(--text-secondary);
            text-decoration: none;
            font-size: 0.9rem;
            transition: color 0.2s ease;
        }

        .header-nav a:hover {
            color: var(--accent-light);
        }

        /* Hero Section */
        .hero {
            background: linear-gradient(135deg, var(--bg-primary) 0%, var(--bg-secondary) 100%);
            border-bottom: 1px solid var(--border-color);
            padding: 100px 20px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(59, 130, 246, 0.1) 0%, transparent 70%);
            border-radius: 50%;
            pointer-events: none;
        }

        .hero-content {
            max-width: 900px;
            margin: 0 auto;
            position: relative;
            z-index: 1;
        }

        .hero h1 {
            font-size: clamp(2.5rem, 8vw, 4rem);
            font-weight: 800;
            margin-bottom: 20px;
            background: linear-gradient(135deg, var(--text-primary) 0%, var(--accent-light) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            letter-spacing: -0.02em;
        }

        .hero p {
            font-size: 1.125rem;
            color: var(--text-secondary);
            margin-bottom: 40px;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
            line-height: 1.8;
        }

        .hero-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn {
            padding: 14px 32px;
            border-radius: 8px;
            border: none;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            min-height: 48px;
        }

        .btn-primary {
            background: var(--accent-primary);
            color: white;
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
        }

        .btn-primary:hover {
            background: var(--accent-hover);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(59, 130, 246, 0.4);
        }

        .btn-secondary {
            background: transparent;
            color: var(--text-primary);
            border: 1.5px solid var(--border-color);
        }

        .btn-secondary:hover {
            background: var(--bg-tertiary);
            border-color: var(--accent-primary);
            color: var(--accent-light);
        }

        /* Stats Section */
        .stats {
            background: var(--bg-secondary);
            border-bottom: 1px solid var(--border-color);
            padding: 60px 20px;
        }

        .stats-grid {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 40px;
        }

        .stat-item {
            text-align: center;
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--accent-primary);
            margin-bottom: 8px;
        }

        .stat-label {
            color: var(--text-secondary);
            font-size: 0.95rem;
        }

        /* Features Section */
        .features {
            padding: 100px 20px;
            background: var(--bg-primary);
        }

        .section-title {
            text-align: center;
            margin-bottom: 60px;
        }

        .section-title h2 {
            font-size: clamp(2rem, 5vw, 3rem);
            font-weight: 800;
            margin-bottom: 15px;
            letter-spacing: -0.02em;
        }

        .section-title p {
            font-size: 1.125rem;
            color: var(--text-secondary);
            max-width: 600px;
            margin: 0 auto;
        }

        .features-grid {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
        }

        .feature-card {
            background: var(--bg-secondary);
            border: 1px solid var(--border-color);
            padding: 40px;
            border-radius: 12px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .feature-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--accent-primary), var(--accent-light));
            transform: scaleX(0);
            transform-origin: left;
            transition: transform 0.3s ease;
        }

        .feature-card:hover {
            border-color: var(--accent-primary);
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(59, 130, 246, 0.1);
        }

        .feature-card:hover::before {
            transform: scaleX(1);
        }

        .feature-icon {
            width: 50px;
            height: 50px;
            background: rgba(59, 130, 246, 0.1);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
            font-size: 1.5rem;
        }

        .feature-card h3 {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 12px;
            color: var(--text-primary);
        }

        .feature-card p {
            color: var(--text-secondary);
            line-height: 1.7;
            font-size: 0.95rem;
        }

        /* Form Section */
        .form-section {
            background: linear-gradient(135deg, var(--bg-secondary) 0%, var(--bg-tertiary) 100%);
            border-top: 1px solid var(--border-color);
            border-bottom: 1px solid var(--border-color);
            padding: 80px 20px;
        }

        .form-container {
            max-width: 500px;
            margin: 0 auto;
            background: var(--bg-primary);
            border: 1px solid var(--border-color);
            padding: 50px;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
        }

        .form-container h2 {
            font-size: 1.75rem;
            font-weight: 800;
            margin-bottom: 10px;
            color: var(--text-primary);
        }

        .form-container > p {
            color: var(--text-secondary);
            margin-bottom: 30px;
            font-size: 0.95rem;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--text-secondary);
            font-size: 0.875rem;
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 12px 14px;
            background: var(--bg-secondary);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            color: var(--text-primary);
            font-size: 0.95rem;
            font-family: inherit;
            transition: all 0.2s ease;
        }

        .form-group input::placeholder,
        .form-group textarea::placeholder {
            color: var(--text-muted);
        }

        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--accent-primary);
            background: var(--bg-tertiary);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }

        .recaptcha-container {
            margin-bottom: 20px;
            display: flex;
            justify-content: center;
        }

        .submit-button {
            width: 100%;
            padding: 14px;
            background: var(--accent-primary);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
        }

        .submit-button:hover:not(:disabled) {
            background: var(--accent-hover);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(59, 130, 246, 0.4);
        }

        .submit-button:disabled {
            background: var(--text-muted);
            cursor: not-allowed;
            opacity: 0.6;
        }

        .error-message {
            color: var(--error);
            font-size: 0.8rem;
            margin-top: 5px;
        }

        .success-message {
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid var(--success);
            color: var(--success);
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: none;
        }

        .loading {
            display: none;
            text-align: center;
            color: var(--accent-primary);
        }

        /* Updated footer with company info */
        .footer {
            background: var(--bg-secondary);
            border-top: 1px solid var(--border-color);
            color: var(--text-secondary);
            padding: 50px 20px 30px;
        }

        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 40px;
            margin-bottom: 40px;
        }

        .footer-section h4 {
            color: var(--text-primary);
            margin-bottom: 15px;
            font-weight: 700;
        }

        .footer-section a {
            display: block;
            color: var(--text-secondary);
            text-decoration: none;
            margin-bottom: 10px;
            transition: color 0.2s ease;
            font-size: 0.9rem;
        }

        .footer-section a:hover {
            color: var(--accent-light);
        }

        .footer-section p {
            font-size: 0.9rem;
            line-height: 1.6;
        }

        .footer-bottom {
            border-top: 1px solid var(--border-color);
            padding-top: 30px;
            text-align: center;
            font-size: 0.9rem;
        }

        .powered-by {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid var(--border-color);
        }

        .powered-by img {
            height: 24px;
            width: auto;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .header-nav {
                display: none;
            }

            .hero {
                padding: 60px 20px;
            }

            .hero h1 {
                font-size: 2rem;
            }

            .hero p {
                font-size: 1rem;
            }

            .hero-buttons {
                flex-direction: column;
            }

            .btn {
                width: 100%;
                justify-content: center;
            }

            .form-container {
                padding: 30px;
            }

            .stats-grid {
                gap: 30px;
            }

            .features-grid {
                grid-template-columns: 1fr;
            }

            .feature-card {
                padding: 30px;
            }
        }
    </style>
</head>
<body>
    <!-- Added header with VEXIM logo -->
    <header class="header">
        <div class="header-content">
            <div class="logo-container">
                <img src="https://veximglobal.com/wp-content/uploads/2025/04/Vexim.png" alt="VEXIM Global Logo">
                <div class="logo-text">
                    <span class="company-name">VEXIM GLOBAL</span>
                    <span>FSMA 204 Platform</span>
                </div>
            </div>
            <div class="header-nav">
                <a href="#features">Tính năng</a>
                <a href="#form-section">Đăng ký</a>
                <a href="{{ route('login') }}">Đăng nhập</a>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <h1>Truy vết Thực phẩm Toàn cầu</h1>
            <p>Giải pháp tuân thủ FDA FSMA 204, bảo đảm an toàn thực phẩm từ trang trại đến bàn ăn với công nghệ blockchain và AI</p>
            <div class="hero-buttons">
                <button class="btn btn-primary" onclick="document.getElementById('form-section').scrollIntoView({behavior: 'smooth'})">
                    Đăng ký Dùng thử Miễn phí
                </button>
                <a href="{{ route('login') }}" class="btn btn-secondary">
                    Đăng nhập
                </a>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats">
        <div class="stats-grid">
            <div class="stat-item">
                <div class="stat-number">500+</div>
                <div class="stat-label">Công ty sử dụng</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">50M+</div>
                <div class="stat-label">Sản phẩm được truy vết</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">99.9%</div>
                <div class="stat-label">Độ chính xác</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">24/7</div>
                <div class="stat-label">Hỗ trợ kỹ thuật</div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="features">
        <div class="section-title">
            <h2>Tính năng Nổi bật</h2>
            <p>Giải pháp truy vết thực phẩm toàn diện với công nghệ tiên tiến</p>
        </div>

        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">🔗</div>
                <h3>Truy vết Toàn bộ Chuỗi</h3>
                <p>Theo dõi sản phẩm từ nguồn gốc đến người tiêu dùng cuối cùng với độ chính xác cao và thời gian thực.</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">✅</div>
                <h3>Tuân thủ FDA FSMA 204</h3>
                <p>Đáp ứng đầy đủ các yêu cầu của Luật An toàn Thực phẩm Hiện đại (FSMA) Section 204.</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">📊</div>
                <h3>Báo cáo Thời gian Thực</h3>
                <p>Nhận thông báo ngay lập tức về bất kỳ vấn đề nào trong chuỗi cung ứng với dashboard trực quan.</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">🔐</div>
                <h3>Bảo mật Cao</h3>
                <p>Dữ liệu được mã hóa end-to-end và lưu trữ an toàn trên máy chủ tuân thủ tiêu chuẩn quốc tế.</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">⚡</div>
                <h3>Tích hợp Dễ dàng</h3>
                <p>Kết nối với hệ thống hiện tại của bạn mà không cần thay đổi quy trình hoạt động.</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">🎯</div>
                <h3>Hỗ trợ 24/7</h3>
                <p>Đội ngũ chuyên gia sẵn sàng hỗ trợ bạn bất cứ lúc nào qua email, chat, hoặc điện thoại.</p>
            </div>
        </div>
    </section>

    <!-- Form Section -->
    <section id="form-section" class="form-section">
        <div class="form-container">
            <h2>Bắt đầu Ngay</h2>
            <p>Đăng ký dùng thử miễn phí trong 30 ngày, không cần thẻ tín dụng</p>
            
            <div class="success-message" id="success-message">
                ✓ Cảm ơn bạn đã đăng ký! Chúng tôi sẽ liên hệ với bạn sớm.
            </div>

            <form id="lead-form" onsubmit="handleFormSubmit(event)">
                <div class="form-group">
                    <label for="full_name">Họ và Tên *</label>
                    <input type="text" id="full_name" name="full_name" placeholder="Nhập họ và tên" required>
                    <div class="error-message" id="error-full_name"></div>
                </div>

                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" placeholder="your@email.com" required>
                    <div class="error-message" id="error-email"></div>
                </div>

                <div class="form-group">
                    <label for="phone">Điện thoại</label>
                    <input type="tel" id="phone" name="phone" placeholder="+84 9xx xxx xxx">
                    <div class="error-message" id="error-phone"></div>
                </div>

                <div class="form-group">
                    <label for="company_name">Tên Công ty *</label>
                    <input type="text" id="company_name" name="company_name" placeholder="Tên công ty của bạn" required>
                    <div class="error-message" id="error-company_name"></div>
                </div>

                <div class="form-group">
                    <label for="industry">Ngành Công nghiệp</label>
                    <select id="industry" name="industry">
                        <option value="">-- Chọn ngành --</option>
                        <option value="Thực phẩm">Thực phẩm</option>
                        <option value="Nông sản">Nông sản</option>
                        <option value="Thủy sản">Thủy sản</option>
                        <option value="Chế biến">Chế biến</option>
                        <option value="Phân phối">Phân phối</option>
                        <option value="Bán lẻ">Bán lẻ</option>
                        <option value="Khác">Khác</option>
                    </select>
                    <div class="error-message" id="error-industry"></div>
                </div>

                <div class="recaptcha-container">
                    <div class="g-recaptcha" data-sitekey="{{ env('RECAPTCHA_SITE_KEY', 'YOUR_RECAPTCHA_SITE_KEY') }}"></div>
                </div>

                <button type="submit" class="submit-button" id="submit-btn">
                    Đăng ký Ngay
                </button>

                <div class="loading" id="loading">
                    <p>Đang xử lý...</p>
                </div>
            </form>
        </div>
    </section>

    <!-- Updated footer with VEXIM company information -->
    <footer class="footer">
        <div class="footer-content">
            <div class="footer-section">
                <h4>FSMA 204 Platform</h4>
                <p>Nền tảng truy vết thực phẩm toàn cầu, tuân thủ FDA FSMA 204, được phát triển bởi VEXIM GLOBAL.</p>
            </div>
            <div class="footer-section">
                <h4>Sản phẩm</h4>
                <a href="#">Tính năng</a>
                <a href="#">Bảng giá</a>
                <a href="#">Tài liệu</a>
                <a href="#">API</a>
            </div>
            <div class="footer-section">
                <h4>Công ty</h4>
                <a href="#">Về chúng tôi</a>
                <a href="#">Blog</a>
                <a href="mailto:contact@veximglobal.com">Liên hệ</a>
                <a href="#">Tuyển dụng</a>
            </div>
            <div class="footer-section">
                <h4>Liên hệ</h4>
                <p>
                    <strong>VEXIM GLOBAL COMPANY LIMITED</strong><br>
                    📍 25/6/51 Ngoa Long, Tay Tuu, Hanoi, Vietnam<br>
                    📧 <a href="mailto:contact@veximglobal.com">contact@veximglobal.com</a><br>
                    📞 <a href="tel:+84736856340">+84-73-685-634</a>
                </p>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2025 FSMA 204 Platform. Tất cả quyền được bảo lưu.</p>
            <div class="powered-by">
                <span>Powered by</span>
                <img src="https://veximglobal.com/wp-content/uploads/2025/04/Vexim.png" alt="VEXIM Global">
            </div>
        </div>
    </footer>

    <script>
        async function handleFormSubmit(event) {
            event.preventDefault();

            document.querySelectorAll('.error-message').forEach(el => el.textContent = '');
            document.getElementById('success-message').style.display = 'none';

            document.getElementById('loading').style.display = 'block';
            document.getElementById('submit-btn').disabled = true;

            try {
                const formData = new FormData(document.getElementById('lead-form'));
                
                const recaptchaResponse = document.querySelector('[name="g-recaptcha-response"]');
                if (recaptchaResponse) {
                    formData.append('g-recaptcha-response', recaptchaResponse.value);
                }

                const response = await fetch('{{ route("leads.store") }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    }
                });

                const data = await response.json();

                if (data.success) {
                    if (typeof gtag !== 'undefined') {
                        gtag('event', 'lead_signup', {
                            'event_category': 'engagement',
                            'event_label': 'fsma204_landing_page'
                        });
                    }

                    if (typeof fbq !== 'undefined') {
                        fbq('track', 'Lead', {
                            content_name: 'FSMA 204 Lead',
                            content_category: 'landing_page'
                        });
                    }

                    document.getElementById('success-message').style.display = 'block';
                    document.getElementById('lead-form').reset();

                    setTimeout(() => {
                        if (data.redirect_url) {
                            window.location.href = data.redirect_url;
                        }
                    }, 2000);
                } else {
                    if (data.errors) {
                        Object.keys(data.errors).forEach(field => {
                            const errorEl = document.getElementById(`error-${field}`);
                            if (errorEl) {
                                errorEl.textContent = data.errors[field][0];
                            }
                        });
                    } else {
                        alert(data.message || 'Có lỗi xảy ra. Vui lòng thử lại.');
                    }
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Có lỗi xảy ra. Vui lòng thử lại sau.');
            } finally {
                document.getElementById('loading').style.display = 'none';
                document.getElementById('submit-btn').disabled = false;
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const utmSource = urlParams.get('utm_source');
            const utmMedium = urlParams.get('utm_medium');
            const utmCampaign = urlParams.get('utm_campaign');

            if (typeof gtag !== 'undefined') {
                gtag('event', 'page_view', {
                    'page_title': 'FSMA 204 Landing Page',
                    'utm_source': utmSource,
                    'utm_medium': utmMedium,
                    'utm_campaign': utmCampaign
                });
            }
        });
    </script>
</body>
</html>
