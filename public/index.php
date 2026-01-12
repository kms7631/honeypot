<?php require_once '../app/check_ban.php'; ?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>Honeypot Demo</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link rel="stylesheet" href="assets/style.css">
    <style>
    body {
        background: linear-gradient(120deg, #e0e7ff 0%, #f4f6fb 100%);
        margin: 0;
        font-family: 'Segoe UI', 'Malgun Gothic', Arial, sans-serif;
        color: #222;
    }
    .nav {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 24px 8vw 0 8vw;
        background: none;
    }
    .nav-logo {
        font-size: 1.6em;
        font-weight: 700;
        color: #3a7afe;
        letter-spacing: -1px;
    }
    .nav-menu a {
        margin-left: 32px;
        color: #3a7afe;
        text-decoration: none;
        font-weight: 500;
        font-size: 1.05em;
        transition: color 0.18s;
    }
    .nav-menu a:hover {
        color: #265ecf;
    }
    .hero {
        max-width: 700px;
        margin: 60px auto 0 auto;
        text-align: center;
        padding: 0 16px;
    }
    .hero-title {
        font-size: 2.6em;
        font-weight: 800;
        color: #222;
        margin-bottom: 0.5em;
        letter-spacing: -2px;
    }
    .hero-desc {
        font-size: 1.25em;
        color: #4b5563;
        margin-bottom: 2em;
    }
    .cta-btn {
        display: inline-block;
        background: linear-gradient(90deg, #3a7afe 60%, #7f56d9 100%);
        color: #fff;
        font-size: 1.15em;
        font-weight: 600;
        border: none;
        border-radius: 8px;
        padding: 0.95em 2.2em;
        box-shadow: 0 2px 16px #3a7afe22;
        cursor: pointer;
        transition: background 0.18s, box-shadow 0.18s;
        margin-bottom: 1.2em;
    }
    .cta-btn:hover {
        background: linear-gradient(90deg, #265ecf 60%, #7f56d9 100%);
        box-shadow: 0 4px 24px #3a7afe33;
    }
    .features {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 32px;
        margin: 60px auto 0 auto;
        max-width: 900px;
        padding: 0 16px;
    }
    .feature-card {
        background: #fff;
        border-radius: 14px;
        box-shadow: 0 2px 16px #3a7afe11;
        padding: 2em 1.5em 1.5em 1.5em;
        min-width: 220px;
        max-width: 270px;
        flex: 1 1 220px;
        text-align: center;
    }
    .feature-icon {
        font-size: 2.2em;
        margin-bottom: 0.5em;
        color: #3a7afe;
    }
    .feature-title {
        font-size: 1.15em;
        font-weight: 700;
        margin-bottom: 0.4em;
    }
    .feature-desc {
        color: #666;
        font-size: 1em;
    }
    @media (max-width: 700px) {
        .features { flex-direction: column; gap: 18px; }
        .nav { flex-direction: column; gap: 10px; padding: 18px 4vw 0 4vw; }
    }
    </style>
</head>
<body>
    <nav class="nav">
        <div class="nav-logo">HONEYPOT DEMO</div>
        <div class="nav-menu">
            <a href="admin.php">포털 로그인</a>
        </div>
    </nav>
    <section class="hero">
        <div class="hero-title">보안 허니팟 데모<br>공격 탐지 & 관리자 대시보드</div>
        <div class="hero-desc">실제 공격 시나리오를 체험하고, 관리자 대시보드에서<br>로그와 차단을 직접 관리해보세요.</div>
        <a href="admin.php" class="cta-btn">관리자 포털로 이동</a>
        <div style="margin-top:1.5em;color:#888;font-size:1em;">체험용 로그인 페이지입니다.<br>입력한 시도는 기록될 수 있습니다.</div>
    </section>
    <section class="features" id="features">
        <div class="feature-card">
            <div class="feature-icon">🔒</div>
            <div class="feature-title">실시간 공격 탐지</div>
            <div class="feature-desc">로그인 시도, 공격 패턴을 실시간으로 기록하고<br>관리자가 직접 확인할 수 있습니다.</div>
        </div>
        <div class="feature-card">
            <div class="feature-icon">🛡️</div>
            <div class="feature-title">수동 차단 & 해제</div>
            <div class="feature-desc">위험 IP를 대시보드에서 직접 차단/해제하며<br>실제 운영 환경을 시뮬레이션합니다.</div>
        </div>
        <div class="feature-card">
            <div class="feature-icon">📊</div>
            <div class="feature-title">심플한 UI/UX</div>
            <div class="feature-desc">모던하고 직관적인 디자인으로<br>누구나 쉽게 체험할 수 있습니다.</div>
        </div>
    </section>
</body>
</html>