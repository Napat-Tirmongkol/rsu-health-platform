<?php
// admin/ajax_force_cancel.php
session_start();
header('Content-Type: application/json');

// Check admin auth
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../config.php';

validate_csrf_or_die();

$appointmentId = isset($_POST['appointment_id']) ? (int)$_POST['appointment_id'] : 0;

if ($appointmentId <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid Appointment ID']);
    exit;
}

try {
    $pdo = db();
    $pdo->beginTransaction();

    // 1. Check if the appointment exists and get info
    $stmt = $pdo->prepare("
        SELECT 
            a.status,
            a.slot_id,
            s.line_user_id,
            s.full_name,
            c.title AS campaign_title,
            t.slot_date,
            t.start_time
        FROM camp_bookings a
        JOIN sys_users s ON a.student_id = s.id
        JOIN camp_list c ON a.campaign_id = c.id
        JOIN camp_slots t ON a.slot_id = t.id
        WHERE a.id = :id
    ");
    $stmt->execute([':id' => $appointmentId]);
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$booking) {
        $pdo->rollBack();
        echo json_encode(['status' => 'error', 'message' => 'ไม่พบข้อมูลคิวนี้']);
        exit;
    }

    if ($booking['status'] === 'cancelled' || $booking['status'] === 'cancelled_by_admin') {
        $pdo->rollBack();
        echo json_encode(['status' => 'error', 'message' => 'คิวนี้ถูกยกเลิกไปแล้ว']);
        exit;
    }

    // 2. Change status to 'cancelled_by_admin'
    $updateStmt = $pdo->prepare("UPDATE camp_bookings SET status = 'cancelled_by_admin' WHERE id = :id");
    $updateStmt->execute([':id' => $appointmentId]);

    // Note: The slot booked count calculation in time_slots.php uses:
    // "SELECT COUNT(*) FROM camp_bookings WHERE slot_id = ts.id AND status IN ('booked', 'confirmed')"
    // Since we changed status to 'cancelled_by_admin', the booked count drops automatically.
    // There is no `booked` column in `camp_slots` in this schema, the count is dynamic!
    // Wait, let's verify if `booked` column exists.
    // The user's prompt said:
    // "Decrease the booked count in the camp_slots table."
    // But in the code:
    // (SELECT COUNT(*) FROM camp_bookings a WHERE a.slot_id = ts.id AND a.status IN ('booked', 'confirmed')) as booked_count
    // It seems they don't have a `booked` column. It's computed on the fly. So changing status handles the seat automatically!
    
    // 3. Send LINE Message (Messaging API)
    if (!empty($booking['line_user_id'])) {
        $lineUserId = $booking['line_user_id'];
        $campaignTitle = $booking['campaign_title'];
        $dateLabel = date('d/m/Y', strtotime($booking['slot_date']));
        $timeLabel = substr($booking['start_time'], 0, 5);
        
        $messageText = "ขออภัยค่ะ เนื่องจากวันนี้มีผู้เข้าร่วมกิจกรรม {$campaignTitle} เกินจำนวนที่รองรับได้ หรือมีเหตุจำเป็น ระบบจึงขออนุญาตยกเลิกคิวของคุณในวันที่ {$dateLabel} เวลา {$timeLabel} น. \n\nกรุณากดปุ่มด้านล่างเพื่อทำการจองรอบเวลาใหม่ค่ะ";
        
        $liffUrl = "https://liff.line.me/YOUR_LIFF_ID_HERE"; // Replace with actual LIFF URL or external URL
        
        $postData = [
            'to' => $lineUserId,
            'messages' => [
                [
                    'type' => 'flex',
                    'altText' => "แจ้งเตือนการยกเลิกคิว (เพื่อเลื่อนวัน) - กิจกรรม {$campaignTitle}",
                    'contents' => [
                        'type' => 'bubble',
                        'size' => 'giga',
                        'header' => [
                            'type' => 'box',
                            'layout' => 'vertical',
                            'backgroundColor' => '#EF4444',
                            'contents' => [
                                [
                                    'type' => 'text',
                                    'text' => '⚠️ แจ้งยกเลิกคิว (คิวเต็ม)',
                                    'color' => '#FFFFFF',
                                    'weight' => 'bold',
                                    'size' => 'lg',
                                    'align' => 'center'
                                ]
                            ]
                        ],
                        'body' => [
                            'type' => 'box',
                            'layout' => 'vertical',
                            'contents' => [
                                [
                                    'type' => 'text',
                                    'text' => $messageText,
                                    'wrap' => true,
                                    'size' => 'sm',
                                    'color' => '#666666'
                                ]
                            ]
                        ],
                        'footer' => [
                            'type' => 'box',
                            'layout' => 'vertical',
                            'contents' => [
                                [
                                    'type' => 'button',
                                    'style' => 'primary',
                                    'color' => '#0052CC',
                                    'action' => [
                                        'type' => 'uri',
                                        'label' => 'จองรอบเวลาใหม่',
                                        'uri' => $liffUrl
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        // If you have LINE API set up, you would do curl out here:
        // $channelAccessToken = 'YOUR_CHANNEL_ACCESS_TOKEN';
        // $ch = curl_init('https://api.line.me/v2/bot/message/push');
        // curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // curl_setopt($ch, CURLOPT_POST, true);
        // curl_setopt($ch, CURLOPT_HTTPHEADER, [
        //     'Content-Type: application/json',
        //     'Authorization: Bearer ' . $channelAccessToken
        // ]);
        // curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
        // $result = curl_exec($ch);
        // curl_close($ch);
    }
    
    $pdo->commit();

    echo json_encode(['status' => 'success']);

} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

