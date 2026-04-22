<?php
// user/submit_booking.php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';
session_start();

// 1. ตรวจสอบ Login ผ่าน LINE
$lineUserId = $_SESSION['line_user_id'] ?? '';
if ($lineUserId === '') {
    header('Location: index.php', true, 303);
    exit;
}

validate_csrf_or_die();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: booking_campaign.php');
    exit;
}

// 2. รับค่าจากฟอร์ม
$slotId = isset($_POST['slot_id']) ? (int)$_POST['slot_id'] : 0;
$campaignId = isset($_POST['campaign_id']) ? (int)$_POST['campaign_id'] : 0;
$bookingDate = $_POST['booking_date'] ?? date('Y-m-d');

if ($slotId <= 0 || $campaignId <= 0) {
    echo "<script>alert('กรุณาเลือกรอบเวลาให้ครบถ้วน'); window.history.back();</script>";
    exit;
}

try {
    $pdo = db();
    
    // ดึงข้อมูลผู้ใช้จาก LINE ID เพื่อให้ได้ ID และ Student Personnel ID ที่ถูกต้อง
    $stmtU = $pdo->prepare("SELECT id, student_personnel_id, email, full_name FROM sys_users WHERE line_user_id = :line_id LIMIT 1");
    $stmtU->execute([':line_id' => $lineUserId]);
    $user = $stmtU->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        die("User profile not found. Please complete your profile first.");
    }

    $userId = (int)$user['id'];
    $studentPersonnelId = $user['student_personnel_id'];
    
    // 3. เช็คว่าเคยกดจองกิจกรรมนี้ไปแล้วหรือยัง
    $checkSql = "SELECT COUNT(*) FROM camp_bookings WHERE student_personnel_id = :sid AND campaign_id = :cid AND status IN ('confirmed', 'booked')";
    $stmtCheck = $pdo->prepare($checkSql);
    $stmtCheck->execute([':sid' => $studentPersonnelId, ':cid' => $campaignId]);
    if ((int)$stmtCheck->fetchColumn() > 0) {
        header('Location: my_bookings.php?error=already_booked', true, 303);
        exit;
    }

    // 4. เช็คโควต้าแคมเปญ
    $sqlCamp = "
        SELECT c.title, c.total_capacity, c.is_auto_approve,
        (SELECT COUNT(*) FROM camp_bookings WHERE campaign_id = c.id AND status IN ('booked', 'confirmed')) as used
        FROM camp_list c
        WHERE id = :cid AND status = 'active'
          AND (available_until IS NULL OR available_until >= CURDATE())
    ";
    $stmtCamp = $pdo->prepare($sqlCamp);
    $stmtCamp->execute([':cid' => $campaignId]);
    $campData = $stmtCamp->fetch(PDO::FETCH_ASSOC);

    if (!$campData || $campData['used'] >= $campData['total_capacity']) {
        echo "<script>alert('ขออภัย กิจกรรมนี้ที่นั่งเต็มหรือหมดเขตไปแล้ว'); window.location.href='booking_campaign.php';</script>";
        exit;
    }

    // 5. เช็คโควต้ารอบเวลา (Slot)
    $sqlSlot = "
        SELECT max_capacity,
        (SELECT COUNT(*) FROM camp_bookings WHERE slot_id = t.id AND status IN ('booked', 'confirmed')) as slot_used
        FROM camp_slots t
        WHERE id = :slot_id
    ";
    $stmtSlot = $pdo->prepare($sqlSlot);
    $stmtSlot->execute([':slot_id' => $slotId]);
    $slotData = $stmtSlot->fetch(PDO::FETCH_ASSOC);

    if (!$slotData || $slotData['slot_used'] >= $slotData['max_capacity']) {
        echo "<script>alert('ขออภัย รอบเวลาที่คุณเลือกเต็มแล้ว'); window.history.back();</script>";
        exit;
    }

    $bookingStatus = ($campData['is_auto_approve'] == 1) ? 'confirmed' : 'booked';

    // 7. บันทึกข้อมูล (ใช้ student_personnel_id เป็นหลักตามโครงสร้างใหม่)
    $insertSql = "INSERT INTO camp_bookings (student_id, student_personnel_id, campaign_id, slot_id, status, booking_date, booking_time) 
                  SELECT :userId, :sid, :cid, :slot, :status, slot_date, start_time 
                  FROM camp_slots WHERE id = :slot";
    $stmtInsert = $pdo->prepare($insertSql);
    $stmtInsert->execute([
        ':userId' => $userId,
        ':sid'    => $studentPersonnelId, 
        ':cid'    => $campaignId, 
        ':slot'   => $slotId,
        ':status' => $bookingStatus
    ]);

    log_activity('New Booking', "User booked '{$campData['title']}' (Status: $bookingStatus)", $userId);

    // 8. ส่งอีเมล
    if (!empty($user['email'])) {
        try {
            require_once __DIR__ . '/../includes/mail_helper.php';
            $stmtTime = $pdo->prepare("SELECT slot_date, start_time, end_time FROM camp_slots WHERE id = :slot_id");
            $stmtTime->execute([':slot_id' => $slotId]);
            $tInfo = $stmtTime->fetch(PDO::FETCH_ASSOC);
            
            $slot_date = $tInfo ? date('d M Y', strtotime($tInfo['slot_date'])) : date('d M Y', strtotime($bookingDate));
            $slot_time = $tInfo ? substr($tInfo['start_time'], 0, 5) . ' - ' . substr($tInfo['end_time'], 0, 5) : '-';

            notify_booking_status($user['email'], 'confirmation', [
                'campaign_title' => $campData['title'],
                'full_name'      => $user['full_name'],
                'date'           => $slot_date,
                'time'           => $slot_time,
            ]);
        } catch (Exception $ex) { error_log("Email error: " . $ex->getMessage()); }
    }

    header('Location: success.php');
    exit;

} catch (PDOException $e) {
    error_log("Booking Error: " . $e->getMessage());
    echo "<script>alert('เกิดข้อผิดพลาดในการบันทึกข้อมูล'); window.history.back();</script>";
    exit;
}
