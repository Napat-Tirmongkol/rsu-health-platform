<?php
// admin/ajax_approve_booking.php
session_start();
header('Content-Type: application/json');

// เช็คสิทธิ์ Admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit;
}

require_once __DIR__ . '/../config.php'; // ปรับ Path ให้ถอยออกไป 1 ชั้น

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validate_csrf_or_die();
    $appointmentId = (int)($_POST['appointment_id'] ?? 0);

    if ($appointmentId > 0) {
        try {
            $pdo = db();
            // เปลี่ยนจากตาราง vac_ เป็น camp_ 
            $sql = "UPDATE camp_bookings SET status = 'confirmed' WHERE id = :id AND status = 'booked'";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':id' => $appointmentId]);

            if ($stmt->rowCount() > 0) {
                echo json_encode(['status' => 'success', 'message' => 'อนุมัติคิวสำเร็จ']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'ไม่สามารถอัปเดตได้ คิวอาจถูกอนุมัติหรือถูกยกเลิกไปแล้ว']);
            }
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid ID']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
