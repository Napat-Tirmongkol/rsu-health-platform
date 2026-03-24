<?php
// admin/ajax_dashboard.php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/includes/auth.php';

header('Content-Type: application/json');

try {
    $pdo = db();
    
    // 1. ดึงสถิติภาพรวม
    $stmt = $pdo->query("
        SELECT 
            COUNT(*) as total_campaigns,
            (SELECT COUNT(*) FROM camp_appointments WHERE status = 'booked') as pending_count,
            (SELECT COUNT(*) FROM camp_appointments WHERE status = 'confirmed') as confirmed_count
        FROM campaigns WHERE status = 'active'
    ");
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);

    // 2. ดึง 5 แคมเปญยอดฮิต
    $popular_stmt = $pdo->query("
        SELECT c.title, COUNT(a.id) as booking_count
        FROM campaigns c
        LEFT JOIN camp_appointments a ON c.id = a.campaign_id AND a.status IN ('booked', 'confirmed')
        GROUP BY c.id
        ORDER BY booking_count DESC
        LIMIT 5
    ");
    $popular_campaigns = $popular_stmt->fetchAll(PDO::FETCH_ASSOC);

    // 3. สร้างก้อน HTML สำหรับรายชื่อแคมเปญยอดฮิต
    $popular_html = '';
    if (count($popular_campaigns) > 0) {
        foreach($popular_campaigns as $index => $pc) {
            $rankColors = ['bg-orange-100 text-orange-600', 'bg-gray-100 text-gray-600', 'bg-amber-100 text-amber-600'];
            $rankClass = $index < 3 ? $rankColors[$index] : 'bg-blue-50 text-blue-500';
            $num = $index + 1;
            $title = htmlspecialchars($pc['title']);
            $count = number_format($pc['booking_count']);
            
            $popular_html .= '
            <div class="group flex justify-between items-center p-3 rounded-2xl hover:bg-gray-50 transition-colors border border-transparent hover:border-gray-100">
                <div class="flex items-center gap-4">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-sm '.$rankClass.'">
                        '.$num.'
                    </div>
                    <span class="text-gray-800 font-semibold group-hover:text-blue-600 transition-colors">'.$title.'</span>
                </div>
                <span class="bg-white border border-gray-200 shadow-sm px-4 py-1.5 rounded-full text-xs font-bold text-gray-700 whitespace-nowrap">
                    '.$count.' คน
                </span>
            </div>';
        }
    } else {
        $popular_html = '
        <div class="text-center py-10">
            <i class="fa-solid fa-inbox text-4xl text-gray-300 mb-3"></i>
            <p class="text-gray-500 text-sm">ยังไม่มีข้อมูลการจอง</p>
        </div>';
    }

    // ส่งกลับไปให้ Javascript อัปเดตหน้าจอ
    echo json_encode([
        'status' => 'success',
        'stats' => [
            'total' => number_format((float)$stats['total_campaigns']),
            'pending' => number_format((float)$stats['pending_count']),
            'confirmed' => number_format((float)$stats['confirmed_count'])
        ],
        'popular_html' => $popular_html
    ]);

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}