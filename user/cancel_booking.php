<?php
// user/cancel_booking.php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('Location: my_bookings.php', true, 303);
  exit;
}

validate_csrf_or_die();

$studentId = isset($_SESSION['evax_student_id']) ? (int)$_SESSION['evax_student_id'] : 0;
$appointmentId = isset($_POST['appointment_id']) ? (int)$_POST['appointment_id'] : 0;

if ($studentId <= 0 || $appointmentId <= 0) {
  header('Location: my_bookings.php', true, 303);
  exit;
}

try {
  $pdo = db();
  
  // อัปเดตสถานะเป็น cancelled ในตารางใหม่ (camp_bookings)
  $sql = "
    UPDATE camp_bookings 
    SET status = 'cancelled' 
    WHERE id = :appointment_id AND student_id = :student_id
  ";
  $stmt = $pdo->prepare($sql);
  $stmt->execute([
    ':appointment_id' => $appointmentId,
    ':student_id' => $studentId
  ]);

} catch (PDOException $e) {
  die("Error cancelling booking: " . $e->getMessage());
}

// ยกเลิกเสร็จ เด้งกลับไปหน้าประวัติการจอง
header('Location: my_bookings.php', true, 303);
exit;
