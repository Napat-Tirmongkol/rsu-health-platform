<?php
// user/hub.php — ดีไซน์ใหม่ Command Center (Production)
declare(strict_types=1);
session_start();
require_once __DIR__ . '/../config.php';
check_maintenance('e_campaign');

// ── Auth check ────────────────────────────────────────────────────────────────
if (empty($_SESSION['evax_student_id'])) {
    header('Location: index.php');
    exit;
}

$userId = (int)$_SESSION['evax_student_id'];

// ── DB Connection & Data Fetching ─────────────────────────────────────────────
$user = null;
$camp_list = [];
$booking_list = [];
$upcoming_count = 0;
$borrow_count = 0;

try {
    $pdo = db();
    $today = date('Y-m-d');

    // 1. User Profile
    $stmt = $pdo->prepare("SELECT * FROM sys_users WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $userId]);
    $user = $stmt->fetch();

    // 2. Fetch Active Campaigns
    $stmt = $pdo->prepare("
        SELECT c.*,
               (SELECT COUNT(*) FROM camp_bookings a WHERE a.campaign_id = c.id AND a.status IN ('booked', 'confirmed')) as used_seats
        FROM camp_list c
        WHERE c.status = 'active'
        AND (c.available_until IS NULL OR c.available_until >= :today)
        ORDER BY c.created_at DESC
    ");
    $stmt->execute([':today' => $today]);
    $camp_list = $stmt->fetchAll();

    // 3. Fetch Recent Bookings for this user
    $stmt = $pdo->prepare("
        SELECT b.*, c.title as camp_name, c.type as camp_type
        FROM camp_bookings b
        JOIN camp_list c ON b.campaign_id = c.id
        WHERE b.student_id = :id
        ORDER BY b.booking_date DESC, b.booking_time DESC
        LIMIT 5
    ");
    $stmt->execute([':id' => $userId]);
    $booking_list = $stmt->fetchAll();
    
    // Count Upcoming
    foreach($booking_list as $b) {
        if (in_array($b['status'], ['booked', 'confirmed'])) $upcoming_count++;
    }

    // 4. Count Active Borrows (e-Borrow)
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM borrow_records WHERE borrower_student_id = :id AND status IN ('borrowed','approved')");
        $stmt->execute([':id' => $userId]);
        $borrow_count = (int)$stmt->fetchColumn();
    } catch (Exception $e) { $borrow_count = 0; }

} catch (Exception $e) {
    // Graceful fail
}

// ── Helpers ───────────────────────────────────────────────────────────────────
function getCampStyle($type): array {
    return match($type) {
        'vaccine'      => ['label' => 'วัคซีน', 'class' => 'bg-blue-50 text-blue-600 border-blue-100', 'icon' => 'fa-syringe'],
        'health_check' => ['label' => 'ตรวจสุขภาพ', 'class' => 'bg-emerald-50 text-emerald-600 border-emerald-100', 'icon' => 'fa-stethoscope'],
        default        => ['label' => 'ทั่วไป', 'class' => 'bg-gray-50 text-gray-600 border-gray-100', 'icon' => 'fa-star'],
    };
}

function getStatusStyle($status): array {
    return match($status) {
        'confirmed', 'booked' => ['label' => 'ยืนยันแล้ว', 'class' => 'bg-blue-50 text-blue-600'],
        'completed'           => ['label' => 'สำเร็จแล้ว', 'class' => 'bg-emerald-50 text-emerald-600'],
        'cancelled'           => ['label' => 'ยกเลิกแล้ว', 'class' => 'bg-red-50 text-red-600'],
        default               => ['label' => 'รอดำเนินการ', 'class' => 'bg-gray-50 text-gray-600'],
    };
}

// ── Smart Greeting Logic ─────────────────────────────────
date_default_timezone_set('Asia/Bangkok');
$hour = date('H');
$greeting = "สวัสดี";
if ($hour >= 5 && $hour < 12) $greeting = "อรุณสวัสดิ์";
else if ($hour >= 12 && $hour < 17) $greeting = "สวัสดีตอนบ่าย";
else if ($hour >= 17 && $hour < 21) $greeting = "สวัสดีตอนเย็น";
else $greeting = "สวัสดีตอนค่ำ";

// ── Thai Date Logic ──────────────────────────────────────
$days = ["อาทิตย์", "จันทร์", "อังคาร", "พุธ", "พฤหัสบดี", "ศุกร์", "เสาร์"];
$months = ["มกราคม", "กุมภาพันธ์", "มีนาคม", "เมษายน", "พฤษภาคม", "มิถุนายน", "กรกฎาคม", "สิงหาคม", "กันยายน", "ตุลาคม", "พฤศจิกายน", "ธันวาคม"];
$thaiDate = $days[date('w')] . ", " . date('j') . " " . $months[date('n')-1] . " " . (date('Y') + 543);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>RSU Medical Hub</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700;800;900&display=swap');
        body { font-family: 'Prompt', sans-serif; -webkit-tap-highlight-color: transparent; }
        .glass-header { background: rgba(0, 82, 204, 0.9); backdrop-filter: blur(20px); }
        .custom-scrollbar::-webkit-scrollbar { display: none; }
        .animate-pulse-slow { animation: pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite; }
        @keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: .7; } }
    </style>
