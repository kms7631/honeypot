<?php
// 차단 IP 확인 및 리다이렉트
require_once __DIR__ . '/db.php';

$ip = $_SERVER['REMOTE_ADDR'] ?? '';

// dashboard_login.php는 차단 예외로 하지 않고, 모든 public/*에 적용
if ($ip) {
    $pdo = get_pdo();
    $stmt = $pdo->prepare('SELECT 1 FROM ip_bans WHERE ip_address = ? LIMIT 1');
    $stmt->execute([$ip]);
    if ($stmt->fetch()) {
        header('Location: /banned.html');
        exit;
    }
}
