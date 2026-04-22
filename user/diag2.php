<?php
// user/diag2.php — ไฟล์วินิจฉัยใหม่เพื่อข้ามปัญหา Cache
declare(strict_types=1);
error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once __DIR__ . '/../config.php';
$pdo = db();

echo "<h2>Diagnostic 2: Error Log Probe</h2>";
try {
    // ลองสุ่มดึงข้อมูล 1 แถวมาดูโครงสร้าง
    $stmt = $pdo->query("SELECT * FROM sys_error_logs ORDER BY id DESC LIMIT 1");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($row) {
        echo "✅ Found latest error log entry:<br>";
        echo "<pre>" . print_r($row, true) . "</pre>";
    } else {
        echo "✅ No errors found in sys_error_logs table.<br>";
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}

echo "<h2>Checking sys_faculties for name_th...</h2>";
try {
    $stmt = $pdo->query("SELECT name_th FROM sys_faculties LIMIT 1");
    echo "Sample name_th: " . $stmt->fetchColumn() . "<br>";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}

echo "<h2>Final Test: Includes</h2>";
$lang = __DIR__ . '/../includes/lang.php';
echo "lang.php: " . (file_exists($lang) ? 'EXISTS' : 'MISSING') . "<br>";
