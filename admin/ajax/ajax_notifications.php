<?php
// admin/ajax_notifications.php
declare(strict_types=1);

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json');

try {
    $pdo = db();

    $errors_today = (int)$pdo->query(
        "SELECT COUNT(*) FROM sys_error_logs WHERE DATE(created_at) = CURDATE() AND status != 'Resolved'"
    )->fetchColumn();

    $pending_bookings = (int)$pdo->query(
        "SELECT COUNT(*) FROM camp_bookings WHERE status = 'booked'"
    )->fetchColumn();

    echo json_encode([
        'status'          => 'success',
        'errors_today'    => $errors_today,
        'pending_bookings'=> $pending_bookings,
        'total'           => $errors_today + $pending_bookings,
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'status'          => 'error',
        'errors_today'    => 0,
        'pending_bookings'=> 0,
        'total'           => 0,
    ]);
}
