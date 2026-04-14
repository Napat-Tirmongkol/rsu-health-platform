<?php
// admin/users.php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../admin/includes/auth.php';

$pdo = db();
$message = '';
$messageType = '';

// ==========================================
// ส่วนจัดการ POST Request (อัปเดตข้อมูลนักศึกษา)
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'edit_user') {
        $userId = (int)($_POST['user_id'] ?? 0);
        $fullName = trim($_POST['full_name'] ?? '');
        $studentId = trim($_POST['student_personnel_id'] ?? '');
        $phone = trim($_POST['phone_number'] ?? '');

        if ($userId > 0 && $fullName !== '' && $studentId !== '') {
            try {
                $sql = "UPDATE sys_users 
                        SET full_name = :name, 
                            student_personnel_id = :studentid, 
                            citizen_id = :citizenid,
                            phone_number = :phone,
                            status = :status 
                        WHERE id = :id";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':name' => $fullName,
                    ':studentid' => trim($_POST['student_personnel_id'] ?? ''),
                    ':citizenid' => trim($_POST['citizen_id'] ?? ''),
                    ':phone' => $phone,
                    ':status' => trim($_POST['status'] ?? ''),
                    ':id' => $userId
                ]);
                $message = "อัปเดตข้อมูลนักศึกษาเรียบร้อยแล้ว!";
                log_activity("Updated User Profile", "แก้ไขข้อมูลงรายชื่อ: $fullName (#$studentId)");
                $messageType = "success";
            } catch (PDOException $e) {
                $message = "เกิดข้อผิดพลาด: " . $e->getMessage();
                $messageType = "error";
            }
        } else {
            $message = "กรุณากรอกข้อมูลให้ครบถ้วน";
            $messageType = "error";
        }
    }
}

// ==========================================
// ดึงข้อมูลและระบบค้นหา (แก้ไข Error HY093)
// ==========================================
$search = $_GET['search'] ?? '';
$users = [];
$params = [];

try {
    $sql = "SELECT * FROM sys_users WHERE 1=1";
    
    if ($search !== '') {
        // แยกชื่อ Parameter ให้ไม่ซ้ำกัน
        $sql .= " AND (full_name LIKE :search1 OR student_personnel_id LIKE :search2 OR phone_number LIKE :search3)";
        $searchTerm = "%{$search}%";
        $params[':search1'] = $searchTerm;
        $params[':search2'] = $searchTerm;
        $params[':search3'] = $searchTerm;
    }
    
    $sql .= " ORDER BY created_at DESC"; // เรียงจากคนที่สมัครล่าสุดขึ้นก่อน
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $users = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("portal users error: " . $e->getMessage()); $users = [];
}

// portal/users.php ไม่แสดง admin sidebar ของ e-campaign
$_GET['layout'] = 'none';
require_once __DIR__ . '/../admin/includes/header.php';
?>

