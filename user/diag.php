<?php
// user/diag.php — ตรวจสอบบันทึกการ Pull
declare(strict_types=1);
error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once __DIR__ . '/../config.php';
$pdo = db();

echo "<h2>Git Pull Log Analysis</h2>";
try {
    $stmt = $pdo->query("SELECT * FROM sys_git_pull_log ORDER BY pulled_at DESC LIMIT 10");
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Time</th><th>Status</th><th>Output</th></tr>";
    foreach ($logs as $l) {
        echo "<tr>";
        echo "<td>{$l['pulled_at']}</td>";
        echo "<td>{$l['status']}</td>";
        echo "<td><pre>" . htmlspecialchars($l['output'] ?? '') . "</pre></td>";
        echo "</tr>";
    }
    echo "</table>";
} catch (Exception $e) {
    echo "❌ Error reading git log: " . $e->getMessage() . "<br>";
}

echo "<h2>Done!</h2>";
