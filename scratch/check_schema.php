<?php
require_once 'config.php';
$pdo = db();
$stmt = $pdo->query("DESCRIBE camp_bookings");
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC), JSON_PRETTY_PRINT);
