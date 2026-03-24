<?php
// staff/ajax_scan_checkin.php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['staff_logged_in']) || $_SESSION['staff_logged_in'] !== true) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validate_csrf_or_die();
    $qrData = trim($_POST['qr_data'] ?? '');
    
    // 🌟 อัปเดต: ใช้ preg_replace เพื่อตัดตัวอักษรและเครื่องหมายทุกอย่างทิ้ง ให้เหลือแค่ "ตัวเลขล้วนๆ"
    $appointmentId = (int) preg_replace('/[^0-9]/', '', $qrData);

    if ($appointmentId > 0) {
        try {
            $pdo = db();
            $sql = "SELECT a.*, c.title as campaign_title, s.full_name FROM camp_appointments a JOIN campaigns c ON a.campaign_id = c.id JOIN med_students s ON a.student_id = s.id WHERE a.id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':id' => $appointmentId]);
            $booking = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$booking) { 
                echo json_encode(['status' => 'error', 'message' => 'ไม่พบข้อมูลการจองรหัส ' . $appointmentId . ' ในระบบ']); 
                exit; 
            }
            if ($booking['status'] === 'cancelled') { 
                echo json_encode(['status' => 'error', 'message' => 'คิวนี้ถูกยกเลิกไปแล้วครับ']); 
                exit; 
            }
            if ($booking['status'] === 'booked') { 
                echo json_encode(['status' => 'warning', 'message' => 'คิวนี้ยังไม่อนุมัติ กรุณาให้แอดมินอนุมัติในระบบก่อน']); 
                exit; 
            }
            if (!empty($booking['attended_at'])) {
                $time = date('H:i', strtotime($booking['attended_at']));
                echo json_encode(['status' => 'warning', 'message' => "สแกนซ้ำ! เช็คอินไปแล้วเวลา {$time} น."]); 
                exit;
            }

            // ผ่านเงื่อนไขทั้งหมด -> บันทึกเวลาเข้างาน
            $pdo->prepare("UPDATE camp_appointments SET attended_at = NOW() WHERE id = :id")->execute([':id' => $appointmentId]);
            
            echo json_encode(['status' => 'success', 'data' => ['name' => $booking['full_name'], 'campaign' => $booking['campaign_title']]]);

        } catch (PDOException $e) { 
            echo json_encode(['status' => 'error', 'message' => 'DB error: ' . $e->getMessage()]); 
        }
    } else { 
        echo json_encode(['status' => 'error', 'message' => 'อ่านข้อมูล QR Code ได้: ' . $qrData . ' (ไม่พบรหัสตัวเลข)']); 
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid Request']);
}