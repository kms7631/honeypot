<?php
// DB 접속 정보
define('DB_HOST', 'localhost');
define('DB_NAME', 'honeypot_demo');
define('DB_USER', 'root');
define('DB_PASS', 'CHANGE_ME');

define('HONEYPOT_BAN_THRESHOLD', 3); // N회 이상 시 위험 표시

// PW 마스킹 규칙: 앞 N글자만 노출 (나머지는 모두 '*')
define('HONEYPOT_PW_VISIBLE_CHARS', 3);

// 데모 편의 옵션: 비밀번호 원문 저장 여부(운영/공유용이면 false 권장)
define('HONEYPOT_STORE_RAW_PW', false);

// 관리자 계정(데모용)
define('ADMIN_ID', 'admin');
define('ADMIN_PW', 'CHANGE_ME');
