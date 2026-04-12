<?php
// admin/includes/auth.php

// ── Session Security Settings (ตั้งได้เฉพาะก่อน session_start) ──────────────
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.gc_maxlifetime', 7200);
    ini_set('session.cookie_lifetime', 0);
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_samesite', 'Lax');
    session_start();
}

// ── Idle Timeout: logout อัตโนมัติถ้าไม่มีการใช้งานนาน 2 ชั่วโมง ────────────
const ADMIN_SESSION_TIMEOUT = 7200;

if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    if (isset($_SESSION['_admin_last_activity'])) {
        if (time() - $_SESSION['_admin_last_activity'] > ADMIN_SESSION_TIMEOUT) {
            // หมดเวลา — ล้าง session แล้ว redirect
            session_unset();
            session_destroy();
            header('Location: login.php?reason=timeout');
            exit;
        }
    }
    $_SESSION['_admin_last_activity'] = time(); // อัปเดตทุก request
}

// ── Auth Check ──────────────────────────────────────────────────────────────
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../../config.php';