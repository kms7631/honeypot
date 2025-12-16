<?php
require_once '../app/check_ban.php';
require_once '../app/db.php';
require_once '../app/helpers.php';
require_once '../app/config.php';

$err = '';
$success = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = trim($_POST['id'] ?? '');
    $pw = trim($_POST['pw'] ?? '');
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    if ($id !== '' && $pw !== '' && $ip) {
        $pdo = get_pdo();
        $masked = mask_pw($pw);
        // 로그 저장
        $stmt = $pdo->prepare('INSERT INTO attack_log (ip_address, attempt_id, attempt_pw_raw, attempt_pw_masked) VALUES (?, ?, ?, ?)');
        $stmt->execute([$ip, $id, $pw, $masked]);
        // 시도 횟수 조회
        $stmt2 = $pdo->prepare('SELECT COUNT(*) FROM attack_log WHERE ip_address = ? AND access_time > DATE_SUB(NOW(), INTERVAL 1 DAY)');
        $stmt2->execute([$ip]);
        $count = (int)$stmt2->fetchColumn();
        if ($count >= HONEYPOT_BAN_THRESHOLD) {
            // 이미 차단된 경우 무시
            $stmt3 = $pdo->prepare('INSERT IGNORE INTO ip_bans (ip_address) VALUES (?)');
            $stmt3->execute([$ip]);
            header('Location: /banned.html');
            exit;
        }
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
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <div class="container">
        <h1>관리자 로그인(허니팟)</h1>
        <?php if ($success): ?>
            <p>로그인 시도가 기록되었습니다.</p>
        <?php else: ?>
        <form method="post">
            <label>ID <input type="text" name="id" required></label><br><br>
            <label>PW <input type="password" name="pw" required></label><br><br>
            <button type="submit">로그인</button>
        </form>
        <?php if ($err) echo '<p style="color:red">'.h($err).'</p>'; ?>
        <?php endif; ?>
        <p><a href="index.php">메인으로</a></p>
    </div>
</body>
</html>
