<?php
// user/my_bookings.php — Premium Booking History (Production)
declare(strict_types=1);
session_start();
require_once __DIR__ . '/../config.php';
check_maintenance('e_campaign');

$lineUserId = $_SESSION['line_user_id'] ?? '';
if ($lineUserId === '') {
    header('Location: index.php');
    exit;
}

// Fetch Bookings
$bookings = [];
try {
    $pdo = db();
    $sql = "
        SELECT 
            a.id AS appointment_id, 
            a.status, 
            a.attended_at,
            s.slot_date, 
            s.start_time,
            c.title AS campaign_title,
            c.type AS camp_type,
            c.description AS campaign_desc
        FROM camp_bookings a
        JOIN camp_list c ON a.campaign_id = c.id
        JOIN camp_slots s ON a.slot_id = s.id
        WHERE a.student_id = :sid
        ORDER BY s.slot_date DESC, s.start_time DESC
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':sid' => $user['id']]);
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("my_bookings error: " . $e->getMessage());
}

// Categorize
$upcomingBookings = [];
$historyBookings  = [];
$today = date('Y-m-d');

foreach ($bookings as $b) {
    $isAttended      = !empty($b['attended_at']);
    $isCancelled     = in_array($b['status'], ['cancelled', 'cancelled_by_admin']);
    $isPast          = ($b['slot_date'] < $today);

    if ($isAttended || $isCancelled || $isPast) {
        $historyBookings[] = $b;
    } else {
        $upcomingBookings[] = $b;
    }
}

function getStatusInfo($b): array {
    $status = $b['status'];
    $isAttended = !empty($b['attended_at']);
    
    if ($isAttended) {
        return ['label' => 'เช็คอินแล้ว', 'class' => 'bg-emerald-50 text-emerald-600 border-emerald-100', 'icon' => 'fa-circle-check'];
    }
    return match($status) {
        'confirmed' => ['label' => 'ยืนยันแล้ว', 'class' => 'bg-blue-50 text-blue-600 border-blue-100', 'icon' => 'fa-calendar-check'],
        'booked'    => ['label' => 'รออนุมัติ', 'class' => 'bg-amber-50 text-amber-600 border-amber-100', 'icon' => 'fa-clock-rotate-left'],
        'cancelled', 'cancelled_by_admin' => ['label' => 'ยกเลิกแล้ว', 'class' => 'bg-red-50 text-red-600 border-red-100', 'icon' => 'fa-circle-xmark'],
        default     => ['label' => 'รอดำเนินการ', 'class' => 'bg-gray-50 text-gray-600 border-gray-100', 'icon' => 'fa-ellipsis'],
    };
}

