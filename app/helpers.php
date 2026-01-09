<?php
// HTML 이스케이프
function h($str) {
    return htmlspecialchars($str, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// 비밀번호 마스킹(앞 3글자 + ***)
function mask_pw($pw) {
    $len = mb_strlen($pw, 'UTF-8');
    if ($len <= 3) return str_repeat('*', $len);
    return mb_substr($pw, 0, 3, 'UTF-8') . str_repeat('*', $len - 3);
}
