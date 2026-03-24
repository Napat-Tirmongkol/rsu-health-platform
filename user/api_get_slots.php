<?php
// user/api_get_slots.php
declare(strict_types=1);
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json');

$date = $_GET['date'] ?? '';
$campaignId = (int)($_GET['campaign_id'] ?? 0);

if (!$date || !$campaignId) {
    echo json_encode(['status' => 'error', 'message' => 'ข้อมูลไม่ครบถ้วน']);
    exit;
}

try {
    $pdo = db();
    $sql = "
        SELECT 
            t.id, t.max_capacity,
            (SELECT COUNT(*) FROM camp_appointments a WHERE a.slot_id = t.id AND a.status IN ('booked', 'confirmed')) as booked_count
        FROM camp_time_slots t
        WHERE t.slot_date = :date AND t.campaign_id = :cid
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':date' => $date, ':cid' => $campaignId]);
    $slots = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $result = [];
    foreach ($slots as $slot) {
        $remaining = (int)$slot['max_capacity'] - (int)$slot['booked_count'];
        $result[$slot['id']] = max(0, $remaining); // ส่งกลับเป็น ID => จำนวนที่เหลือ
    }
    
    echo json_encode(['status' => 'success', 'data' => $result]);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}