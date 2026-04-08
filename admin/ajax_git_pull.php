<?php
// admin/ajax_git_pull.php
// รัน git pull โดยตรงบน server — เฉพาะ Superadmin เท่านั้น

require_once __DIR__ . '/../portal/includes/auth.php';
header('Content-Type: application/json; charset=utf-8');

// 1. Superadmin เท่านั้น
if (($_SESSION['admin_role'] ?? '') !== 'superadmin') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'ไม่มีสิทธิ์ดำเนินการ']);
    exit;
}

// 2. POST only
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
}

// 3. ตรวจสอบว่า exec() ใช้งานได้
if (!function_exists('exec')) {
    echo json_encode(['status' => 'error', 'message' => 'exec() ถูกปิดใช้งานบน server นี้']);
    exit;
}

// 4. Path ของ git repo (= root ของโปรเจกต์)
$repoPath = realpath(__DIR__ . '/..');

// 5. รัน git pull
$output = [];
$code   = 0;
exec(
    'cd ' . escapeshellarg($repoPath) . ' && git pull origin main 2>&1',
    $output,
    $code
);

$outputText = implode("\n", $output);

if ($code === 0) {
    echo json_encode([
        'status'  => 'success',
        'message' => 'Git Pull สำเร็จ ✓',
        'detail'  => $outputText,
    ]);
} else {
    echo json_encode([
        'status'  => 'error',
        'message' => 'git pull ล้มเหลว (exit code ' . $code . ')',
        'detail'  => $outputText,
    ]);
}
