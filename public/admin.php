<?php
session_start();
require_once '../app/check_ban.php';
require_once '../app/db.php';
require_once '../app/helpers.php';
require_once '../app/config.php';

$err = '';
$success = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = trim($_POST['id'] ?? '');
    $pw = trim($_POST['pw'] ?? '');
    // 관리자 계정이면 대시보드로 이동
    if ($id === ADMIN_ID && $pw === ADMIN_PW) {
        $_SESSION['is_admin'] = true;
        header('Location: dashboard.php');
        exit;
    }
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    if ($id !== '' && $pw !== '' && $ip) {
        $pdo = get_pdo();
        $masked = mask_pw($pw);
        $raw = (defined('HONEYPOT_STORE_RAW_PW') && HONEYPOT_STORE_RAW_PW) ? $pw : '';
        // 로그 저장
        $stmt = $pdo->prepare('INSERT INTO attack_log (ip_address, attempt_id, attempt_pw_raw, attempt_pw_masked) VALUES (?, ?, ?, ?)');
        $stmt->execute([$ip, $id, $raw, $masked]);
        // 자동 차단 로직 제거: 시도만 기록, 차단은 대시보드에서 수동으로
        $success = true;
    } else {
        $err = 'ID/PW를 모두 입력하세요.';
    }
}
?><!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>관리자 로그인(허니팟)</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
    body { background: #f4f6fb; }
    .login-box {
        max-width: 350px;
        margin: 60px auto;
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 4px 24px #0002;
        padding: 2.5em 2em 2em 2em;
        text-align: center;
    }
    .login-box h1 {
        font-size: 1.5em;
        margin-bottom: 1.2em;
        color: #333;
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
    .login-box .error {
        color: #e74c3c;
        margin-bottom: 1em;
    }
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
        <h1>관리자 로그인(허니팟)</h1>
        <?php if ($success): ?>
            <p>로그인 시도가 기록되었습니다.</p>
        <?php else: ?>
        <form method="post" autocomplete="off">
            <input type="text" name="id" placeholder="아이디" required autofocus>
            <input type="password" name="pw" placeholder="비밀번호" required>
            <?php if ($err) echo '<div class="error">'.h($err).'</div>'; ?>
            <button type="submit">로그인</button>
        </form>
        <?php endif; ?>
        <a href="index.php" class="back-link">메인으로 돌아가기</a>
    </div>
</body>
</html>
