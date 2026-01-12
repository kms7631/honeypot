<?php
$secure_cookie = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
ini_set('session.use_strict_mode', '1');
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'secure' => $secure_cookie,
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();
require_once '../app/check_ban.php';
require_once '../app/db.php';
require_once '../app/helpers.php';
require_once '../app/config.php';

$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = trim($_POST['id'] ?? '');
    $pw = trim($_POST['pw'] ?? '');
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    $request_path = $_SERVER['REQUEST_URI'] ?? ($_SERVER['PHP_SELF'] ?? null);
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? null;
    $method = $_SERVER['REQUEST_METHOD'] ?? 'POST';
    if ($request_path === null || $request_path === '') {
        $request_path = 'admin.php';
    }
    if ($id !== '' && $pw !== '' && $ip) {
        $pdo = get_pdo();
        $masked = mask_pw($pw);
        $raw = (defined('HONEYPOT_STORE_RAW_PW') && HONEYPOT_STORE_RAW_PW) ? $pw : '';
        // 로그 저장(DB가 죽어도 페이지는 동일하게 동작)
        if ($pdo) {
            try {
                $stmt = $pdo->prepare('INSERT INTO attack_log (ip_address, attempt_id, attempt_pw_raw, attempt_pw_masked, request_path, user_agent, method) VALUES (?, ?, ?, ?, ?, ?, ?)');
                $stmt->execute([$ip, $id, $raw, $masked, $request_path, $user_agent, $method]);
            } catch (Throwable $e) {
                error_log('[HONEYPOT_LOG_ERROR] ' . $e->getMessage());
            }
        } else {
            error_log('[HONEYPOT_LOG_SKIP] DB unavailable; skipping attack_log insert');
        }
        // 허니팟 페이지는 어떤 자격증명이 들어와도 로그인 실패처럼 동작해야 함
        $err = '로그인 정보를 확인할 수 없습니다. 다시 시도하세요.';
    } else {
        $err = 'ID/PW를 모두 입력하세요.';
    }
}
?><!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>관리자 포털 로그인</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
    body { background: #f4f6fb; }
    .login-box {
        max-width: 380px;
        margin: 60px auto;
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 4px 24px #0002;
        padding: 2.5em 2em 2em 2em;
        text-align: center;
    }
    .brand {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        margin-bottom: 10px;
    }
    .brand-mark {
        width: 34px;
        height: 34px;
        border-radius: 10px;
        background: #3a7afe;
        box-shadow: 0 6px 14px rgba(58, 122, 254, 0.22);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-weight: 700;
        letter-spacing: -1px;
        user-select: none;
    }
    .brand-name {
        font-size: 1.08em;
        font-weight: 700;
        color: #222;
        letter-spacing: -0.5px;
    }
    .login-box h1 {
        font-size: 1.35em;
        margin: 8px 0 0.4em 0;
        color: #333;
        background: transparent;
        padding: 0;
        border-radius: 0;
    }
    .sub {
        color: #667085;
        font-size: 0.98em;
        margin-bottom: 1.2em;
        line-height: 1.35;
    }
    .login-box input[type=text],
    .login-box input[type=password] {
        width: 90%;
        padding: 0.7em;
        margin: 0.5em 0 1em 0;
        border: 1px solid #ccc;
        border-radius: 6px;
        font-size: 1em;
        background: #f9f9f9;
    }
    .row {
        width: 90%;
        margin: 0 auto 1em auto;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        font-size: 0.95em;
        color: #667085;
    }
    .row a {
        color: #3a7afe;
        text-decoration: none;
    }
    .row a:hover {
        text-decoration: underline;
    }
    .remember {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        white-space: nowrap;
        user-select: none;
    }
    .login-box button {
        width: 100%;
        padding: 0.7em;
        background: #3a7afe;
        color: #fff;
        border: none;
        border-radius: 6px;
        font-size: 1.1em;
        cursor: pointer;
        margin-top: 0.5em;
        transition: background 0.2s;
    }
    .login-box button:hover {
        background: #265ecf;
    }
    .secondary {
        width: 100%;
        padding: 0.7em;
        background: #eef4ff;
        color: #265ecf;
        border: 1px solid #d8e6ff;
        border-radius: 6px;
        font-size: 1.05em;
        cursor: not-allowed;
        margin-top: 0.7em;
    }
    .login-box .error {
        color: #e74c3c;
        margin-bottom: 1em;
    }
    .notice {
        width: 90%;
        margin: 1.1em auto 0 auto;
        color: #8892a6;
        font-size: 0.92em;
        line-height: 1.35;
        text-align: left;
        background: #f8fafc;
        border: 1px solid #eef2f7;
        border-radius: 10px;
        padding: 10px 12px;
    }
    .notice b { color: #2d3a4b; }
    .login-box .back-link {
        display: block;
        margin-top: 1.5em;
        color: #888;
        text-decoration: none;
        font-size: 0.95em;
    }
    </style>
</head>
<body>
    <div class="login-box">
        <div class="brand">
            <div class="brand-mark">P</div>
            <div class="brand-name">Portal</div>
        </div>
        <h1>관리자 포털 로그인</h1>
        <div class="sub">보안 정책에 따라 접근이 제한될 수 있습니다.<br>사내 계정으로 로그인하세요.</div>
        <form method="post" autocomplete="off">
            <input type="text" name="id" placeholder="이메일 또는 사번" required autofocus>
            <input type="password" name="pw" placeholder="비밀번호" required>
            <div class="row">
                <label class="remember">
                    <input type="checkbox" name="remember" value="1">
                    로그인 상태 유지
                </label>
                <a href="#" onclick="return false;">비밀번호 재설정</a>
            </div>
            <?php if ($err) echo '<div class="error">'.h($err).'</div>'; ?>
            <button type="submit">로그인</button>
            <button type="button" class="secondary" disabled title="SSO는 데모에서 제공되지 않습니다.">SSO로 로그인</button>
        </form>
        <div class="notice">
            <b>안내</b><br>
            로그인 시도는 보안 감사를 위해 기록될 수 있습니다.
        </div>
        <a href="index.php" class="back-link">메인으로 돌아가기</a>
    </div>
</body>
<!-- honeypot build: 2026-01-09 ctx-fields-v1 -->
</html>
