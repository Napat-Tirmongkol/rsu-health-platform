<?php
// portal/includes/auth.php

// ── Session Security Settings ────────────────────────────────────────────────
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.gc_maxlifetime', 7200);
    ini_set('session.cookie_lifetime', 0);
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_samesite', 'Lax');
    // Only send session cookie over HTTPS
    ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? '1' : '0');
    session_start();
}

// ── Idle Timeout ─────────────────────────────────────────────────────────────
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    if (isset($_SESSION['_admin_last_activity'])) {
        if (time() - $_SESSION['_admin_last_activity'] > 7200) {
            $isStaff = !empty($_SESSION['is_ecampaign_staff']);
            session_unset();
            session_destroy();
            // ajax_git_pull.php อยู่ที่ admin/ajax/ (depth ลึกกว่า portal/)
            $_inAjax = basename(dirname($_SERVER['SCRIPT_NAME'] ?? '')) === 'ajax';
            $_pfx = $_inAjax ? '../../admin/auth/' : '../admin/auth/';
            header('Location: ' . $_pfx . ($isStaff ? 'staff_login.php' : 'login.php') . '?reason=timeout');
            exit;
        }
    }
    $_SESSION['_admin_last_activity'] = time();
}

// ── Auth Check ───────────────────────────────────────────────────────────────
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    $_inAjax = basename(dirname($_SERVER['SCRIPT_NAME'] ?? '')) === 'ajax';
    header('Location: ' . ($_inAjax ? '../../admin/auth/login.php' : '../admin/auth/login.php'));
    exit;
}
