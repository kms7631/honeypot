<?php
session_start();
require_once '../app/check_ban.php';
require_once '../app/config.php';
require_once '../app/helpers.php';

$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
?><!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>관리자 대시보드 로그인</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <div class="container">
        <h1>관리자 대시보드 로그인</h1>
        <form method="post">
            <label>ID <input type="text" name="id" required></label><br><br>
            <label>PW <input type="password" name="pw" required></label><br><br>
            <button type="submit">로그인</button>
        </form>
        <?php if ($err) echo '<p style="color:red">'.h($err).'</p>'; ?>
        <p><a href="index.php">메인으로</a></p>
    </div>
</body>
</html>
