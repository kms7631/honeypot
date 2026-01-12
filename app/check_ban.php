<?php
// 차단 IP 확인 및 리다이렉트
require_once __DIR__ . '/db.php';

if (session_status() === PHP_SESSION_NONE) {
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
}

$script = basename($_SERVER['SCRIPT_NAME'] ?? '');
// 관리자 복구용 예외: 로그인 페이지는 항상 접근 허용
if ($script === 'dashboard_login.php' || $script === 'logout.php') {
    return;
}
// 대시보드는 관리자 세션이 있으면 접근 허용(차단된 관리자도 해제 가능)
if ($script === 'dashboard.php' && !empty($_SESSION['is_admin'])) {
    return;
}

$ip = $_SERVER['REMOTE_ADDR'] ?? '';
if ($ip) {
    $pdo = get_pdo();
    if (!$pdo) {
        error_log('[HONEYPOT_BAN_CHECK] DB unavailable; skipping ban check');
        return;
    }
    $stmt = $pdo->prepare('SELECT 1 FROM ip_bans WHERE ip_address = ? LIMIT 1');
    $stmt->execute([$ip]);
    if ($stmt->fetch()) {
        header('Location: banned.html');
        exit;
    }
}
