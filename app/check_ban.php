<?php
// 차단 IP 확인 및 리다이렉트
require_once __DIR__ . '/db.php';

if (session_status() === PHP_SESSION_NONE) {
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
    $stmt = $pdo->prepare('SELECT 1 FROM ip_bans WHERE ip_address = ? LIMIT 1');
    $stmt->execute([$ip]);
    if ($stmt->fetch()) {
        header('Location: banned.html');
        exit;
    }
}
