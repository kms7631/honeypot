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
require_once '../app/config.php';
require_once '../app/helpers.php';

if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: dashboard_login.php');
    exit;
}
$err = '';
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $posted_token = $_POST['csrf_token'] ?? '';
    if (!is_string($posted_token) || !hash_equals($csrf_token, $posted_token)) {
        http_response_code(400);
        $err = '잘못된 요청';
    } else {
    $id = trim($_POST['id'] ?? '');
    $pw = trim($_POST['pw'] ?? '');
    if ($id === ADMIN_ID && $pw === ADMIN_PW) {
        $_SESSION['is_admin'] = true;
        header('Location: dashboard.php');
        exit;
    } else {
        $err = '관리자 인증 실패';
    }
    }
}
?><!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>관리자 대시보드 로그인</title>
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
        margin: 0 0 1.2em 0;
        color: #333;
        background: transparent;
        padding: 0;
        border-radius: 0;
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
        <h1>관리자 대시보드 로그인</h1>
        <form method="post" autocomplete="off">
            <input type="hidden" name="csrf_token" value="<?=h($csrf_token)?>">
            <input type="text" name="id" placeholder="아이디" required autofocus>
            <input type="password" name="pw" placeholder="비밀번호" required>
            <?php if ($err) echo '<div class="error">'.h($err).'</div>'; ?>
            <button type="submit">로그인</button>
        </form>
        <a href="index.php" class="back-link">메인으로 돌아가기</a>
    </div>
</body>
</html>