<style>
    /* ── Premium UI Elements ── */
    @keyframes slideUpFade {
        0% { opacity: 0; transform: translateY(20px) scale(0.98); }
        100% { opacity: 1; transform: translateY(0) scale(1); }
    }
    .animate-slide-up { animation: slideUpFade 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards; }

    .premium-input {
        width: 100%;
        padding: 0.875rem 1.25rem;
        background-color: #f9fafb;
        border: 1.5px solid #e5e7eb;
        border-radius: 1.25rem;
        font-size: 0.9375rem;
        font-weight: 600;
        color: #111827;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        outline: none;
    }

    .premium-input:focus {
        background-color: #ffffff;
        border-color: #f59e0b; /* Orange-ish for users */
        box-shadow: 0 0 0 4px rgba(245, 158, 11, 0.1);
        transform: translateY(-1px);
    }

    .premium-modal-header {
        background: rgba(255, 255, 255, 0.8);
        backdrop-filter: blur(12px);
        border-bottom: 1px solid #f3f4f6;
    }

    /* Custom Scrollbar */
    .custom-scrollbar::-webkit-scrollbar { width: 5px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #e5e7eb; border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #d1d5db; }
</style>

<div class="max-w-6xl mx-auto px-4 py-8">
<?php
    // (A) PREPARE HEADER ACTIONS
    $header_actions = '
    <div class="flex items-center gap-3">
        <a href="index.php" class="bg-white border border-gray-100 hover:bg-gray-50 text-gray-500 px-5 py-2.5 rounded-2xl font-bold flex items-center gap-2 transition-all shadow-sm active:scale-95 group text-sm">
            <i class="fa-solid fa-arrow-left group-hover:-translate-x-1 transition-transform"></i> Back to Portal
        </a>
        <div class="relative group hidden sm:block">
            <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-300 group-focus-within:text-blue-500 transition-colors"></i>
            <input type="text" placeholder="พิมพ์เพื่อกรองด่วน..." 
                   class="pl-10 pr-4 py-2.5 bg-white/50 border border-gray-100 rounded-xl text-xs font-bold outline-none focus:ring-2 focus:ring-blue-100 focus:border-blue-400 transition-all w-64 shadow-sm" 
                   onkeyup="filterUsers(this.value)">
        </div>
    </div>';
    renderPageHeader("User Directory", "ศูนย์กลางจัดการรายชื่อ: ค้นหา ตรวจสอบความถูกต้อง และจัดการประวัติผู้ใช้งานทั้งแคมเปญ", $header_actions); 
?>

    <!-- User Stats Summary -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8 animate-slide-up">
        <div class="bg-gradient-to-br from-white to-gray-50/50 p-6 rounded-[28px] border border-gray-100 shadow-sm">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-blue-100 rounded-2xl flex items-center justify-center text-[#0052CC]">
                    <i class="fa-solid fa-users text-xl"></i>
                </div>
                <div>
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Master Directory</p>
                    <p class="text-2xl font-black text-gray-900"><?= number_format(count($users)) ?> Users</p>
                </div>
            </div>
        </div>
        <div class="bg-gradient-to-br from-white to-gray-50/50 p-6 rounded-[28px] border border-gray-100 shadow-sm relative overflow-hidden group">
            <div class="absolute -right-4 -bottom-4 opacity-5 group-hover:scale-110 transition-transform duration-700">
                <i class="fa-solid fa-address-card text-8xl text-indigo-900"></i>
            </div>
            <div class="flex items-center gap-4 relative z-10">
                <div class="w-12 h-12 bg-indigo-100 rounded-2xl flex items-center justify-center text-indigo-600">
                    <i class="fa-solid fa-id-card-clip text-xl transition-transform group-hover:rotate-12"></i>
                </div>
                <div>
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Active Accounts</p>
<p class="text-2xl font-black text-gray-900">
    <?php 
        // นับเฉพาะ User ที่มีประวัติจองแคมเปญ (camp_bookings) หรือ ประวัติยืมของ (borrow_records)
        $activeSql = "
            SELECT COUNT(DISTINCT id) 
            FROM sys_users 
            WHERE id IN (SELECT student_id FROM camp_bookings WHERE student_id IS NOT NULL)
               OR id IN (SELECT borrower_student_id FROM borrow_records WHERE borrower_student_id IS NOT NULL)
        ";
        $activeCount = $pdo->query($activeSql)->fetchColumn();
        echo number_format($activeCount);
    ?>
</p>
                </div>
            </div>
        </div>
        <div class="bg-[#0052CC] p-7 rounded-[32px] shadow-lg shadow-blue-200/50 relative overflow-hidden group pointer-events-none sm:pointer-events-auto">
            <div class="absolute -right-6 -top-6 w-32 h-32 bg-white/10 rounded-full blur-2xl group-hover:bg-white/20 transition-all duration-500"></div>
            <div class="relative z-10">
                <p class="text-[11px] font-black text-white/50 uppercase tracking-[0.2em] mb-1">Advanced Tool</p>
                <div class="flex items-center justify-between">
                    <h3 class="text-xl font-bold text-white tracking-tight leading-tight">Sync Users<br><span class="opacity-60 font-medium text-sm text-blue-100">Maintain Database</span></h3>
                    <div class="w-12 h-12 bg-white/20 rounded-2xl flex items-center justify-center text-white backdrop-blur-sm group-hover:scale-110 transition-transform shadow-xl">
                        <i class="fa-solid fa-arrows-rotate text-lg"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main User Table Content -->
    <div class="bg-white rounded-[32px] shadow-2xl shadow-gray-200/40 border border-gray-100/50 overflow-hidden animate-slide-up" style="animation-delay: 100ms;">
        <div class="px-8 py-7 border-b border-gray-50 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex items-center gap-3">
                <div class="w-2 h-6 bg-blue-600 rounded-full"></div>
                <h3 class="font-black text-gray-900 text-sm uppercase tracking-widest">Master Records</h3>
            </div>
            
            <form method="GET" class="flex items-center gap-3">
                <div class="relative">
                    <i class="fa-solid fa-filter absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" 
                           placeholder="ระบุรหัสประจำตัว หรือชื่อเพื่อค้นหา..." 
                           class="pl-9 pr-4 py-2.5 bg-gray-50 border border-gray-100 rounded-xl text-xs font-bold outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all w-full md:w-80 shadow-sm border-gray-200/50">
                </div>
                <button type="submit" class="bg-gray-900 hover:bg-black text-white px-5 py-2.5 rounded-xl font-bold text-xs transition-all active:scale-95 shadow-lg shadow-gray-200 shadow-md">
                    <span>ตกลง</span>
                </button>
            </form>
        </div>

        <table class="w-full text-left border-collapse" id="userTable">
            <thead>
                <tr class="bg-gray-50/30 border-b border-gray-50 text-[10px] uppercase tracking-[0.2em] font-black text-gray-400">
                    <th class="px-8 py-5">Verified Identity</th>
                    <th class="px-8 py-5">Contact Node</th>
                    <th class="px-8 py-5">Onboarded</th>
                    <th class="px-8 py-5 text-right">Service</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                <?php if (empty($users)): ?>
                <tr>
                    <td colspan="4" class="px-8 py-20 text-center">
                        <div class="flex flex-col items-center opacity-30">
                            <i class="fa-solid fa-ghost text-6xl mb-4"></i>
                            <p class="font-black uppercase tracking-widest text-sm text-gray-400">No records found</p>
                            <p class="text-xs font-bold mt-2">ไม่พบรายชื่อที่ต้องการค้นหาในขณะนี้</p>
                        </div>
                    </td>
                </tr>
                <?php else: ?>
                    <?php foreach ($users as $user): ?>
                    <tr class="hover:bg-blue-50/20 transition-all group/row cursor-default">
                        <td class="px-8 py-6">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 bg-gradient-to-br from-gray-100 to-gray-200 text-gray-400 rounded-2xl flex items-center justify-center font-black shadow-sm group-hover/row:from-blue-600 group-hover/row:to-indigo-600 group-hover/row:text-white transition-all duration-500 text-sm">
                                    <?= mb_substr($user['full_name'], 0, 1) ?>
                                </div>
                                <div class="space-y-1">
                                    <div class="font-black text-gray-900 group-hover/row:text-blue-700 transition-colors uppercase tracking-tight"><?= htmlspecialchars($user['full_name']) ?></div>
                                    <div class="flex items-center gap-2">
                                        <div class="text-[10px] font-black text-gray-400 uppercase tracking-widest bg-gray-50 px-2 py-0.5 rounded-md border border-gray-100">
                                            #<?= htmlspecialchars($user['student_personnel_id'] ?? '—') ?>
                                        </div>
                                        <span class="w-1 h-1 rounded-full bg-gray-300"></span>
                                        <span class="text-[10px] text-gray-400 font-bold uppercase tracking-wider"><?= htmlspecialchars($user['status'] ?? 'Registered') ?></span>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-8 py-6">
                            <div class="space-y-2">
                                <div class="flex items-center gap-2 group/link">
                                    <div class="w-6 h-6 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-[10px] group-hover/link:bg-emerald-600 group-hover/link:text-white transition-all">
                                        <i class="fa-solid fa-phone"></i>
                                    </div>
                                    <span class="text-xs font-black text-gray-700 tracking-tight group-hover/link:text-emerald-700"><?= htmlspecialchars($user['phone_number'] ?: '---') ?></span>
                                </div>
                                <div class="flex items-center gap-2 group/link">
                                    <div class="w-6 h-6 rounded-lg bg-sky-50 text-sky-600 flex items-center justify-center text-[10px] group-hover/link:bg-sky-600 group-hover/link:text-white transition-all">
                                        <i class="fa-solid fa-envelope"></i>
                                    </div>
                                    <span class="text-[11px] font-bold text-gray-400 truncate max-w-[140px] tracking-tight group-hover/link:text-sky-700"><?= htmlspecialchars($user['email'] ?? 'No contact info') ?></span>
                                </div>
                            </div>
                        </td>
                        <td class="px-8 py-6">
                            <div class="flex flex-col">
                                <span class="text-xs font-black text-gray-800 tracking-tight"><?= date('d M Y', strtotime($user['created_at'])) ?></span>
                                <span class="text-[10px] text-gray-400 font-bold uppercase tracking-widest"><?= date('H:i', strtotime($user['created_at'])) ?> น.</span>
                            </div>
                        </td>
                        <td class="px-8 py-6 text-right">
                            <div class="flex justify-end gap-2 translate-x-4 opacity-0 group-hover/row:translate-x-0 group-hover/row:opacity-100 transition-all duration-300">
                                <button onclick='openViewModal(<?= json_encode($user) ?>)' class="w-10 h-10 flex items-center justify-center rounded-xl bg-white border border-gray-100 text-gray-400 hover:text-[#0052CC] hover:border-blue-100 hover:shadow-lg hover:shadow-blue-50 transition-all active:scale-90 shadow-sm" title="Inspect Record">
                                    <i class="fa-solid fa-search text-xs"></i>
                                </button>
                                <button onclick='openEditModal(<?= json_encode($user) ?>)' class="w-10 h-10 flex items-center justify-center rounded-xl bg-white border border-gray-100 text-gray-400 hover:text-indigo-600 hover:border-indigo-100 hover:shadow-lg hover:shadow-indigo-50 transition-all active:scale-90 shadow-sm" title="Edit Profile">
                                    <i class="fa-solid fa-user-edit text-xs"></i>
                                </button>
                                <a href="../admin/user_history.php?id=<?= $user['id'] ?>" class="w-10 h-10 flex items-center justify-center rounded-xl bg-white border border-gray-100 text-gray-400 hover:text-amber-600 hover:border-amber-100 hover:shadow-lg hover:shadow-amber-50 transition-all active:scale-90 shadow-sm" title="Transaction Log">
                                    <i class="fa-solid fa-history text-xs"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div id="editModal" class="fixed inset-0 z-50 bg-gray-900/60 backdrop-blur-sm hidden flex items-center justify-center p-4" style="z-index:200;">
    <div id="editModalBox" class="bg-white rounded-[32px] shadow-2xl w-full max-w-lg overflow-hidden transform transition-all scale-95 opacity-0 duration-300">
        <form method="POST">
            <?php csrf_field(); ?>
            <input type="hidden" name="action" value="edit_user">
            <input type="hidden" name="user_id" id="edit_user_id">

            <!-- Header -->
            <div class="px-8 py-6 premium-modal-header flex justify-between items-center sticky top-0 z-10">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-amber-100 text-amber-600 rounded-2xl flex items-center justify-center shadow-inner">
                        <i class="fa-solid fa-user-pen text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-[900] text-gray-900 tracking-tight">แก้ไขข้อมูลผู้ใช้งาน</h3>
                        <p class="text-[11px] text-gray-400 font-bold uppercase tracking-wider">ปรับปรุงรายละเอียดส่วนบุคคลและสถานะ</p>
                    </div>
                </div>
                <button type="button" onclick="closeModal('edit')" class="w-10 h-10 flex items-center justify-center rounded-2xl hover:bg-gray-100 text-gray-400 hover:text-gray-700 transition-all duration-200">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <div class="px-8 py-7 space-y-6 max-h-[70vh] overflow-y-auto custom-scrollbar bg-white/50">
                
                <div class="grid grid-cols-1 gap-5">
                    <!-- Full Name -->
                    <div>
                        <label class="block text-[11px] font-black text-gray-400 uppercase tracking-widest mb-2">ชื่อ-นามสกุล <span class="text-red-500">*</span></label>
                        <input type="text" id="edit_full_name" name="full_name" required 
                               class="premium-input" placeholder="ระบุชื่อจริง-นามสกุล">
                    </div>

                    <!-- Citizen ID -->
                    <div>
                        <label class="block text-[11px] font-black text-gray-400 uppercase tracking-widest mb-2">เลขบัตรประชาชน (13 หลัก) <span class="text-red-500">*</span></label>
                        <input type="text" id="edit_citizen_id" name="citizen_id" required maxlength="13" 
                               class="premium-input tracking-[0.1em]" placeholder="X-XXXX-XXXXX-XX-X">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <!-- Student/Staff ID -->
                        <div>
                            <label class="block text-[11px] font-black text-gray-400 uppercase tracking-widest mb-2">รหัสนักศึกษา / บุคลากร</label>
                            <input type="text" id="edit_student_id" name="student_personnel_id" maxlength="15" 
                                   class="premium-input" placeholder="7 หรือ 10 หลัก">
                        </div>
                        <!-- Phone -->
                        <div>
                            <label class="block text-[11px] font-black text-gray-400 uppercase tracking-widest mb-2">เบอร์โทรศัพท์</label>
                            <input type="text" id="edit_phone" name="phone_number" 
                                   class="premium-input" placeholder="0XXXXXXXXX">
                        </div>
                    </div>

                    <!-- User Type -->
                    <div>
                        <label class="block text-[11px] font-black text-gray-400 uppercase tracking-widest mb-2">ประเภทผู้ใช้งาน <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <select name="status" id="edit_status" required class="premium-input appearance-none">
                                <option value="">-- เลือกประเภท --</option>
                                <option value="student">นักศึกษา (Student)</option>
                                <option value="staff">บุคลากร/อาจารย์ (Personnel)</option>
                                <option value="other">บุคคลทั่วไป (External)</option>
                            </select>
                            <i class="fa-solid fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-gray-300 pointer-events-none text-xs"></i>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Footer -->
            <div class="px-8 py-6 border-t border-gray-100 flex gap-4 bg-gray-50/80 backdrop-blur-md">
                <button type="button" onclick="closeModal('edit')" 
                        class="px-8 py-3.5 rounded-2xl font-black text-gray-500 bg-white border border-gray-200 hover:bg-gray-100 hover:text-gray-700 transition-all duration-200 active:scale-95 text-sm uppercase tracking-widest shadow-sm">
                    ยกเลิก
                </button>
                <button type="submit" 
                        class="flex-1 py-3.5 rounded-2xl font-black text-white text-sm uppercase tracking-widest active:scale-95 transition-all duration-300 shadow-xl"
                        style="background: linear-gradient(135deg, #f59e0b, #d97706); box-shadow: 0 8px 25px rgba(245, 158, 11, 0.35);">
                    <i class="fa-solid fa-save mr-2"></i> บันทึกข้อมูล
                </button>
            </div>
        </form>
    </div>
</div>

<!-- View Modal -->
<div id="viewModal" class="fixed inset-0 z-50 bg-gray-900/60 backdrop-blur-sm hidden flex items-center justify-center p-4" style="z-index:200;">
    <div id="viewModalBox" class="bg-white rounded-[32px] shadow-2xl w-full max-w-lg overflow-hidden transform transition-all scale-95 opacity-0 duration-300">
        <div class="px-8 py-6 premium-modal-header flex justify-between items-center">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-blue-100 text-blue-600 rounded-2xl flex items-center justify-center shadow-inner">
                    <i class="fa-solid fa-id-card-clip text-xl"></i>
                </div>
                <div>
                    <h3 class="text-xl font-[900] text-gray-900 tracking-tight">ข้อมูลผู้ใช้งาน</h3>
                    <p class="text-[11px] text-gray-400 font-bold uppercase tracking-wider">ตรวจสอบประวัติและการลงทะเบียน</p>
                </div>
            </div>
            <button type="button" onclick="closeModal('view')" class="w-10 h-10 flex items-center justify-center rounded-2xl hover:bg-gray-100 text-gray-400 hover:text-gray-700 transition-all duration-200">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>
        
        <div class="px-8 py-7 space-y-5 bg-white/50">
            <div class="grid grid-cols-1 gap-4">
                <div class="flex flex-col gap-1 p-4 bg-gray-50/50 rounded-2xl border border-gray-100">
                    <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">ชื่อ-นามสกุล</span>
                    <span id="view_full_name" class="font-black text-gray-900 text-lg tracking-tight"></span>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col gap-1 p-4 bg-gray-50/50 rounded-2xl border border-gray-100">
                        <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">เลขบัตรประชาชน</span>
                        <span id="view_citizen_id" class="font-bold text-gray-700 tracking-wider text-sm"></span>
                    </div>
                    <div class="flex flex-col gap-1 p-4 bg-gray-50/50 rounded-2xl border border-gray-100">
                        <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">รหัสประจำตัว</span>
                        <span id="view_student_id" class="font-bold text-gray-700 text-sm"></span>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col gap-1 p-4 bg-gray-50/50 rounded-2xl border border-gray-100">
                        <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">เบอร์โทรศัพท์</span>
                        <span id="view_phone" class="font-bold text-gray-700 text-sm"></span>
                    </div>
                    <div class="flex flex-col gap-1 p-4 bg-gray-50/50 rounded-2xl border border-gray-100">
                        <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">ประเภท</span>
                        <span id="view_status" class="font-bold text-blue-600 text-sm"></span>
                    </div>
                </div>

                <div class="flex flex-col gap-1 p-4 bg-blue-50/30 rounded-2xl border border-blue-100/50">
                    <span class="text-[10px] font-black text-blue-400 uppercase tracking-widest">วันที่ลงทะเบียนเข้าระบบ</span>
                    <span id="view_created_at" class="font-bold text-gray-600 text-sm"></span>
                </div>
            </div>
        </div>

        <div class="px-8 py-6 border-t border-gray-100 bg-gray-50/80 flex justify-end">
            <button type="button" onclick="closeModal('view')" 
                    class="px-10 py-3.5 rounded-2xl font-black text-gray-600 bg-white border border-gray-200 hover:bg-gray-100 transition-all duration-200 active:scale-95 text-sm uppercase tracking-widest shadow-sm">
                รับทราบ
            </button>
        </div>
    </div>
</div>

<script>
// Premium Modal Controls
function closeModal(type) {
    const modal    = document.getElementById(type + 'Modal');
    const modalBox = document.getElementById(type + 'ModalBox');
    
    if (modalBox) {
        modalBox.classList.add('scale-95', 'opacity-0');
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 300);
    } else {
        modal.classList.add('hidden');
    }
}

function openEditModal(user) {
    document.getElementById('edit_user_id').value = user.id;
    document.getElementById('edit_full_name').value = user.full_name;
    document.getElementById('edit_student_id').value = user.student_personnel_id;
    document.getElementById('edit_citizen_id').value = user.citizen_id;
    document.getElementById('edit_phone').value = user.phone_number;
    document.getElementById('edit_status').value = user.status;
    
    const modal    = document.getElementById('editModal');
    const modalBox = document.getElementById('editModalBox');
    
    modal.classList.remove('hidden');
    setTimeout(() => {
        modalBox.classList.remove('scale-95', 'opacity-0');
    }, 10);
}

function openViewModal(user) {
    document.getElementById('view_full_name').innerText = user.full_name || '-';
    document.getElementById('view_citizen_id').innerText = user.citizen_id || '-';
    document.getElementById('view_student_id').innerText = user.student_personnel_id || '-';
    document.getElementById('view_phone').innerText = user.phone_number || '-';
    document.getElementById('view_status').innerText = user.status ? (user.status === 'student' ? 'นักศึกษา' : (user.status === 'staff' ? 'บุคลากร/อาจารย์' : 'บุคคลทั่วไป')) : '-';
    document.getElementById('view_created_at').innerText = new Date(user.created_at).toLocaleString('th-TH', {
        year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit'
    });
    
    const modal    = document.getElementById('viewModal');
    const modalBox = document.getElementById('viewModalBox');
    
    modal.classList.remove('hidden');
    setTimeout(() => {
        modalBox.classList.remove('scale-95', 'opacity-0');
    }, 10);
}

function filterUsers(val) {
    const rows = document.querySelectorAll('#userTable tbody tr');
    val = val.toLowerCase();
    rows.forEach(row => {
        const text = row.innerText.toLowerCase();
        row.style.display = text.includes(val) ? '' : 'none';
    });
}

// Close on backdrop click
document.getElementById('editModal').addEventListener('click', e => { if (e.target.id === 'editModal') closeModal('edit'); });
document.getElementById('viewModal').addEventListener('click', e => { if (e.target.id === 'viewModal') closeModal('view'); });
</script>

<?php require_once __DIR__ . '/../admin/includes/footer.php'; ?>
