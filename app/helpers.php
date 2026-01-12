<?php
// HTML 이스케이프
function h($str) {
    return htmlspecialchars($str, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// 비밀번호 마스킹(앞 3글자 + ***)
function mask_pw($pw) {
    $visible = defined('HONEYPOT_PW_VISIBLE_CHARS') ? (int)HONEYPOT_PW_VISIBLE_CHARS : 3;
    if ($visible < 0) $visible = 0;
    $len = mb_strlen($pw, 'UTF-8');
    if ($len <= $visible) return str_repeat('*', $len);
    return mb_substr($pw, 0, $visible, 'UTF-8') . str_repeat('*', $len - $visible);
}