</head>
<body class="bg-[#F1F5FB] min-h-screen text-slate-900 pb-24">

    <div class="max-w-md mx-auto relative min-h-screen flex flex-col">
        
        <!-- ── Top Navigation / Header ── -->
        <header class="glass-header sticky top-0 z-[60] px-6 py-5 flex items-center justify-between shadow-lg shadow-blue-900/10 rounded-b-[2rem]">
            <div class="flex items-center gap-3">
                <div onclick="showProfile()" class="w-10 h-10 rounded-xl bg-white/20 p-0.5 border border-white/20 active:scale-90 transition-all cursor-pointer">
                    <img src="<?= $user['picture_url'] ?? 'https://ui-avatars.com/api/?name='.urlencode($user['full_name']); ?>" class="w-full h-full object-cover rounded-[0.6rem]">
                </div>
                <div>
                    <p class="text-blue-100 text-[10px] font-bold uppercase tracking-widest leading-none mb-1 opacity-70"><?= $greeting ?></p>
                    <h1 class="text-white font-black text-sm leading-none"><?= explode(' ', $user['full_name'])[0] ?></h1>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <button onclick="showNotifications()" class="w-10 h-10 flex items-center justify-center bg-white/10 rounded-xl border border-white/10 active:bg-white/20 transition-all relative">
                    <i class="fa-solid fa-bell text-white text-sm"></i>
                    <span class="absolute top-2.5 right-2.5 w-2 h-2 bg-red-500 rounded-full border-2 border-blue-600"></span>
                </button>
                <button onclick="showQR()" class="w-10 h-10 flex items-center justify-center bg-white/10 rounded-xl border border-white/10 active:bg-white/20 transition-all">
                    <i class="fa-solid fa-qrcode text-white text-sm"></i>
                </button>
            </div>
        </header>

        <main class="flex-1 px-5 pt-6 space-y-7 overflow-y-auto custom-scrollbar">
            
            <!-- ── Dynamic Date Greeting ── -->
            <div class="px-1">
                <p class="text-gray-400 text-[10px] font-black uppercase tracking-[0.2em] mb-1 opacity-80"><?= $thaiDate ?></p>
                <h2 class="text-2xl font-black text-slate-800">Command Center</h2>
            </div>

            <!-- ── Main Profile Card (Modern Style) ── -->
            <div class="relative overflow-hidden bg-gradient-to-br from-[#0052CC] via-[#0066FF] to-[#0052CC] rounded-[2.5rem] p-7 shadow-2xl shadow-blue-200 group active:scale-[0.98] transition-all">
                <div class="absolute -right-8 -top-8 w-40 h-40 bg-white/10 rounded-full blur-3xl transition-transform group-hover:scale-125 duration-700"></div>
                <div class="absolute -left-10 -bottom-10 w-48 h-48 bg-blue-400/20 rounded-full blur-3xl"></div>
                
                <div class="relative z-10">
                    <div class="flex items-start justify-between mb-8">
                        <div class="bg-white/20 backdrop-blur-md px-4 py-1.5 rounded-full border border-white/20 flex items-center gap-2 shadow-sm">
                            <span class="w-1.5 h-1.5 bg-emerald-400 rounded-full animate-pulse"></span>
                            <span class="text-white text-[10px] font-black uppercase tracking-widest leading-none">Online System</span>
                        </div>
                        <button onclick="showProfile()" class="flex-shrink-0 w-9 h-9 flex items-center justify-center bg-white/15 rounded-xl border border-white/20 active:bg-white/25 transition-all">
                            <i class="fa-solid fa-chevron-right text-white"></i>
                        </button>
                    </div>
                    <div class="relative flex items-center justify-between mt-5 pt-4 border-t border-white/15">
                        <div class="flex items-center gap-2 text-white">
                            <i class="fa-solid fa-graduation-cap text-blue-200 text-sm"></i>
                            <p class="text-blue-100 text-xs font-medium truncate max-w-[180px]"><?= $user['status'] ?> · <?= $user['department'] ?></p>
                        </div>
                        <div class="flex items-center gap-1.5 bg-emerald-500/20 border border-emerald-400/30 rounded-full px-3 py-1">
                            <i class="fa-solid fa-circle-check text-emerald-300 text-[10px]"></i>
                            <span class="text-emerald-200 text-[10px] font-black uppercase tracking-wider">Verified</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── Stats Row ── -->
            <div class="space-y-3">
                <p class="text-gray-500 text-[10px] font-black uppercase tracking-[0.2em] px-1">ภาพรวมสุขภาพ</p>
                <div class="grid grid-cols-2 gap-3">
                    <button onclick="showUpcoming('ประวัติการเข้าพบ')" class="bg-white rounded-3xl p-4 border border-gray-100 shadow-sm flex flex-col items-center text-center active:scale-95 transition-all">
                        <div class="w-10 h-10 bg-blue-50 rounded-2xl flex items-center justify-center mb-2">
                            <i class="fa-solid fa-calendar-check text-blue-600"></i>
                        </div>
                        <p class="font-black text-sm text-blue-600"><?= count($booking_list) ?></p>
                        <p class="text-gray-400 text-[10px] font-bold">การเข้าพบ</p>
                    </button>
                    <button onclick="showUpcoming('รายการยืมอุปกรณ์')" class="bg-white rounded-3xl p-4 border border-gray-100 shadow-sm flex flex-col items-center text-center active:scale-95 transition-all">
                        <div class="w-10 h-10 bg-orange-50 rounded-2xl flex items-center justify-center mb-2">
                            <i class="fa-solid fa-boxes-stacked text-orange-600"></i>
                        </div>
                        <p class="font-black text-sm text-orange-600"><?= $borrow_count ?></p>
                        <p class="text-gray-400 text-[10px] font-bold">รายการยืม</p>
                    </button>
                </div>
            </div>

            <!-- ── Quick Actions ── -->
            <div class="space-y-3">
                <p class="text-gray-500 text-[10px] font-black uppercase tracking-[0.2em] px-1">เมนูหลัก</p>
                <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-5">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-gray-800 font-bold text-sm">บริการด่วน</h3>
                        <button onclick="showUpcoming('บริการทั้งหมด')" class="text-blue-600 text-xs font-black">ดูทั้งหมด <i class="fa-solid fa-arrow-right ml-1"></i></button>
                    </div>
                    <div class="grid grid-cols-2 gap-3 mb-6">
                        <button onclick="showCampaigns()" class="relative flex flex-col items-start p-4 rounded-3xl border border-blue-700 bg-blue-600 shadow-lg shadow-blue-300/50 active:scale-95 transition-all text-white overflow-hidden text-left">
                            <div class="w-10 h-10 rounded-2xl bg-white/20 flex items-center justify-center mb-3">
                                <i class="fa-solid fa-calendar-plus text-white"></i>
                            </div>
                            <p class="text-xs font-black leading-tight">จองคิว /<br>แคมเปญ</p>
                        </button>
                        <a href="../e_Borrow/" class="relative flex flex-col items-start p-4 rounded-3xl border border-amber-100 bg-amber-50 shadow-sm active:scale-95 transition-all text-amber-600">
                            <div class="w-10 h-10 rounded-2xl bg-white flex items-center justify-center mb-3 shadow-sm">
                                <i class="fa-solid fa-box-archive text-amber-600"></i>
                            </div>
                            <p class="text-xs font-black leading-tight text-gray-800">ยืม-คืน<br>e-Borrow</p>
                        </a>
                        <button onclick="showHistory()" class="relative flex flex-col items-start p-4 rounded-3xl border border-indigo-100 bg-indigo-50 shadow-sm active:scale-95 transition-all text-indigo-600 text-left">
                            <?php if ($upcoming_count > 0): ?>
                                <span class="absolute top-3 right-3 w-5 h-5 bg-red-500 text-white text-[10px] font-black rounded-full flex items-center justify-center border-2 border-white"><?= $upcoming_count ?></span>
                            <?php endif; ?>
                            <div class="w-10 h-10 rounded-2xl bg-white flex items-center justify-center mb-3 shadow-sm">
                                <i class="fa-solid fa-clipboard-list text-indigo-600"></i>
                            </div>
                            <p class="text-xs font-black leading-tight text-gray-800">ประวัติ<br>การรักษา</p>
                        </button>
                        <button onclick="showProfile()" class="relative flex flex-col items-start p-4 rounded-3xl border border-gray-100 bg-gray-50 shadow-sm active:scale-95 transition-all text-gray-600 text-left">
                            <div class="w-10 h-10 rounded-2xl bg-white flex items-center justify-center mb-3 shadow-sm">
                                <i class="fa-solid fa-gear text-gray-400"></i>
                            </div>
                            <p class="text-xs font-black leading-tight text-gray-800">ตั้งค่า<br>โปรไฟล์</p>
                        </button>
                    </div>

                    <div class="border-t border-gray-50 pt-4">
                        <p class="text-gray-400 text-[9px] font-black uppercase tracking-[0.2em] mb-4">บริการอื่นๆ</p>
                        <div class="grid grid-cols-4 gap-4">
                            <a href="https://lin.ee/C3CJ2A9" target="_blank" class="flex flex-col items-center gap-2 active:scale-90 transition-all">
                                <div class="w-11 h-11 bg-purple-50 rounded-2xl flex items-center justify-center text-purple-600 shadow-sm">
                                    <i class="fa-solid fa-comments text-lg"></i>
                                </div>
                                <span class="text-gray-500 text-[9px] font-bold text-center leading-tight">Counseling<br>Clinic</span>
                            </a>
                            <a href="https://line.me/R/ti/p/@115vbibe?oat_content=url&ts=12222134" target="_blank" class="flex flex-col items-center gap-2 active:scale-90 transition-all">
                                <div class="w-11 h-11 bg-cyan-50 rounded-2xl flex items-center justify-center text-cyan-600 shadow-sm">
                                    <i class="fa-solid fa-heart-pulse text-lg"></i>
                                </div>
                                <span class="text-gray-500 text-[9px] font-bold text-center leading-tight">NCD<br>Clinic</span>
                            </a>
                            <button onclick="showContact()" class="flex flex-col items-center gap-2 active:scale-90 transition-all">
                                <div class="w-11 h-11 bg-emerald-50 rounded-2xl flex items-center justify-center text-emerald-600 shadow-sm">
                                    <i class="fa-solid fa-phone text-lg"></i>
                                </div>
                                <span class="text-gray-500 text-[10px] font-bold text-center">ติดต่อ</span>
                            </button>
                            <button onclick="showChat()" class="flex flex-col items-center gap-2 active:scale-90 transition-all">
                                <div class="w-11 h-11 bg-orange-50 rounded-2xl flex items-center justify-center text-orange-600 shadow-sm">
                                    <i class="fa-solid fa-circle-question text-lg"></i>
                                </div>
                                <span class="text-gray-500 text-[10px] font-bold text-center">ช่วยเหลือ</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── Appointment Card ── -->
            <div class="space-y-3 pb-8">
                <p class="text-gray-500 text-[10px] font-black uppercase tracking-[0.2em] px-1">การนัดหมายที่กำลังมา</p>
                <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
                    <div class="flex items-center justify-between px-5 pt-5 pb-3 border-b border-gray-50">
                        <h3 class="text-gray-800 font-bold text-xs uppercase tracking-wider">คิวล่าสุดของคุณ</h3>
                        <span class="text-blue-600 text-[10px] font-black"><?= $upcoming_count ?> รายการ</span>
                    </div>
                    <div class="p-5 space-y-4">
                        <?php if (empty($booking_list)): ?>
                            <div class="py-10 text-center text-gray-300 font-bold text-sm italic">ไม่มีนัดหมายในเร็วๆ นี้</div>
                        <?php else: ?>
                            <?php foreach ($booking_list as $b): 
                                if (!in_array($b['status'], ['booked', 'confirmed'])) continue;
                                $status = getStatusStyle($b['status']);
                            ?>
                            <div class="bg-gray-50/50 rounded-3xl p-5 border border-gray-50 relative group active:scale-[0.98] transition-all">
                                <div class="flex items-start justify-between mb-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 bg-white rounded-2xl flex items-center justify-center shadow-sm text-blue-600">
                                            <i class="fa-solid fa-calendar-check text-sm"></i>
                                        </div>
                                        <div>
                                            <h4 class="text-gray-900 font-bold text-sm leading-none mb-1"><?= htmlspecialchars($b['camp_name']) ?></h4>
                                            <p class="text-gray-400 text-[10px] font-bold uppercase tracking-wider">Appointment Confirmed</p>
                                        </div>
                                    </div>
                                    <span class="px-2 py-1 rounded-lg <?= $status['class'] ?> text-[9px] font-black uppercase"><?= $status['label'] ?></span>
                                </div>
                                <div class="grid grid-cols-2 gap-3">
                                    <div class="bg-white p-3 rounded-2xl border border-gray-100/50 shadow-sm">
                                        <p class="text-gray-400 text-[9px] font-bold uppercase tracking-widest mb-1">วันที่รับบริการ</p>
                                        <p class="text-gray-800 font-black text-xs"><?= date('d M Y', strtotime($b['booking_date'])) ?></p>
                                    </div>
                                    <div class="bg-white p-3 rounded-2xl border border-gray-100/50 shadow-sm">
                                        <p class="text-gray-400 text-[9px] font-bold uppercase tracking-widest mb-1">เวลา</p>
                                        <p class="text-gray-800 font-black text-xs leading-none"><?= date('H:i', strtotime($b['booking_time'])) ?> น.</p>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- ── Insurance / Coverage Card ── -->
            <div class="space-y-3">
                <p class="text-gray-500 text-[10px] font-black uppercase tracking-[0.2em] px-1">สิทธิ์การรักษา</p>
                <div class="grid grid-cols-1 gap-3">
                    <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-[2.5rem] p-7 shadow-xl relative overflow-hidden">
                        <div class="absolute -right-6 -bottom-6 w-32 h-32 bg-white/5 rounded-full blur-2xl"></div>
                        <div class="flex items-start justify-between mb-8">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-white/10 rounded-xl flex items-center justify-center text-white border border-white/10">
                                    <i class="fa-solid fa-shield-heart text-sm"></i>
                                </div>
                                <div>
                                    <p class="text-white font-black text-sm">สิทธิ์นักศึกษา RSU</p>
                                    <p class="text-white/30 text-[9px] font-bold uppercase tracking-widest leading-none mt-1">Medical Coverage</p>
                                </div>
                            </div>
                            <div class="relative w-9 h-6 rounded-md bg-gradient-to-br from-yellow-300/60 to-yellow-500/60 mb-5 border border-yellow-300/30 shadow-sm"></div>
                        </div>
                        <div class="relative mb-5">
                            <div class="flex items-center gap-2 mb-1">
                                <p class="text-white/40 text-[10px] font-black uppercase tracking-[0.2em]">วงเงินคงเหลือ</p>
                                <i class="fa-solid fa-eye text-white/30 text-[10px]"></i>
                            </div>
                            <p class="text-3xl font-bold tracking-tight text-white">฿ 34,500</p>
                            <p class="text-white/30 text-[10px] mt-1 font-bold">จาก ฿ 45,000</p>
                        </div>
                        <div class="relative flex items-end justify-between pt-4 border-t border-white/10">
                            <div>
                                <p class="text-white/40 text-[9px] font-black uppercase tracking-widest mb-1">ผู้ถือกรมธรรม์</p>
                                <p class="text-white text-[11px] font-bold uppercase truncate max-w-[160px]"><?= $user['full_name'] ?></p>
                            </div>
                            <div class="text-right">
                                <p class="text-white/40 text-[9px] font-black uppercase tracking-widest mb-1">หมดอายุ</p>
                                <p class="text-white text-[11px] font-mono font-bold">12 / 25</p>
                            </div>
                        </div>
                    </div>
                    <!-- Coverage Bar -->
                    <div class="bg-white rounded-3xl p-5 border border-gray-100 shadow-sm">
                        <div class="flex items-center justify-between mb-3">
                            <p class="text-gray-500 text-[11px] font-bold">ใช้ไปแล้ว</p>
                            <p class="text-gray-900 text-[11px] font-black">23%</p>
                        </div>
                        <div class="w-full h-2.5 bg-gray-50 rounded-full overflow-hidden border border-gray-100">
                            <div class="h-full bg-emerald-400 rounded-full transition-all shadow-sm shadow-emerald-100" style="width: 23%"></div>
                        </div>
                        <div class="flex items-center justify-between mt-4">
                            <p class="text-gray-400 text-[10px] font-mono font-bold uppercase tracking-tighter opacity-70">RSU-ACC-2025-67891</p>
                            <button onclick="showUpcoming('รายละเอียดกรมธรรม์')" class="text-[#0052CC] text-[10px] font-black uppercase tracking-wider">รายละเอียด <i class="fa-solid fa-chevron-right ml-0.5"></i></button>
                        </div>
                    </div>
                </div>

                <!-- ── Footer ── -->
                <div class="pt-2 pb-8 text-center space-y-1 opacity-40">
                    <p class="text-gray-400 text-[10px] font-bold uppercase tracking-widest">© 2568 RSU Medical Clinic Services</p>
                    <p class="text-gray-400 text-[9px] font-medium uppercase tracking-tighter">Hospital OS v3.1 · user/hub.php</p>
                </div>

            </div>
        </main>

        <!-- ── Bottom Navigation ── -->
        <nav class="fixed bottom-0 left-0 right-0 bg-white/95 backdrop-blur-xl border-t border-gray-100 px-7 py-3 pb-7 sm:pb-3 flex justify-between items-center z-[70] shadow-[0_-5px_20px_rgba(0,0,0,0.03)] max-w-md mx-auto">
            <button onclick="location.reload()" class="flex flex-col items-center gap-1.5 text-[#0052CC] transition-all">
                <i class="fa-solid fa-house text-xl"></i>
                <span class="text-[9px] font-black uppercase tracking-wider">หน้าแรก</span>
            </button>
            <button onclick="showHistory()" class="flex flex-col items-center gap-1.5 text-gray-300 transition-all hover:text-gray-500">
                <i class="fa-solid fa-calendar-days text-xl"></i>
                <span class="text-[9px] font-black uppercase tracking-wider">นัดหมาย</span>
            </button>
            <div class="relative -mt-12">
                <button onclick="showCampaigns()" class="w-15 h-15 bg-[#0052CC] rounded-3xl rotate-45 flex items-center justify-center text-white shadow-2xl shadow-blue-400/50 border-4 border-[#F1F5FB] active:scale-90 transition-all group">
                    <i class="fa-solid fa-plus text-2xl -rotate-45 group-active:scale-110 transition-transform"></i>
                </button>
            </div>
            <button onclick="showUpcoming('ภาพรวมสุขภาพ')" class="flex flex-col items-center gap-1.5 text-gray-300 transition-all hover:text-gray-500">
                <i class="fa-solid fa-heart-pulse text-xl"></i>
                <span class="text-[9px] font-black uppercase tracking-wider">สุขภาพ</span>
            </button>
            <button onclick="showProfile()" class="flex flex-col items-center gap-1.5 text-gray-300 transition-all hover:text-gray-500">
                <i class="fa-solid fa-user text-xl"></i>
                <span class="text-[9px] font-black uppercase tracking-wider">โปรไฟล์</span>
            </button>
        </nav>

    </div>

    <!-- ── QR Code Modal ── -->
    <div id="qr-modal" class="fixed inset-0 z-[100] hidden flex items-center justify-center p-6">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-md" onclick="hideQR()"></div>
        <div class="relative bg-white w-full max-w-[340px] rounded-[2.5rem] p-8 text-center shadow-2xl animate-in zoom-in duration-300">
            <div class="w-12 h-1 bg-gray-100 rounded-full mx-auto mb-6"></div>
            <div class="bg-[#F1F5FB] rounded-3xl p-6 mb-6">
                <div id="qrcode" class="flex justify-center bg-white p-4 rounded-2xl shadow-inner border border-gray-100 mx-auto"></div>
            </div>
            <h3 class="text-gray-900 font-black text-xl mb-1">รหัสประจำตัวของคุณ</h3>
            <p class="text-blue-600 font-mono font-bold text-sm tracking-widest mb-6"><?= $user['student_id'] ?></p>
            <p class="text-gray-400 text-xs leading-relaxed mb-8 px-4">แสดง QR Code นี้แก่เจ้าหน้าที่คลินิก<br>เพื่อทำการยืนยันตัวตน (Check-in)</p>
            <button onclick="hideQR()" class="w-full h-14 bg-gray-50 text-gray-800 font-black rounded-2xl border border-gray-200 active:scale-95 transition-all">ปิดหน้าต่าง</button>
        </div>
    </div>

    <!-- ── Notifications Modal ── -->
    <div id="notif-modal" class="fixed inset-0 z-[100] hidden flex items-end sm:items-center justify-center p-0 sm:p-6">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-md" onclick="hideNotifications()"></div>
        <div class="relative bg-white w-full max-w-[430px] rounded-t-[2.5rem] sm:rounded-[2.5rem] max-h-[85vh] flex flex-col shadow-2xl animate-in slide-in-from-bottom duration-300">
            <div class="w-10 h-1 bg-gray-100 rounded-full mx-auto mt-4 mb-2"></div>
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-50">
                <h3 class="text-gray-900 font-black text-lg">การแจ้งเตือน</h3>
                <span class="bg-blue-50 text-blue-600 text-[10px] font-black px-2 py-1 rounded-lg uppercase tracking-wider"><?= $upcoming_count ?> ใหม่</span>
            </div>
            <div class="flex-1 overflow-y-auto custom-scrollbar divide-y divide-gray-50">
                <div class="flex gap-4 p-5 bg-blue-50/40 relative">
                    <div class="absolute left-0 top-0 bottom-0 w-1 bg-blue-500"></div>
                    <div class="w-10 h-10 rounded-2xl bg-blue-100 flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-calendar-check text-blue-600"></i>
                    </div>
                    <div class="flex-1 min-w-0 text-left">
                        <div class="flex justify-between items-start mb-1">
                            <p class="text-gray-900 font-bold text-sm">นัดหมายสุขภาพ</p>
                            <span class="text-gray-400 text-[10px]">ระบบ</span>
                        </div>
                        <p class="text-gray-600 text-xs leading-relaxed">คุณมีนัดหมายที่รอยืนยันจำนวน <?= $upcoming_count ?> รายการ</p>
                    </div>
                </div>
            </div>
            <div class="p-6 border-t border-gray-50 bg-gray-50/50">
                <button onclick="hideNotifications()" class="w-full h-14 bg-white text-gray-800 font-black rounded-2xl border border-gray-200 active:scale-95 transition-all">ปิดหน้าต่าง</button>
            </div>
        </div>
    </div>

    <!-- ── Profile Modal ── -->
    <div id="profile-modal" class="fixed inset-0 z-[100] hidden flex items-end sm:items-center justify-center p-0 sm:p-6">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-md" onclick="hideProfile()"></div>
        <div class="relative bg-white w-full max-w-[430px] rounded-t-[2.5rem] sm:rounded-[2.5rem] shadow-2xl animate-in slide-in-from-bottom duration-300">
            <div class="w-10 h-1 bg-gray-100 rounded-full mx-auto mt-4 mb-2"></div>
            <div class="p-8 text-center">
                <div class="relative inline-block mb-4">
                    <div class="w-24 h-24 rounded-[2rem] overflow-hidden border-4 border-blue-50 shadow-xl mx-auto">
                        <img src="<?= $user['picture_url'] ?? 'https://ui-avatars.com/api/?name='.urlencode($user['full_name']); ?>" class="w-full h-full object-cover">
                    </div>
                    <div class="absolute -bottom-1 -right-1 w-8 h-8 bg-emerald-500 rounded-2xl border-4 border-white flex items-center justify-center text-white"><i class="fa-solid fa-check text-[10px]"></i></div>
                </div>
                <h3 class="text-gray-900 font-black text-xl mb-1"><?= $user['full_name'] ?></h3>
                <p class="text-gray-400 text-xs font-bold uppercase tracking-widest mb-8"><?= $user['status'] ?> · <?= $user['department'] ?></p>
                <div class="space-y-3 mb-8">
                    <div class="flex items-center gap-4 p-4 bg-gray-50 rounded-2xl border border-gray-100 text-left">
                        <div class="w-10 h-10 bg-white rounded-xl flex items-center justify-center shadow-sm text-blue-600"><i class="fa-solid fa-id-card"></i></div>
                        <div>
                            <p class="text-gray-400 text-[10px] font-bold uppercase tracking-wider">รหัสประจำตัว</p>
                            <p class="text-gray-800 font-bold text-sm"><?= $user['student_id'] ?></p>
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <button onclick="showUpcoming('แก้ไขโปรไฟล์')" class="flex items-center justify-center gap-2 h-14 bg-blue-600 text-white font-black rounded-2xl shadow-lg shadow-blue-200 active:scale-95 transition-all"><i class="fa-solid fa-user-pen text-sm"></i> แก้ไขข้อมูล</button>
                    <a href="../logout.php" class="flex items-center justify-center gap-2 h-14 bg-red-50 text-red-600 font-black rounded-2xl border border-red-100 active:scale-95 transition-all"><i class="fa-solid fa-right-from-bracket text-sm"></i> ออกจากระบบ</a>
                </div>
            </div>
            <div class="px-8 pb-8">
                <button onclick="hideProfile()" class="w-full h-14 bg-gray-50 text-gray-400 font-bold rounded-2xl active:scale-95 transition-all">ปิดหน้าต่าง</button>
            </div>
        </div>
    </div>

    <!-- ── Campaigns List Modal ── -->
    <div id="camps-modal" class="fixed inset-0 z-[100] hidden flex items-end sm:items-center justify-center p-0 sm:p-6">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-md" onclick="hideCampaigns()"></div>
        <div class="relative bg-white w-full max-w-[480px] rounded-t-[2.5rem] sm:rounded-[3rem] shadow-2xl animate-in slide-in-from-bottom duration-300 flex flex-col max-h-[90vh]">
            <div class="w-12 h-1.5 bg-gray-100 rounded-full mx-auto mt-4 mb-2 flex-shrink-0"></div>
            <div class="px-8 pt-6 pb-4 border-b border-gray-50 flex-shrink-0">
                <div class="flex items-center gap-3 mb-1"><div class="w-1.5 h-5 bg-blue-600 rounded-full"></div><h3 class="text-gray-900 font-black text-xl text-left">เลือกแคมเปญ</h3></div>
                <p class="text-gray-400 text-xs font-medium text-left">รายการแคมเปญที่เปิดรับจองคิวในขณะนี้</p>
            </div>
            <div class="flex-1 overflow-y-auto custom-scrollbar px-6 py-4 space-y-4 bg-gray-50/30 text-left">
                <?php if (empty($camp_list)): ?>
                    <div class="py-10 text-center text-gray-400 font-bold text-sm italic">ขออภัย ยังไม่มีแคมเปญที่เปิดรับจอง</div>
                <?php else: ?>
                    <?php foreach ($camp_list as $c): 
                        $style = getCampStyle($c['type']);
                        $remaining = $c['total_capacity'] - $c['used_seats'];
                        $isFull = ($remaining <= 0);
                    ?>
                    <div class="bg-white rounded-3xl p-5 border border-gray-100 shadow-sm relative <?= $isFull ? 'opacity-60' : '' ?>">
                        <div class="flex justify-between items-start mb-3">
                            <span class="px-2 py-1 rounded-lg border <?= $style['class'] ?> text-[9px] font-black uppercase"><?= $style['label'] ?></span>
                            <?php if ($isFull): ?>
                                <span class="text-red-500 text-[10px] font-black uppercase">เต็มแล้ว</span>
                            <?php else: ?>
                                <span class="text-blue-600 text-[10px] font-black uppercase">ว่าง <?= $remaining ?> สิทธิ์</span>
                            <?php endif; ?>
                        </div>
                        <h4 class="text-gray-900 font-bold text-sm mb-3"><?= htmlspecialchars($c['title']) ?></h4>
                        <?php if (!$isFull): ?>
                            <a href="booking_date.php?campaign_id=<?= $c['id'] ?>" class="w-full h-12 bg-blue-600 text-white font-black rounded-2xl flex items-center justify-center gap-2 active:scale-95 transition-all text-xs">เลือกแคมเปญนี้ <i class="fa-solid fa-chevron-right text-[10px]"></i></a>
                        <?php else: ?>
                            <button disabled class="w-full h-12 bg-gray-100 text-gray-400 font-black rounded-2xl cursor-not-allowed text-xs">ไม่สามารถจองได้</button>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <div class="p-6 border-t border-gray-50 flex-shrink-0"><button onclick="hideCampaigns()" class="w-full h-14 bg-gray-50 text-gray-500 font-bold rounded-2xl active:scale-95 transition-all">ปิดหน้าต่าง</button></div>
        </div>
    </div>

    <!-- ── History List Modal ── -->
    <div id="history-modal" class="fixed inset-0 z-[100] hidden flex items-end sm:items-center justify-center p-0 sm:p-6">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-md" onclick="hideHistory()"></div>
        <div class="relative bg-white w-full max-w-[480px] rounded-t-[2.5rem] sm:rounded-[3rem] shadow-2xl animate-in slide-in-from-bottom duration-300 flex flex-col max-h-[90vh]">
            <div class="w-12 h-1.5 bg-gray-100 rounded-full mx-auto mt-4 mb-2 flex-shrink-0"></div>
            <div class="px-8 pt-6 pb-4 border-b border-gray-50 flex-shrink-0 text-left">
                <div class="flex items-center gap-3 mb-1"><div class="w-1.5 h-5 bg-indigo-600 rounded-full"></div><h3 class="text-gray-900 font-black text-xl">ประวัติการรับบริการ</h3></div>
                <p class="text-gray-400 text-xs font-medium">รายการนัดหมายและประวัติสุขภาพของคุณ</p>
            </div>
            <div class="flex-1 overflow-y-auto custom-scrollbar px-6 py-4 space-y-4 bg-gray-50/30 text-left">
                <?php if (empty($booking_list)): ?>
                    <div class="py-10 text-center text-gray-400 font-bold text-sm italic">ยังไม่มีประวัติการนัดหมายในขณะนี้</div>
                <?php else: ?>
                    <?php foreach ($booking_list as $b): 
                        $status = getStatusStyle($b['status']);
                        $campIcon = getCampStyle($b['camp_type'])['icon'];
                    ?>
                    <div class="bg-white rounded-3xl p-5 border border-gray-100 shadow-sm">
                        <div class="flex justify-between items-start mb-3">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 bg-gray-50 rounded-lg flex items-center justify-center text-gray-400"><i class="fa-solid <?= $campIcon ?> text-xs"></i></div>
                                <span class="text-gray-900 font-bold text-xs"><?= htmlspecialchars($b['camp_name']) ?></span>
                            </div>
                            <span class="px-2 py-1 rounded-lg <?= $status['class'] ?> text-[9px] font-black uppercase"><?= $status['label'] ?></span>
                        </div>
                        <div class="flex items-center justify-between text-[11px] text-gray-500">
                            <div class="flex items-center gap-1.5"><i class="fa-regular fa-calendar"></i><?= date('d M Y', strtotime($b['booking_date'])) ?></div>
                            <div class="flex items-center gap-1.5 font-mono font-bold"><i class="fa-regular fa-clock"></i><?= date('H:i', strtotime($b['booking_time'])) ?> น.</div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <div class="p-6 border-t border-gray-50 flex-shrink-0">
                <a href="my_bookings.php" class="w-full h-14 bg-indigo-50 text-indigo-600 font-black rounded-2xl flex items-center justify-center mb-3 active:scale-95 transition-all text-sm">ดูประวัติทั้งหมด</a>
                <button onclick="hideHistory()" class="w-full h-14 bg-gray-50 text-gray-500 font-bold rounded-2xl active:scale-95 transition-all text-sm">ปิดหน้าต่าง</button>
            </div>
        </div>
    </div>

    <!-- ── Contact Modal ── -->
    <div id="contact-modal" class="fixed inset-0 z-[100] hidden flex items-end sm:items-center justify-center p-0 sm:p-6">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-md" onclick="hideContact()"></div>
        <div class="relative bg-white w-full max-w-[430px] rounded-t-[2.5rem] sm:rounded-[2.5rem] shadow-2xl animate-in slide-in-from-bottom duration-300">
            <div class="w-10 h-1 bg-gray-100 rounded-full mx-auto mt-4 mb-2"></div>
            <div class="p-8 text-left">
                <div class="flex items-center gap-4 mb-6">
                    <div class="w-14 h-14 bg-emerald-50 rounded-2xl flex items-center justify-center text-emerald-600 text-xl shadow-inner"><i class="fa-solid fa-headset"></i></div>
                    <div><h3 class="text-gray-900 font-black text-lg leading-tight">ศูนย์บริการสุขภาพ</h3><p class="text-gray-400 text-[10px] font-bold uppercase tracking-widest">Contact Center</p></div>
                </div>
                <div class="mb-6 rounded-3xl overflow-hidden border border-gray-100 shadow-sm"><img src="../assets/images/clinic_map.jpg" class="w-full h-auto object-cover" alt="Clinic Map"></div>
                <div class="space-y-3 mb-8">
                    <a href="tel:027916000,4499" class="flex items-center gap-4 p-5 bg-gray-50 rounded-3xl border border-gray-100 active:scale-95 transition-all group">
                        <div class="w-12 h-12 bg-white rounded-2xl flex items-center justify-center text-emerald-600 shadow-sm group-hover:bg-emerald-600 group-hover:text-white transition-colors"><i class="fa-solid fa-phone-volume"></i></div>
                        <div><p class="text-gray-400 text-[10px] font-black uppercase tracking-widest mb-0.5 leading-none">เบอร์โทรศัพท์</p><p class="text-gray-800 font-black text-sm">02-791-6000 <span class="text-emerald-600 ml-1">ต่อ 4499</span></p></div>
                    </a>
                    <div class="flex items-center gap-4 p-5 bg-gray-50 rounded-3xl border border-gray-100">
                        <div class="w-12 h-12 bg-white rounded-2xl flex items-center justify-center text-blue-600 shadow-sm"><i class="fa-solid fa-location-dot"></i></div>
                        <div><p class="text-gray-400 text-[10px] font-black uppercase tracking-widest mb-0.5 leading-none">ที่ตั้งคลินิก</p><p class="text-gray-800 font-bold text-xs leading-relaxed">52/347 อาคาร 12/1 ถ.เอกทักษิณ ต.หลักหก จ.ปทุมธานี</p></div>
                    </div>
                </div>
                <a href="https://maps.app.goo.gl/rsuhealthcare" target="_blank" class="flex items-center justify-center gap-3 w-full h-16 bg-gray-900 text-white font-black rounded-2xl shadow-xl active:scale-95 transition-all mb-4 text-sm"><i class="fa-solid fa-map-location-dot"></i> เปิดแผนที่คลินิก</a>
                <button onclick="hideContact()" class="w-full h-14 bg-gray-50 text-gray-400 font-bold rounded-2xl active:scale-95 transition-all text-sm">ปิดหน้าต่าง</button>
            </div>
        </div>
    </div>

    <!-- ── Support Chat Modal ── -->
    <div id="chat-modal" class="fixed inset-0 z-[110] hidden flex items-end sm:items-center justify-center p-0 sm:p-6">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-md" onclick="hideChat()"></div>
        <div class="relative bg-white w-full max-w-[430px] rounded-t-[2.5rem] sm:rounded-[2.5rem] shadow-2xl animate-in slide-in-from-bottom duration-300 flex flex-col h-[85vh] sm:h-[600px]">
            <div class="px-8 py-5 border-b border-gray-50 flex items-center justify-between flex-shrink-0 text-left">
                <div class="flex items-center gap-3">
                    <div class="relative"><div class="w-10 h-10 bg-orange-100 rounded-xl flex items-center justify-center text-orange-600 shadow-sm"><i class="fa-solid fa-headset"></i></div><span class="absolute -bottom-0.5 -right-0.5 w-3 h-3 bg-emerald-500 rounded-full border-2 border-white"></span></div>
                    <div><h3 class="text-gray-900 font-black text-sm leading-tight">ฝ่ายสนับสนุน</h3><div class="flex items-center gap-1 leading-none"><span class="w-1.5 h-1.5 bg-emerald-500 rounded-full animate-pulse"></span><span class="text-emerald-500 text-[9px] font-bold uppercase tracking-widest">พร้อมบริการ</span></div></div>
                </div>
                <button onclick="hideChat()" class="text-gray-300 hover:text-gray-500 transition-colors"><i class="fa-solid fa-circle-xmark text-xl"></i></button>
            </div>
            <div id="chat-content" class="flex-1 overflow-y-auto p-6 space-y-6 custom-scrollbar bg-gray-50/30 text-left">
                <div class="text-center"><span class="bg-white px-4 py-1.5 rounded-full text-[9px] font-black text-gray-300 border border-gray-50 uppercase tracking-widest">วันนี้</span></div>
                <div class="flex items-start gap-3 max-w-[85%]">
                    <div class="w-8 h-8 bg-orange-100 rounded-lg flex items-center justify-center text-orange-600 text-xs shrink-0 mt-1 shadow-sm"><i class="fa-solid fa-headset"></i></div>
                    <div class="space-y-1"><div class="bg-white p-4 rounded-2xl rounded-tl-none border border-gray-100 shadow-sm"><p class="text-gray-700 text-xs leading-relaxed text-left">สวัสดีครับ ยินดีต้อนรับสู่ศูนย์ช่วยเหลือครับ มีอะไรให้ช่วยไหมครับ?</p></div><span class="text-[9px] text-gray-300 font-bold ml-1 uppercase"><?= date('H:i') ?></span></div>
                </div>
            </div>
            <div class="p-6 border-t border-gray-50 bg-white flex-shrink-0">
                <form id="chat-form" onsubmit="handleChatSubmit(event)" class="relative"><input type="text" id="chat-input" placeholder="พิมพ์ข้อความของคุณ..." class="w-full h-14 bg-gray-50 border-none rounded-2xl pl-5 pr-16 text-sm font-medium focus:ring-2 focus:ring-blue-100 transition-all placeholder:text-gray-300 leading-none"><button type="submit" class="absolute right-2 top-2 w-10 h-10 bg-blue-600 text-white rounded-xl shadow-lg shadow-blue-100 active:scale-90 transition-all flex items-center justify-center"><i class="fa-solid fa-paper-plane-bottom text-xs"></i></button></form>
            </div>
        </div>
    </div>

    <!-- ── Upcoming Modal ── -->
    <div id="upcoming-modal" class="fixed inset-0 z-[110] hidden flex items-center justify-center p-6">
        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" onclick="hideUpcoming()"></div>
        <div class="relative bg-white w-full max-w-[340px] rounded-[2.5rem] shadow-2xl p-8 text-center animate-in zoom-in duration-200">
            <div class="w-20 h-20 bg-blue-50 rounded-[2rem] flex items-center justify-center mx-auto mb-6 text-blue-600 text-3xl"><i class="fa-solid fa-rocket animate-bounce"></i></div>
            <h3 id="upcoming-title" class="text-gray-900 font-black text-xl mb-2 leading-tight">Coming Soon</h3>
            <p class="text-gray-400 text-sm font-medium mb-8 leading-relaxed">ฟีเจอร์ <span id="upcoming-name" class="text-blue-600 font-black"></span> กำลังพัฒนาและจะพร้อมให้บริการเร็วๆ นี้</p>
            <button onclick="hideUpcoming()" class="w-full h-14 bg-blue-600 text-white font-black rounded-2xl shadow-lg shadow-blue-200 active:scale-95 transition-all">รับทราบ</button>
        </div>
    </div>

    <script>
        let qr = null;
        function showQR() {
            const modal = document.getElementById('qr-modal');
            const qrContainer = document.getElementById('qrcode');
            modal.classList.remove('hidden'); modal.classList.add('flex');
            if (!qr) {
                qrContainer.innerHTML = '';
                qr = new QRCode(qrContainer, { text: "<?= $user['student_id'] ?>", width: 160, height: 160, colorDark : "#000000", colorLight : "#ffffff", correctLevel : QRCode.CorrectLevel.H });
            }
        }
        function hideQR() { document.getElementById('qr-modal').classList.add('hidden'); }
        function showNotifications() { document.getElementById('notif-modal').classList.remove('hidden'); document.getElementById('notif-modal').classList.add('flex'); }
        function hideNotifications() { document.getElementById('notif-modal').classList.add('hidden'); }
        function showProfile() { document.getElementById('profile-modal').classList.remove('hidden'); document.getElementById('profile-modal').classList.add('flex'); }
        function hideProfile() { document.getElementById('profile-modal').classList.add('hidden'); }
        function showCampaigns() { document.getElementById('camps-modal').classList.remove('hidden'); document.getElementById('camps-modal').classList.add('flex'); }
        function hideCampaigns() { document.getElementById('camps-modal').classList.add('hidden'); }
        function showHistory() { document.getElementById('history-modal').classList.remove('hidden'); document.getElementById('history-modal').classList.add('flex'); }
        function hideHistory() { document.getElementById('history-modal').classList.add('hidden'); }
        function showContact() { document.getElementById('contact-modal').classList.remove('hidden'); document.getElementById('contact-modal').classList.add('flex'); }
        function hideContact() { document.getElementById('contact-modal').classList.add('hidden'); }
        function showChat() { document.getElementById('chat-modal').classList.remove('hidden'); document.getElementById('chat-modal').classList.add('flex'); const content = document.getElementById('chat-content'); content.scrollTop = content.scrollHeight; }
        function hideChat() { document.getElementById('chat-modal').classList.add('hidden'); }
        function showUpcoming(name) { document.getElementById('upcoming-name').innerText = name; document.getElementById('upcoming-modal').classList.remove('hidden'); document.getElementById('upcoming-modal').classList.add('flex'); }
        function hideUpcoming() { document.getElementById('upcoming-modal').classList.add('hidden'); }

        function handleChatSubmit(e) {
            e.preventDefault();
            const input = document.getElementById('chat-input');
            const message = input.value.trim();
            if (!message) return;
            const chatContent = document.getElementById('chat-content');
            const time = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            const userBubble = `
                <div class="flex flex-row-reverse items-start gap-3 max-w-[85%] ml-auto animate-in slide-in-from-right-2 duration-300">
                    <div class="space-y-1 text-right">
                        <div class="bg-blue-600 p-4 rounded-2xl rounded-tr-none shadow-lg shadow-blue-100"><p class="text-white text-xs leading-relaxed text-left">${message}</p></div>
                        <span class="text-[9px] text-gray-300 font-bold mr-1 uppercase">${time}</span>
                    </div>
                </div>
            `;
            chatContent.insertAdjacentHTML('beforeend', userBubble);
            input.value = ''; chatContent.scrollTop = chatContent.scrollHeight;
        }
    </script>
</body>
</html>
