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
            // 개별 로그 삭제
            if (isset($_POST['delete_log_id'])) {
                $log_id = (int)$_POST['delete_log_id'];
                $stmt = $pdo->prepare('DELETE FROM attack_log WHERE log_id = ?');
                $stmt->execute([$log_id]);
                header('Location: dashboard.php'); exit;
            }
            // 삭제 모드 토글
            if (isset($_POST['toggle_delete_mode'])) {
                $_SESSION['delete_mode'] = !empty($_SESSION['delete_mode']) ? 0 : 1;
                header('Location: dashboard.php'); exit;
            }
        // 시험용 차단 IP 추가
        if (isset($_POST['add_test_bans'])) {
            $admin_ip = $_SERVER['REMOTE_ADDR'];
            $test_ips = ['203.0.113.10', '198.51.100.22', '192.0.2.33'];
            foreach ($test_ips as $ip) {
                if ($ip !== $admin_ip) {
                    $stmt = $pdo->prepare('INSERT IGNORE INTO ip_bans (ip_address, ban_reason) VALUES (?, ?)');
                    $stmt->execute([$ip, '시험 데이터']);
                }
            }
            header('Location: dashboard.php'); exit;
        }
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
        if (isset($_SESSION['show_pw_id']) && $_SESSION['show_pw_id'] == $show_pw_id) {
            unset($_SESSION['show_pw_id']); // 이미 열려있으면 닫기
        } else {
            $_SESSION['show_pw_id'] = $show_pw_id; // 아니면 열기
        }
        header('Location: dashboard.php'); exit;
    }
}
// 공격 로그 50개
// IP별 시도 횟수 집계 (최근 1일 기준)
$ip_attempts = [];
$stmt = $pdo->query("SELECT ip_address, COUNT(*) as cnt FROM attack_log WHERE access_time > DATE_SUB(NOW(), INTERVAL 1 DAY) GROUP BY ip_address");
foreach ($stmt as $row) {
    $ip_attempts[$row['ip_address']] = $row['cnt'];
}
// 최근 1일 내 전체 로그를 IP별로 그룹화
$raw_logs = $pdo->query('SELECT * FROM attack_log WHERE access_time > DATE_SUB(NOW(), INTERVAL 1 DAY) ORDER BY access_time DESC')->fetchAll();
$logs_by_ip = [];
foreach ($raw_logs as $row) {
    $ip = $row['ip_address'];
    if (!isset($logs_by_ip[$ip])) $logs_by_ip[$ip] = [];
    $logs_by_ip[$ip][] = $row;
}
// 차단 IP 목록
$bans = $pdo->query('SELECT * FROM ip_bans ORDER BY ban_time DESC')->fetchAll();
$show_pw_id = $_SESSION['show_pw_id'] ?? null;
$delete_mode = !empty($_SESSION['delete_mode']);
$open_ip = $_GET['open_ip'] ?? null;
$open_ban_ip = $_GET['open_ban_ip'] ?? null;

function build_dashboard_url(array $overrides = []): string {
    $params = $_GET;
    foreach ($overrides as $key => $value) {
        if ($value === null || $value === '') {
            unset($params[$key]);
        } else {
            $params[$key] = $value;
        }
    }
    $qs = http_build_query($params);
    return 'dashboard.php' . ($qs ? ('?' . $qs) : '');
}

