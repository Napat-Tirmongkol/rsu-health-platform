<?php
// user/test_auth.php — ตัวช่วยให้ Playwright ล็อกอินอัตโนมัติ (เฉพาะใน Staging)
declare(strict_types=1);
session_start();
require_once __DIR__ . '/../config.php';

// ในอนาคตควรเช็ค IP หรือมี Token ป้องกัน
// สำหรับตอนนี้ใช้เพื่อการทดสอบ Automated Test

$_SESSION['line_user_id'] = 'U99999999999999999999999999999999'; // เทสไอดีสมมติ
$_SESSION['user_full_name'] = 'Test Automation';

echo "✅ Auth session set for Playwright. Redirecting to hub...";
header('Refresh: 1; URL=hub.php');