function getCampIcon($type): string {
    return match($type) {
        'vaccine'      => 'fa-syringe',
        'health_check' => 'fa-stethoscope',
        default        => 'fa-calendar-day',
    };
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>ประวัติการจอง - RSU Medical</title>
    <link rel="icon" type="image/x-icon" href="../favicon.ico">
    <script src="https://cdn.tailwindcss.com/3.4.1"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        @font-face {
            font-family: 'RSU';
            src: url('../assets/fonts/RSU_Regular.ttf') format('truetype');
            font-weight: normal;
            font-style: normal;
        }
        @font-face {
            font-family: 'RSU';
            src: url('../assets/fonts/RSU_BOLD.ttf') format('truetype');
            font-weight: bold;
            font-style: normal;
        }
        body { font-family: 'RSU', sans-serif; background-color: #F8FAFF; -webkit-tap-highlight-color: transparent; }
        .glass-header { background: rgba(255, 255, 255, 0.8); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px); }
        .custom-scrollbar::-webkit-scrollbar { display: none; }
        .active-tab { background: #0052CC; color: white; box-shadow: 0 10px 20px rgba(0, 82, 204, 0.2); }
    </style>
</head>
<body class="text-slate-900 pb-32">

    <div class="max-w-md mx-auto relative min-h-screen">
        
        <!-- ── Navigation Header ── -->
        <header class="glass-header sticky top-0 z-[60] px-6 py-5 flex items-center justify-between border-b border-slate-100">
            <button onclick="window.location.href='hub.php'" class="w-11 h-11 flex items-center justify-center bg-slate-50 rounded-2xl text-slate-400 active:scale-90 transition-all">
                <i class="fa-solid fa-chevron-left"></i>
            </button>
            <h1 class="text-lg font-black text-slate-900 tracking-tight">ประวัติการนัดหมาย</h1>
            <button onclick="location.reload()" class="w-11 h-11 flex items-center justify-center text-slate-400 active:rotate-180 transition-all duration-500">
                <i class="fa-solid fa-rotate-right"></i>
            </button>
        </header>

        <main class="px-6 pt-8 space-y-8">
            
            <!-- ── Stats Overview ── -->
            <div class="grid grid-cols-3 gap-3">
                <div class="bg-white rounded-[2rem] p-4 text-center border border-slate-50 shadow-sm">
                    <p class="text-blue-600 font-black text-xl leading-none mb-1"><?= count($upcomingBookings) ?></p>
                    <p class="text-[9px] text-slate-400 font-black uppercase tracking-widest leading-none">นัดหมายใหม่</p>
                </div>
                <div class="bg-white rounded-[2rem] p-4 text-center border border-slate-50 shadow-sm">
                    <p class="text-slate-900 font-black text-xl leading-none mb-1"><?= count($historyBookings) ?></p>
                    <p class="text-[9px] text-slate-400 font-black uppercase tracking-widest leading-none">ประวัติรวม</p>
                </div>
                <div class="bg-white rounded-[2rem] p-4 text-center border border-slate-50 shadow-sm">
                    <?php $attended = count(array_filter($bookings, fn($b) => !empty($b['attended_at']))); ?>
                    <p class="text-emerald-500 font-black text-xl leading-none mb-1"><?= $attended ?></p>
                    <p class="text-[9px] text-slate-400 font-black uppercase tracking-widest leading-none">รับบริการแล้ว</p>
                </div>
            </div>

            <!-- ── Tab Control ── -->
            <div class="bg-white/50 backdrop-blur-md p-1.5 rounded-[2rem] flex items-center border border-slate-100">
                <button onclick="switchTab('upcoming')" id="tab-upcoming" class="active-tab flex-1 h-12 rounded-[1.8rem] text-sm font-black transition-all duration-300">
                    รายการนัดหมาย
                </button>
                <button onclick="switchTab('history')" id="tab-history" class="flex-1 h-12 rounded-[1.8rem] text-sm font-black text-slate-400 transition-all duration-300">
                    ประวัติการรักษา
                </button>
            </div>

            <!-- ── Booking List ── -->
            <div id="content-upcoming" class="space-y-4 animate-in fade-in slide-in-from-bottom-4 duration-500">
                <?php if (empty($upcomingBookings)): ?>
                    <div class="py-20 text-center space-y-4">
                        <div class="w-20 h-24 bg-slate-50 rounded-[2.5rem] flex items-center justify-center mx-auto text-slate-200">
                            <i class="fa-solid fa-calendar-xmark text-4xl"></i>
                        </div>
                        <p class="text-slate-400 font-bold text-sm">ไม่มีนัดหมายที่รอรับบริการ</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($upcomingBookings as $b): 
                        $status = getStatusInfo($b);
                        $icon = getCampIcon($b['camp_type']);
                    ?>
                    <div onclick="openDetails(<?= htmlspecialchars(json_encode($b)) ?>)" class="bg-white rounded-[2.5rem] p-6 border border-slate-100 shadow-[0_15px_30px_rgba(0,0,0,0.02)] active:scale-[0.98] transition-all group cursor-pointer relative overflow-hidden">
                        <div class="flex items-start justify-between mb-5">
                            <div class="flex items-center gap-4 text-left">
                                <div class="w-12 h-12 bg-blue-50 rounded-2xl flex items-center justify-center text-blue-600 border border-blue-100 shadow-inner group-hover:scale-110 transition-transform">
                                    <i class="fa-solid <?= $icon ?> text-base"></i>
                                </div>
                                <div>
                                    <h3 class="text-slate-900 font-black text-sm leading-tight mb-1"><?= htmlspecialchars($b['campaign_title']) ?></h3>
                                    <div class="flex items-center gap-2">
                                        <i class="fa-regular fa-clock text-blue-400 text-[10px]"></i>
                                        <p class="text-slate-400 text-[10px] font-bold uppercase tracking-widest leading-none"><?= date('H:i', strtotime($b['start_time'])) ?> น.</p>
                                    </div>
                                </div>
                            </div>
                            <span class="px-3 py-1.5 rounded-xl <?= $status['class'] ?> text-[10px] font-black uppercase tracking-widest border"><?= $status['label'] ?></span>
                        </div>
                        <div class="flex items-center justify-between pt-5 border-t border-slate-50">
                            <div class="flex items-center gap-2">
                                <i class="fa-regular fa-calendar-days text-slate-300"></i>
                                <span class="text-slate-500 font-black text-xs"><?= date('d F Y', strtotime($b['slot_date'])) ?></span>
                            </div>
                            <i class="fa-solid fa-chevron-right text-slate-200 text-[10px]"></i>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div id="content-history" class="hidden space-y-4 animate-in fade-in slide-in-from-bottom-4 duration-500">
                <?php if (empty($historyBookings)): ?>
                    <div class="py-20 text-center space-y-4">
                        <div class="w-20 h-24 bg-slate-50 rounded-[2.5rem] flex items-center justify-center mx-auto text-slate-200">
                            <i class="fa-solid fa-folder-open text-4xl"></i>
                        </div>
                        <p class="text-slate-400 font-bold text-sm">ยังไม่พบประวัติการรับบริการ</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($historyBookings as $b): 
                        $status = getStatusInfo($b);
                        $icon = getCampIcon($b['camp_type']);
                    ?>
                    <div class="bg-white rounded-[2.5rem] p-6 border border-slate-100 opacity-80 active:scale-[0.98] transition-all group">
                        <div class="flex items-start justify-between mb-5">
                            <div class="flex items-center gap-4 text-left">
                                <div class="w-12 h-12 bg-slate-50 rounded-2xl flex items-center justify-center text-slate-400 border border-slate-100">
                                    <i class="fa-solid <?= $icon ?> text-base"></i>
                                </div>
                                <div>
                                    <h3 class="text-slate-900 font-black text-sm leading-tight mb-1"><?= htmlspecialchars($b['campaign_title']) ?></h3>
                                    <p class="text-slate-400 text-[10px] font-bold uppercase tracking-widest leading-none"><?= date('d M Y', strtotime($b['slot_date'])) ?></p>
                                </div>
                            </div>
                            <span class="px-3 py-1.5 rounded-xl <?= $status['class'] ?> text-[10px] font-black uppercase tracking-widest border"><?= $status['label'] ?></span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

        </main>

        <!-- ── Premium Bottom Navigation ── -->
        <nav class="fixed bottom-0 left-0 right-0 z-[70] bg-white/90 backdrop-blur-2xl border-t border-slate-50 px-8 py-4 pb-10 flex justify-between items-center max-w-md mx-auto shadow-[0_-20px_40px_rgba(0,0,0,0.04)]">
            <button onclick="window.location.href='hub.php'" class="flex flex-col items-center gap-1.5 text-slate-300 transition-all hover:text-slate-500">
                <i class="fa-solid fa-house-chimney text-xl"></i>
                <span class="text-[8px] font-black uppercase tracking-[0.1em]">Home</span>
            </button>
            <button onclick="location.reload()" class="flex flex-col items-center gap-1.5 text-blue-600 transition-all scale-110">
                <i class="fa-solid fa-calendar-day text-xl"></i>
                <span class="text-[8px] font-black uppercase tracking-[0.1em]">Booking</span>
            </button>
            <div class="relative -mt-14">
                <button onclick="window.location.href='hub.php?action=campaigns'" class="w-16 h-16 bg-blue-600 rounded-[1.8rem] rotate-45 flex items-center justify-center text-white shadow-[0_15px_30px_rgba(0,82,204,0.4)] border-[6px] border-[#F8FAFF] active:scale-90 transition-all group">
                    <i class="fa-solid fa-plus text-2xl -rotate-45 group-hover:scale-125 transition-transform"></i>
                </button>
            </div>
            <button onclick="window.location.href='hub.php'" class="flex flex-col items-center gap-1.5 text-slate-300 transition-all hover:text-slate-500">
                <i class="fa-solid fa-heart-pulse text-xl"></i>
                <span class="text-[8px] font-black uppercase tracking-[0.1em]">Health</span>
            </button>
            <button onclick="window.location.href='hub.php'" class="flex flex-col items-center gap-1.5 text-slate-300 transition-all hover:text-slate-500">
                <i class="fa-solid fa-user-ninja text-xl"></i>
                <span class="text-[8px] font-black uppercase tracking-[0.1em]">Account</span>
            </button>
        </nav>

    </div>

    <!-- ── Details Bottom Sheet ── -->
    <div id="details-modal" class="fixed inset-0 z-[100] hidden flex items-end justify-center p-0"><div class="absolute inset-0 bg-slate-900/60 backdrop-blur-md" onclick="hideDetails()"></div><div class="relative bg-white w-full max-w-[430px] rounded-t-[3.5rem] shadow-2xl animate-in slide-in-from-bottom duration-300 flex flex-col max-h-[85vh] overflow-hidden"><div class="w-14 h-1.5 bg-slate-100 rounded-full mx-auto mt-6 mb-2 flex-shrink-0"></div><div class="px-10 pt-8 pb-8 text-center"><div id="det-icon-wrap" class="w-20 h-20 bg-blue-50 rounded-[2.5rem] flex items-center justify-center mx-auto mb-6 text-blue-600 text-3xl shadow-inner"><i id="det-icon" class="fa-solid fa-calendar-check"></i></div><h3 id="det-title" class="text-slate-900 font-black text-2xl tracking-tight mb-2 leading-tight"></h3><span id="det-status" class="inline-block px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest mb-10 border"></span><div class="grid grid-cols-2 gap-4 mb-10"><div class="bg-slate-50 p-6 rounded-[2rem] border border-slate-100 text-left"><p class="text-slate-400 text-[9px] font-black uppercase tracking-[0.2em] mb-2 leading-none">วันที่นัดหมาย</p><p id="det-date" class="text-slate-900 font-black text-sm tracking-tight"></p></div><div class="bg-slate-50 p-6 rounded-[2rem] border border-slate-100 text-left"><p class="text-slate-400 text-[9px] font-black uppercase tracking-[0.2em] mb-2 leading-none">เวลานัดหมาย</p><p id="det-time" class="text-slate-900 font-black text-sm tracking-tight"></p></div></div><div class="space-y-4"><div id="qr-container" class="bg-white p-6 rounded-[2.5rem] border border-slate-100 shadow-sm mb-6 flex flex-col items-center"><div id="qrcode" class="mb-4"></div><p class="text-slate-300 text-[10px] font-black uppercase tracking-widest">Appointment ID: <span id="det-id" class="text-slate-900"></span></p></div><div id="cancel-section" class="hidden"><form action="cancel_booking.php" method="POST" onsubmit="return confirmCancel(event)"><input type="hidden" name="appointment_id" id="cancel-id"><button type="submit" class="w-full h-18 bg-red-50 text-red-600 font-black rounded-2xl active:scale-95 transition-all text-sm tracking-wide flex items-center justify-center gap-3 border border-red-100"><i class="fa-solid fa-trash-can"></i> ยกเลิกการจองนี้</button></form></div><button onclick="hideDetails()" class="w-full h-18 bg-slate-900 text-white font-black rounded-2xl active:scale-95 transition-all text-sm tracking-widest shadow-xl shadow-slate-200">ย้อนกลับ</button></div></div></div></div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Check for URL parameters to show alerts
        window.addEventListener('DOMContentLoaded', () => {
            const params = new URLSearchParams(window.location.search);
            
            if (params.has('error')) {
                const error = params.get('error');
                if (error === 'already_booked') {
                    Swal.fire({
                        title: 'คุณจองกิจกรรมนี้ไปแล้ว',
                        text: 'ไม่สามารถจองกิจกรรมเดิมซ้ำได้ คุณสามารถดูรายละเอียดหรือยกเลิกนัดหมายเดิมได้ในหน้านี้',
                        icon: 'info',
                        confirmButtonText: 'รับทราบ',
                        confirmButtonColor: '#0052CC',
                        customClass: { popup: 'rounded-[2.5rem]', confirmButton: 'rounded-xl px-10' }
                    });
                }
            }

            if (params.has('msg')) {
                const msg = params.get('msg');
                if (msg === 'cancelled_success') {
                    Swal.fire({
                        title: 'ยกเลิกนัดหมายแล้ว',
                        text: 'ระบบได้ทำการยกเลิกนัดหมายของคุณเรียบร้อยแล้ว',
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false,
                        customClass: { popup: 'rounded-[2.5rem]' }
                    });
                } else if (msg === 'error') {
                    Swal.fire({
                        title: 'เกิดข้อผิดพลาด',
                        text: 'ไม่สามารถดำเนินการได้ในขณะนี้ กรุณาลองใหม่อีกครั้ง',
                        icon: 'error',
                        confirmButtonColor: '#0052CC',
                        customClass: { popup: 'rounded-[2.5rem]', confirmButton: 'rounded-xl' }
                    });
                }
            }
        });

        function switchTab(tab) {
            const upBtn = document.getElementById('tab-upcoming');
            const hisBtn = document.getElementById('tab-history');
            const upContent = document.getElementById('content-upcoming');
            const hisContent = document.getElementById('content-history');
            
            if (tab === 'upcoming') {
                upBtn.classList.add('active-tab'); upBtn.classList.remove('text-slate-400');
                hisBtn.classList.remove('active-tab'); hisBtn.classList.add('text-slate-400');
                upContent.classList.remove('hidden'); hisContent.classList.add('hidden');
            } else {
                hisBtn.classList.add('active-tab'); hisBtn.classList.remove('text-slate-400');
                upBtn.classList.remove('active-tab'); upBtn.classList.add('text-slate-400');
                hisContent.classList.remove('hidden'); upContent.classList.add('hidden');
            }
        }

        let qr = null;
        function openDetails(b) {
            document.getElementById('det-title').innerText = b.campaign_title;
            document.getElementById('det-date').innerText = b.slot_date;
            document.getElementById('det-time').innerText = b.start_time + ' น.';
            document.getElementById('det-id').innerText = b.appointment_id;
            
            const status = getStatus(b);
            const statusEl = document.getElementById('det-status');
            statusEl.innerText = status.label;
            statusEl.className = `inline-block px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest mb-10 border ${status.class}`;
            
            // QR Code
            const qrTarget = document.getElementById('qrcode');
            qrTarget.innerHTML = '';
            qr = new QRCode(qrTarget, { text: b.appointment_id.toString(), width: 160, height: 160, colorDark : "#0f172a", colorLight : "#ffffff" });
            
            // Cancel button
            const cancelSect = document.getElementById('cancel-section');
            if (b.status === 'booked' || b.status === 'confirmed') {
                cancelSect.classList.remove('hidden');
                document.getElementById('cancel-id').value = b.appointment_id;
            } else {
                cancelSect.classList.add('hidden');
            }
            
            document.getElementById('details-modal').classList.remove('hidden');
            document.getElementById('details-modal').classList.add('flex');
        }

        function hideDetails() { document.getElementById('details-modal').classList.add('hidden'); }

        function getStatus(b) {
            if (b.attended_at) return { label: 'เช็คอินแล้ว', class: 'bg-emerald-50 text-emerald-600 border-emerald-100' };
            if (b.status === 'confirmed') return { label: 'ยืนยันแล้ว', class: 'bg-blue-50 text-blue-600 border-blue-100' };
            if (b.status === 'booked') return { label: 'รออนุมัติ', class: 'bg-amber-50 text-amber-600 border-amber-100' };
            return { label: 'ยกเลิกแล้ว', class: 'bg-red-50 text-red-600 border-red-100' };
        }

        function confirmCancel(e) {
            e.preventDefault();
            Swal.fire({
                title: 'ยกเลิกการจอง?',
                text: "คุณแน่ใจหรือไม่ที่จะยกเลิกนัดหมายนี้? การกระทำนี้ไม่สามารถย้อนกลับได้",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#94a3b8',
                confirmButtonText: 'ยืนยันการยกเลิก',
                cancelButtonText: 'ย้อนกลับ',
                customClass: { popup: 'rounded-[2rem] font-rsu', confirmButton: 'rounded-xl', cancelButton: 'rounded-xl' }
            }).then((result) => { if (result.isConfirmed) e.target.submit(); });
        }
    </script>
</body>
</html>