function random_accounts_for_demo(string $ip, int $min = 2, int $max = 5): array {
    $base = ['admin', 'root', 'administrator', 'test', 'user', 'manager', 'guest', 'support'];
    $count = mt_rand($min, $max);
    $out = [];
    for ($i = 0; $i < $count; $i++) {
        $name = $base[array_rand($base)];
        if (mt_rand(0, 1) === 1) {
            $name .= (string)mt_rand(1, 999);
        }
        $out[] = $name;
    }
    return $out;
}
?><!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>허니팟 관리자 대시보드</title>
    <link rel="stylesheet" href="../assets/style.css">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <style>
    body {
        background: #f4f6fb;
        font-family: 'Segoe UI', 'Malgun Gothic', Arial, sans-serif;
        margin: 0;
        padding: 0;
    }
    .dashboard-container {
        max-width: 900px;
        margin: 40px auto 0 auto;
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 4px 24px rgba(0,0,0,0.08);
        padding: 32px 28px 28px 28px;
    }
    .header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 28px;
    }
    .header-title {
        font-size: 2rem;
        font-weight: 700;
        color: #222;
        letter-spacing: -1px;
    }
    .logout {
        color: #e74c3c;
        text-decoration: none;
        font-weight: bold;
        font-size: 1rem;
        transition: color 0.2s;
    }
    .logout:hover {
        color: #b93222;
    }
    table {
        border-collapse: collapse;
        width: 100%;
        margin-top: 10px;
        background: #fff;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0,0,0,0.03);
    }
    th, td {
        border: none;
        padding: 12px 8px;
        text-align: center;
    }
    th {
        background: #f0f3fa;
        color: #333;
        font-weight: 600;
        font-size: 1.05rem;
    }
    tr:nth-child(even) {
        background: #f8fafd;
    }
    tr:hover {
        background: #eaf3ff;
    }
    .danger {
        color: #e74c3c;
        font-weight: bold;
        font-size: 1.05em;
    }
    button {
        padding: 6px 16px;
        border-radius: 5px;
        border: none;
        background: #3498db;
        color: #fff;
        font-size: 1rem;
        cursor: pointer;
        transition: background 0.18s;
        margin: 0 2px;
    }
    button:hover {
        background: #217dbb;
    }
    .banned {
        color: #aaa;
        font-weight: bold;
    }
    .pw-raw {
        font-family: 'Consolas', 'Menlo', monospace;
        background: #f7f7f7;
        border-radius: 3px;
        padding: 2px 6px;
    }
    .footer {
        margin-top: 32px;
        text-align: right;
        color: #888;
        font-size: 0.97rem;
    }
    </style>
