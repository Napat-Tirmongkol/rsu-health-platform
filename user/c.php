<?php
// user/c.php — Premium Campaign Invite Landing
declare(strict_types=1);
session_start();

// 1. ตรวจสอบ Login เบื้องต้น
$lineUserId = $_SESSION['line_user_id'] ?? '';
if ($lineUserId === '') {
    $_SESSION['invite_token'] = trim($_GET['t'] ?? '');
    
    // แสดงหน้า Gateway สวยๆ ให้ผู้ใช้กด Login เองเพื่อข้ามระบบ Security ของ Browser
    ?>
    <!DOCTYPE html>
    <html lang="th">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
        <title>RSU Medical Hub - Login Required</title>
        <script src="https://cdn.tailwindcss.com/3.4.1"></script>
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
        <style>
            @font-face { font-family:'RSU'; src:url('../assets/fonts/RSU_Regular.ttf') format('truetype'); }
            @font-face { font-family:'RSU'; src:url('../assets/fonts/RSU_BOLD.ttf') format('truetype'); font-weight:bold; }
            body { font-family:'RSU', sans-serif; background:#F8FAFF; overflow:hidden; }
            .premium-gradient { background: linear-gradient(135deg, #0052CC 0%, #0070F3 100%); }
        </style>
    </head>
    <body class="flex items-center justify-center min-h-screen p-6">
        <div class="w-full max-w-md text-center animate-in zoom-in fade-in duration-700">
            <div class="bg-white rounded-[3.5rem] p-10 shadow-[0_30px_80px_rgba(0,0,0,0.1)] border border-slate-50 relative overflow-hidden">
                <div class="absolute -right-20 -top-20 w-48 h-48 bg-blue-50 rounded-full blur-3xl opacity-50"></div>
                <div class="absolute -left-20 -bottom-20 w-48 h-48 bg-emerald-50 rounded-full blur-3xl opacity-50"></div>
                
                <div class="relative z-10">
                    <div class="w-24 h-24 premium-gradient rounded-[2.5rem] flex items-center justify-center mx-auto mb-8 shadow-xl shadow-blue-200">
                        <i class="fa-solid fa-user-shield text-white text-4xl"></i>
                    </div>
                    
                    <h1 class="text-slate-900 font-black text-3xl mb-4 leading-tight">เข้าสู่ระบบเพื่อดำเนินการต่อ</h1>
                    <p class="text-slate-400 text-sm font-bold mb-10 leading-relaxed px-4">
                        คุณกำลังจะเข้าสู่แคมเปญ <span class="text-blue-600">นัดหมายพิเศษ</span><br>
                        กรุณายืนยันตัวตนผ่าน LINE เพื่อความปลอดภัยครับ
                    </p>
                    
                    <a href="https://healthycampus.rsu.ac.th/e-campaignv2/archive/line_api/line_login.php" 
                       class="flex items-center justify-center gap-4 w-full h-20 bg-[#06C755] hover:bg-[#05b34c] text-white font-black rounded-3xl shadow-[0_20px_40px_rgba(6,199,85,0.25)] active:scale-95 transition-all text-base tracking-widest uppercase">
                        <i class="fa-brands fa-line text-2xl"></i>
                        Login with LINE
                    </a>
                    
                    <div class="mt-8 flex items-center justify-center gap-2">
                        <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full animate-pulse"></span>
                        <span class="text-slate-300 text-[10px] font-black uppercase tracking-[0.3em]">Secure Connection</span>
                    </div>
                </div>
            </div>
            
            <p class="mt-10 text-slate-300 text-[10px] font-black uppercase tracking-widest">Powered by RSU Healthcare Services</p>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// 2. ถ้า Login แล้วค่อยโหลด Config และจัดการข้อมูล
require_once __DIR__ . '/../config.php';
check_maintenance('e_campaign');

$pdo = db();
$stmt = $pdo->prepare("SELECT id FROM sys_users WHERE line_user_id = :line_id LIMIT 1");
$stmt->execute([':line_id' => $lineUserId]);
$user = $stmt->fetch();

if (!$user) {
    // กรณีมี Session แต่ไม่มีข้อมูลใน DB (อาจถูกลบหรือข้อมูลค้าง)
    session_destroy();
    header('Location: index.php');
    exit;
}

$token = trim($_GET['t'] ?? '');
if ($token === '') {
    header('Location: hub.php', true, 303);
    exit;
}

$campaign = null;
$usedSeats = 0;
try {
    $stmt = $pdo->prepare("
        SELECT c.*,
               (SELECT COUNT(*) FROM camp_bookings a
                WHERE a.campaign_id = c.id AND a.status IN ('booked','confirmed')) AS used_seats
        FROM camp_list c
        WHERE c.share_token = :token
        LIMIT 1
    ");
    $stmt->execute([':token' => $token]);
    $campaign = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($campaign) {
        $usedSeats = (int)$campaign['used_seats'];
    }
} catch (PDOException $e) { }

$today = date('Y-m-d');
$isExpired = $campaign && $campaign['available_until'] && ($campaign['available_until'] < $today);
$isInactive = $campaign && (!in_array($campaign['status'], ['active', 'private']) || $isExpired);
$remaining = $campaign ? max(0, (int)$campaign['total_capacity'] - $usedSeats) : 0;
$isFull = ($remaining <= 0 && $campaign);

function getCampStyle($type): array {
    return match ($type) {
        'vaccine' => ['label' => 'วัคซีน', 'class' => 'bg-blue-50 text-blue-600 border-blue-100', 'icon' => 'fa-syringe'],
        'health_check' => ['label' => 'ตรวจสุขภาพ', 'class' => 'bg-emerald-50 text-emerald-600 border-emerald-100', 'icon' => 'fa-stethoscope'],
        default => ['label' => 'ทั่วไป', 'class' => 'bg-gray-50 text-gray-600 border-gray-100', 'icon' => 'fa-star'],
    };
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title><?= $campaign ? htmlspecialchars($campaign['title']) : 'ไม่พบแคมเปญ' ?> - RSU Medical</title>
    <script src="https://cdn.tailwindcss.com/3.4.1"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        @font-face { font-family:'RSU'; src:url('../assets/fonts/RSU_Regular.ttf') format('truetype'); font-weight:normal; font-style:normal; }
        @font-face { font-family:'RSU'; src:url('../assets/fonts/RSU_BOLD.ttf') format('truetype'); font-weight:bold; font-style:normal; }
        body { font-family:'RSU', sans-serif; background-color:#F8FAFF; -webkit-tap-highlight-color:transparent; }
        .glass-header { background:rgba(0, 82, 204, 0.95); backdrop-filter:blur(25px); -webkit-backdrop-filter:blur(25px); }
    </style>
</head>
<body class="text-slate-900 pb-20">

    <!-- HEADER -->
    <header class="glass-header sticky top-0 z-[100] px-6 py-4 flex items-center justify-between shadow-lg">
        <a href="hub.php" class="w-10 h-10 bg-white/10 rounded-2xl flex items-center justify-center text-white active:scale-90 transition-all">
            <i class="fa-solid fa-chevron-left text-sm"></i>
        </a>
        <div class="flex flex-col items-center">
            <span class="text-white/60 text-[8px] font-black uppercase tracking-[0.4em] leading-none mb-1">Invitation Link</span>
            <span class="text-white font-black text-xs uppercase tracking-widest">RSU Medical Hub</span>
        </div>
        <div class="w-10 h-10"></div> <!-- Placeholder -->
    </header>

    <main class="p-6 max-w-md mx-auto space-y-6 animate-in fade-in slide-in-from-bottom-4 duration-500">

        <?php if (!$campaign): ?>
            <div class="bg-white rounded-[3rem] p-12 text-center border border-slate-100 shadow-xl">
                <div class="w-24 h-24 bg-red-50 rounded-[2.5rem] flex items-center justify-center mx-auto mb-8 text-red-300 text-4xl">
                    <i class="fa-solid fa-link-slash"></i>
                </div>
                <h2 class="text-slate-900 font-black text-2xl mb-3">ไม่พบแคมเปญนี้</h2>
                <p class="text-slate-400 text-sm font-bold mb-10 leading-relaxed">ลิงก์อาจหมดอายุหรือถูกยกเลิกแล้ว</p>
                <a href="hub.php" class="block w-full h-16 bg-blue-600 text-white font-black rounded-2xl flex items-center justify-center shadow-lg active:scale-95 transition-all text-sm uppercase tracking-widest">กลับหน้าหลัก</a>
            </div>

        <?php elseif ($isInactive): ?>
            <?php $style = getCampStyle($campaign['type']); ?>
            <div class="bg-white rounded-[3rem] p-10 border border-slate-100 shadow-xl relative overflow-hidden">
                <div class="absolute -right-10 -top-10 w-32 h-32 bg-slate-50 rounded-full blur-2xl"></div>
                <div class="relative z-10">
                    <div class="w-16 h-16 <?= $style['class'] ?> rounded-2xl flex items-center justify-center text-2xl mb-6 border">
                        <i class="fa-solid <?= $style['icon'] ?>"></i>
                    </div>
                    <h1 class="text-slate-900 font-black text-2xl leading-tight mb-4"><?= htmlspecialchars($campaign['title']) ?></h1>
                    
                    <div class="bg-red-50 border border-red-100 rounded-2xl p-6 text-center">
                        <i class="fa-solid fa-lock text-red-200 text-3xl mb-3"></i>
                        <p class="text-red-700 font-black text-base mb-1"><?= $isExpired ? 'หมดเขตรับสมัครแล้ว' : 'ปิดรับสมัครชั่วคราว' ?></p>
                        <p class="text-red-500 text-[11px] font-bold">
                            <?= $isExpired ? 'แคมเปญนี้ปิดเมื่อ ' . date('d/m/Y', strtotime($campaign['available_until'])) : 'ขออภัย แคมเปญนี้ยังไม่เปิดให้ลงทะเบียนในขณะนี้' ?>
                        </p>
                    </div>
                </div>
                <a href="hub.php" class="mt-8 block text-center text-slate-400 text-xs font-black uppercase tracking-widest hover:text-blue-600 transition-colors">
                    <i class="fa-solid fa-arrow-left mr-2"></i>ดูแคมเปญอื่นที่เปิดรับ
                </a>
            </div>

        <?php else: ?>
            <?php $style = getCampStyle($campaign['type']); ?>
            
            <!-- Invite Banner -->
            <div class="bg-gradient-to-br from-[#0052CC] to-[#0070f3] rounded-[2.5rem] p-7 text-white relative overflow-hidden shadow-2xl">
                <div class="absolute -right-6 -top-6 w-32 h-32 bg-white/10 rounded-full"></div>
                <div class="relative z-10 flex items-center gap-4">
                    <div class="w-12 h-12 bg-white/20 rounded-2xl flex items-center justify-center text-xl">
                        <i class="fa-solid fa-envelope-open-text"></i>
                    </div>
                    <div>
                        <p class="text-blue-100 text-[10px] font-black uppercase tracking-[0.2em] mb-1">Exclusive Invite</p>
                        <p class="text-white font-black text-sm">คุณได้รับเชิญเข้าร่วมกิจกรรมนี้เป็นพิเศษ</p>
                    </div>
                </div>
            </div>

            <!-- Campaign Info -->
            <div class="bg-white rounded-[3rem] border border-slate-100 shadow-xl overflow-hidden relative">
                <?php if ($isFull): ?>
                    <div class="absolute inset-0 bg-white/60 backdrop-blur-[2px] z-20 flex items-center justify-center">
                        <div class="bg-red-500 text-white px-6 py-2 rounded-full font-black text-sm shadow-xl -rotate-6 tracking-widest">FULLY BOOKED</div>
                    </div>
                <?php endif; ?>

                <div class="p-8">
                    <div class="flex justify-between items-start mb-6">
                        <span class="px-4 py-1.5 rounded-xl border <?= $style['class'] ?> text-[10px] font-black uppercase tracking-widest">
                            <i class="fa-solid <?= $style['icon'] ?> mr-1.5"></i><?= $style['label'] ?>
                        </span>
                        <?php if ($campaign['available_until']): ?>
                            <div class="text-right">
                                <p class="text-slate-300 text-[8px] font-black uppercase tracking-widest leading-none mb-1">Available Until</p>
                                <p class="text-red-500 font-black text-[10px]"><?= date('d M Y', strtotime($campaign['available_until'])) ?></p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <h1 class="text-slate-900 font-black text-2xl leading-tight mb-4"><?= htmlspecialchars($campaign['title']) ?></h1>
                    <div class="bg-slate-50/50 rounded-2xl p-5 mb-8 border border-slate-50">
                        <p class="text-slate-500 text-[13px] leading-relaxed font-medium">
                            <?= nl2br(htmlspecialchars($campaign['description'] ?: 'ไม่มีรายละเอียดเพิ่มเติมสำหรับกิจกรรมนี้')) ?>
                        </p>
                    </div>

                    <!-- Quota Card -->
                    <div class="grid grid-cols-2 gap-4 mb-8">
                        <div class="bg-slate-50 rounded-2xl p-5 border border-slate-100 text-center">
                            <p class="text-slate-400 text-[8px] font-black uppercase tracking-widest mb-2">Available Seats</p>
                            <p class="text-2xl font-black <?= $remaining <= 10 ? 'text-red-500' : 'text-blue-600' ?>"><?= number_format($remaining) ?></p>
                        </div>
                        <div class="bg-slate-50 rounded-2xl p-5 border border-slate-100 text-center">
                            <p class="text-slate-400 text-[8px] font-black uppercase tracking-widest mb-2">Total Capacity</p>
                            <p class="text-2xl font-black text-slate-800"><?= number_format($campaign['total_capacity']) ?></p>
                        </div>
                    </div>

                    <?php if ($campaign['is_auto_approve']): ?>
                        <div class="flex items-center gap-3 text-[11px] font-black text-blue-600 bg-blue-50 rounded-2xl px-5 py-4">
                            <i class="fa-solid fa-bolt text-yellow-500 text-sm"></i> 
                            อนุมัติสิทธิ์อัตโนมัติทันทีหลังลงทะเบียน
                        </div>
                    <?php else: ?>
                        <div class="flex items-center gap-3 text-[11px] font-black text-slate-400 bg-slate-50 rounded-2xl px-5 py-4">
                            <i class="fa-solid fa-user-shield text-sm"></i> 
                            การลงทะเบียนต้องรอแอดมินตรวจสอบ
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="space-y-4 pt-2">
                <?php if (!$isFull): ?>
                    <a href="booking_date.php?campaign_id=<?= (int)$campaign['id'] ?>"
                       class="flex items-center justify-center gap-4 w-full h-20 bg-blue-600 text-white font-black rounded-3xl shadow-[0_20px_40px_rgba(0,82,204,0.3)] active:scale-95 transition-all text-base tracking-widest uppercase">
                        <i class="fa-solid fa-calendar-check text-xl"></i>
                        Book My Slot Now
                    </a>
                <?php else: ?>
                    <button disabled class="flex items-center justify-center gap-4 w-full h-20 bg-slate-100 text-slate-300 font-black rounded-3xl cursor-not-allowed text-base tracking-widest uppercase">
                        <i class="fa-solid fa-lock text-xl"></i>
                        Fully Booked
                    </button>
                <?php endif; ?>
                
                <a href="hub.php" class="flex items-center justify-center gap-2 w-full py-4 text-xs font-black text-slate-400 hover:text-blue-600 transition-colors uppercase tracking-widest">
                    <i class="fa-solid fa-house-user text-sm"></i> Back to Dashboard
                </a>
            </div>

        <?php endif; ?>
         class="flex items-center justify-center gap-3 w-full bg-[#0052CC] hover:bg-blue-700 text-white font-bold py-4 rounded-2xl shadow-lg shadow-blue-200 transition-all active:scale-[0.98] text-base">
        <i class="fa-solid fa-calendar-check text-lg"></i>
        จองคิวสำหรับแคมเปญนี้
      </a>
    <?php else: ?>
      <button disabled
        class="flex items-center justify-center gap-3 w-full bg-gray-100 text-gray-400 font-bold py-4 rounded-2xl cursor-not-allowed text-base">
        <i class="fa-solid fa-lock text-lg"></i>
        ที่นั่งเต็มแล้ว
      </button>
    <?php endif; ?>
    <a href="my_bookings.php" class="flex items-center justify-center gap-2 w-full py-3 text-sm font-bold text-gray-400 hover:text-[#0052CC] transition-colors">
      <i class="fa-solid fa-receipt text-xs"></i> ตรวจสอบการจองของฉัน
    </a>
  </div>

<?php endif; ?>
</div>

<?php render_footer(); ?>
