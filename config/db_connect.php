<?php
declare(strict_types=1);

$DB_HOST = "localhost"; 
$DB_USER = "root";         
$DB_PASS = ""; 
$DB_NAME = "e-campaignv2_db";
$DB_PORT = 3306;              

function db(): PDO {
  static $pdo = null;
  if ($pdo instanceof PDO) return $pdo;

  // เรียกใช้ตัวแปรแบบ Global
  global $DB_HOST, $DB_NAME, $DB_USER, $DB_PASS, $DB_PORT;

  $dsn = "mysql:host={$DB_HOST};port={$DB_PORT};dbname={$DB_NAME};charset=utf8mb4";
  $options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
  ];

  try {
    $pdo = new PDO($dsn, $DB_USER, $DB_PASS, $options);
    return $pdo;
  } catch (PDOException $e) {
    // แสดง Error ชั่วคราวเพื่อให้รู้ว่าเชื่อมต่อสำเร็จหรือไม่
    die("Database connection failed: " . $e->getMessage());
  }
}