</head>
<body>
<div class="dashboard-container">
    <div class="header">
        <div class="header-title">허니팟 관리자 대시보드</div>
        <a href="dashboard_login.php?logout=1" class="logout">로그아웃</a>
    </div>
    <h3 style="margin-bottom:10px; color:#2d3a4b; font-weight:600;">최근 로그인 시도</h3>
    <form method="post" style="display:inline; float:right; margin-bottom:8px;">
        <button type="submit" name="toggle_delete_mode" value="1" style="background:#e74c3c; color:#fff; font-size:0.98em; padding:6px 18px; border-radius:6px; border:none; margin-left:8px; cursor:pointer;">기록 삭제</button>
    </form>
    <table>
        <tr><th>IP</th><th>시도횟수</th></tr>
        <?php foreach ($logs_by_ip as $ip => $rows): ?>
        <?php
            $cnt = $ip_attempts[$ip] ?? count($rows);
            $danger = ($cnt >= HONEYPOT_BAN_THRESHOLD);
            $toggle_url = ($open_ip === $ip)
                ? build_dashboard_url(['open_ip' => null])
                : build_dashboard_url(['open_ip' => $ip]);
        ?>
        <tr>
            <td style="vertical-align:top; text-align:left; position:relative;">
                <a href="<?=h($toggle_url)?>" style="display:inline-block; padding:0 6px 0 0; text-decoration:none; user-select:none; color:#3a7afe; font-size:1.1em; vertical-align:middle;">
                    <?=$open_ip===$ip?'▼':'▶'?>
                </a>
                <span style="vertical-align:middle; font-size:1.05em; font-weight:500; color:#222;"> <?=h($ip)?></span>
                <?php if ($danger): ?>
                    <span class="danger" style="margin-left:8px;">위험</span>
                <?php endif; ?>
            </td>
            <td style="vertical-align:top;"> <?=h((string)$cnt)?> </td>
        </tr>
        <?php if ($open_ip === $ip): ?>
        <tr>
            <td colspan="2" style="background:#f8fafd; padding:0; border-top:1px solid #e0e7ef;">
                <table style="width:100%;margin:0;">
                    <tr style="background:#f0f3fa;"><th>시간</th><th>ID</th><th>PW</th><th>상태</th><th>액션</th><?php if($delete_mode): ?><th>삭제</th><?php endif; ?></tr>
                    <?php foreach ($rows as $row): ?>
                    <tr>
                        <td><?=h($row['access_time'])?></td>
                        <td><?=h($row['attempt_id'])?></td>
                        <td>
                            <?php
                            if ($show_pw_id == $row['log_id']) {
                                echo '<span class="pw-raw">'.h($row['attempt_pw_raw']).'</span>';
                            } else {
                                echo h($row['attempt_pw_masked']);
                            }
                            ?>
                        </td>
                        <td class="<?= $row['is_banned'] ? 'banned' : '' ?>"><?php echo $row['is_banned'] ? '차단됨' : '-'; ?></td>
                        <td>
                            <?php if ($danger): ?>
                            <form method="post" style="display:inline">
                                <input type="hidden" name="ban_ip" value="<?=h($row['ip_address'])?>">
                                <button type="submit">차단</button>
                            </form>
                            <?php endif; ?>
                            <form method="post" style="display:inline">
                                <input type="hidden" name="show_pw" value="<?=h($row['log_id'])?>">
                                <button type="submit"><?=(($show_pw_id ?? null) == $row['log_id']) ? '가리기' : '원문 보기'?></button>
                            </form>
                        </td>
                        <?php if($delete_mode): ?>
                        <td>
                            <form method="post" style="display:inline">
                                <input type="hidden" name="delete_log_id" value="<?=h($row['log_id'])?>">
                                <button type="submit" style="background:#e74c3c; color:#fff; border-radius:4px; padding:4px 10px;">삭제</button>
                            </form>
                        </td>
                        <?php endif; ?>
                    </tr>
                    <?php endforeach; ?>
                </table>
            </td>
        </tr>
        <?php endif; ?>
        <?php endforeach; ?>
    </table>
    <h2>차단 IP 목록</h2>
        <form method="post" style="margin-bottom:10px;">
            <button type="submit" name="add_test_bans" value="1">차단 IP 시험 데이터 추가</button>
        </form>
    <table>
        <tr><th>IP</th><th>차단 시간</th><th>사유</th><th>액션</th></tr>
        <?php foreach ($bans as $row): ?>
        <?php
            $ban_ip = $row['ip_address'];
            $ban_toggle_url = ($open_ban_ip === $ban_ip)
                ? build_dashboard_url(['open_ban_ip' => null])
                : build_dashboard_url(['open_ban_ip' => $ban_ip]);
        ?>
        <tr>
            <td style="text-align:left;">
                <a href="<?=h($ban_toggle_url)?>" style="display:inline-block; padding:0 6px 0 0; text-decoration:none; user-select:none; color:#3a7afe; font-size:1.1em; vertical-align:middle;">
                    <?=$open_ban_ip===$ban_ip?'▼':'▶'?>
                </a>
                <span style="vertical-align:middle;"> <?=h($ban_ip)?> </span>
            </td>
            <td><?=h($row['ban_time'])?></td>
            <td><?=h($row['ban_reason'] ?? '')?></td>
            <td>
                <form method="post" style="display:inline">
                    <input type="hidden" name="unban_ip" value="<?=h($row['ip_address'])?>">
                    <button type="submit">차단 해제</button>
                </form>
            </td>
        </tr>
        <?php if ($open_ban_ip === $ban_ip): ?>
        <tr>
            <td colspan="4" style="background:#f8fafd; padding:10px 12px; border-top:1px solid #e0e7ef;">
                <div style="font-weight:600; color:#2d3a4b; margin-bottom:8px;">시도 계정(랜덤 데모)</div>
                <table style="width:100%; margin:0;">
                    <tr style="background:#f0f3fa;"><th style="text-align:left; padding-left:12px;">계정</th></tr>
                    <?php foreach (random_accounts_for_demo($ban_ip) as $acct): ?>
                    <tr>
                        <td style="text-align:left; padding-left:12px;"><?=h($acct)?></td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            </td>
        </tr>
        <?php endif; ?>
        <?php endforeach; ?>
    </table>
    <div class="footer">© 2025 Honeypot Demo Dashboard</div>
</div>
</body>
</html>

