<?php
session_start();
require_once '../app/check_ban.php';
require_once '../app/db.php';
require_once '../app/helpers.php';
if (empty($_SESSION['is_admin'])) {
    header('Location: dashboard_login.php');
    exit;
}
$pdo = get_pdo();
// POST 액션 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['ban_ip'])) {
        $ip = $_POST['ban_ip'];
        $stmt = $pdo->prepare('INSERT IGNORE INTO ip_bans (ip_address) VALUES (?)');
        $stmt->execute([$ip]);
        header('Location: dashboard.php'); exit;
    }
    if (isset($_POST['unban_ip'])) {
        $ip = $_POST['unban_ip'];
        $stmt = $pdo->prepare('DELETE FROM ip_bans WHERE ip_address = ?');
        $stmt->execute([$ip]);
        header('Location: dashboard.php'); exit;
    }
    if (isset($_POST['show_pw'])) {
        $show_pw_id = (int)$_POST['show_pw'];
        $_SESSION['show_pw_id'] = $show_pw_id;
        header('Location: dashboard.php'); exit;
    }
}
// 공격 로그 50개
$logs = $pdo->query('SELECT * FROM attack_log ORDER BY access_time DESC LIMIT 50')->fetchAll();
// 차단 IP 목록
$bans = $pdo->query('SELECT * FROM ip_bans ORDER BY ban_time DESC')->fetchAll();
$show_pw_id = $_SESSION['show_pw_id'] ?? null;
?><!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>대시보드</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<div class="container">
    <h1>공격 로그(최근 50건)</h1>
    <table>
        <tr><th>IP</th><th>시간</th><th>ID</th><th>PW</th><th>상태</th><th>액션</th></tr>
        <?php foreach ($logs as $row): ?>
        <tr>
            <td><?=h($row['ip_address'])?></td>
            <td><?=h($row['access_time'])?></td>
            <td><?=h($row['attempt_id'])?></td>
            <td>
                <?php
                if ($show_pw_id == $row['log_id']) {
                    echo '<b>'.h($row['attempt_pw_raw']).'</b>';
                } else {
                    echo h($row['attempt_pw_masked']);
                }
                ?>
            </td>
            <td><?=$row['is_banned'] ? '차단됨' : '-'?></td>
            <td>
                <form method="post" style="display:inline">
                    <input type="hidden" name="ban_ip" value="<?=h($row['ip_address'])?>">
                    <button type="submit">즉시 차단</button>
                </form>
                <form method="post" style="display:inline">
                    <input type="hidden" name="show_pw" value="<?=h($row['log_id'])?>">
                    <button type="submit">원문 보기</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
    <h2>차단 IP 목록</h2>
    <table>
        <tr><th>IP</th><th>차단 시간</th><th>사유</th><th>액션</th></tr>
        <?php foreach ($bans as $row): ?>
        <tr>
            <td><?=h($row['ip_address'])?></td>
            <td><?=h($row['ban_time'])?></td>
            <td><?=h($row['ban_reason'])?></td>
            <td>
                <form method="post" style="display:inline">
                    <input type="hidden" name="unban_ip" value="<?=h($row['ip_address'])?>">
                    <button type="submit">차단 해제</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
    <p><a href="logout.php">로그아웃</a></p>
</div>
</body>
</html>
