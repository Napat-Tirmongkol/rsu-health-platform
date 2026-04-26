<?php
// user/force_login.php — FOR TESTING ON STAGING ONLY
session_start();
require_once __DIR__ . '/../config.php';

// ค้นหา ID ของคุณ (หรือใครก็ได้ที่คุณอยากสวมรอยทดสอบ)
$pdo = db();
$stmt = $pdo->query("SELECT line_user_id FROM sys_users WHERE full_name LIKE '%ณภัทร%' OR full_name LIKE '%Napat%' LIMIT 1");
$lineId = $stmt->fetchColumn();

if ($lineId) {
    $_SESSION['line_user_id'] = $lineId;
    echo "<h1>Force Login Success!</h1>";
    echo "<p>Logged in as: $lineId</p>";
    echo "<a href='hub.php'>Go to Premium Hub (Dev)</a>";
} else {
    echo "User not found in database. Please register once first.";
}
