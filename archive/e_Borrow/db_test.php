<?php
// ไฟล์ fix ชั่วคราว — ลบทิ้งหลังใช้งาน
$config_path = realpath(__DIR__ . '/../../config/db_connect.php');
echo "Path: $config_path<br>";

$content = <<<'PHP'
<?php
declare(strict_types=1);

$DB_HOST = "localhost";
$DB_USER = "healthy";
$DB_PASS = "61r_pl6NmNoviy3aB";
$DB_NAME = "e_Borrow";
$DB_PORT = 3306;

function db(): PDO {
  static $pdo = null;
  if ($pdo instanceof PDO) return $pdo;
  global $DB_HOST, $DB_NAME, $DB_USER, $DB_PASS, $DB_PORT;
  try {
    $dsn = "mysql:host=$DB_HOST;dbname=$DB_NAME;port=$DB_PORT;charset=utf8mb4";
    $pdo = new PDO($dsn, $DB_USER, $DB_PASS, [
      PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
      PDO::ATTR_EMULATE_PREPARES   => false,
      PDO::ATTR_TIMEOUT            => 5
    ]);
    return $pdo;
  } catch (PDOException $e) {
    throw new RuntimeException("ไม่สามารถเชื่อมต่อฐานข้อมูลได้: " . $e->getMessage());
  }
}

$pdo = db();
PHP;

if (file_put_contents($config_path, $content) !== false) {
    echo "✅ เขียน db_connect.php สำเร็จ<br>";
} else {
    echo "❌ เขียนไฟล์ไม่ได้ (permission denied)<br>";
    exit;
}

// ทดสอบ connection ทันที
try {
    require_once $config_path;
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM sys_staff");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "✅ เชื่อมต่อ DB สำเร็จ — sys_staff: " . $row['total'] . " แถว<br>";
    echo "<br><strong>✅ เสร็จสิ้น — ลบไฟล์นี้ทิ้งได้เลยครับ</strong>";
} catch (Throwable $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}
?>
