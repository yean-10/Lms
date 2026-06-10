<?php
// កែប្រែត្រង់ជួរនេះ៖ បើកដំណើរការ Session តែក្នុងករណីដែលមិនទាន់មានការបើកប៉ុណ្ណោះ
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']);
}

function isMemberLoggedIn() {
    return isset($_SESSION['member_id']);
}

function requireAdmin() {
    if (!isAdminLoggedIn()) {
        header('Location: /member/login.php');
        exit;
    }
}

function requireMember() {
    if (!isMemberLoggedIn()) {
        header('Location: /member/login.php');
        exit;
    }
}

function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function generateMemberCode() {
    return 'MEM' . date('Y') . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
}

function calculateFine($return_date, $actual_return_date, $rate_per_day = 0.125) {
    if (!$actual_return_date || $actual_return_date <= $return_date) return 0;
    $diff = (strtotime($actual_return_date) - strtotime($return_date)) / 86400;
    return round($diff * $rate_per_day, 2);
}
?>