<?php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/../config/db_connect.php';
require_once __DIR__ . '/../includes/header.php'; 

// Redirect back if there is no pending LINE ID from callback
if (!isset($_SESSION['pending_line_id'])) {
    header('Location: ../index.php');
    exit;
}

$error_msg = '';
$success_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_personnel_id = trim($_POST['student_personnel_id'] ?? '');
    $phone_number = trim($_POST['phone_number'] ?? '');

    if (empty($student_personnel_id) || empty($phone_number)) {
        $error_msg = "กรุณากรอกข้อมูลให้ครบถ้วน";
    } else {
        try {
            $pdo = db();
            $stmt = $pdo->prepare("SELECT id, full_name FROM med_students WHERE student_personnel_id = :st_id AND phone_number = :phone LIMIT 1");
            $stmt->execute([
                ':st_id' => $student_personnel_id,
                ':phone' => $phone_number
            ]);
            $user = $stmt->fetch();

            if ($user) {
                // Update new LINE ID mapping
                $updateStmt = $pdo->prepare("UPDATE med_students SET line_user_id = :line_id WHERE id = :id");
                $updateStmt->execute([
                    ':line_id' => $_SESSION['pending_line_id'],
                    ':id' => $user['id']
                ]);

                $pendingRedirect = $_SESSION['pending_redirect'] ?? 'ecampaign';

                // ตั้ง Session ที่ใช้ร่วมทั้ง 2 ระบบ
                $_SESSION['line_user_id']      = $_SESSION['pending_line_id'];
                // e-campaign sessions
                $_SESSION['evax_student_id']   = (int)$user['id'];
                $_SESSION['evax_full_name']    = $user['full_name'];
                // e_Borrow sessions
                $_SESSION['student_id']        = (int)$user['id'];
                $_SESSION['student_full_name'] = $user['full_name'];
                $_SESSION['student_line_id']   = $_SESSION['pending_line_id'];

                unset($_SESSION['pending_line_id'], $_SESSION['pending_redirect']);

                // เก็บไว้ใน success_msg และ redirect target
                $success_msg    = "เชื่อมต่อบัญชีสำเร็จ!";
                $successRedirect = ($pendingRedirect === 'eborrow') ? '../e_Borrow/index.php' : '../index.php';
            } else {
                $error_msg = "ข้อมูลไม่ถูกต้อง หรือไม่พบข้อมูลในระบบ";
            }
        } catch (PDOException $e) {
            $error_msg = "เกิดข้อผิดพลาดในการรันคำสั่ง Database: " . $e->getMessage();
        }
    }
}

render_header('เชื่อมต่อบัญชีผู้ใช้ (Link Account)');
?>

<div class="flex flex-col items-center justify-center min-h-screen bg-[#f4f7fa] p-4 text-gray-800">
    <div class="w-full max-w-md bg-white rounded-[24px] shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-gray-100 overflow-hidden font-prompt animate-in fade-in slide-in-from-bottom-4 duration-500">
        
        <!-- Header Banner Section -->
        <div class="bg-[#0052CC] p-8 text-center relative overflow-hidden">
            <!-- Glassmorphism Orbs -->
            <div class="absolute -right-6 -top-6 w-32 h-32 bg-white opacity-[0.08] rounded-full blur-2xl"></div>
            <div class="absolute -left-6 -bottom-6 w-32 h-32 bg-cyan-200 opacity-[0.08] rounded-full blur-2xl"></div>
            
            <div class="mx-auto w-16 h-16 bg-white rounded-2xl flex items-center justify-center shadow-lg mb-4 relative z-10 rotate-3 transition-transform hover:rotate-0">
                <i class="fa-brands fa-line text-4xl text-[#00c300]"></i>
            </div>
            <h2 class="text-[22px] font-bold text-white mb-2 relative z-10 tracking-wide">เชื่อมต่อบัญชี LINE ใหม่</h2>
            <p class="text-blue-100 text-[13.5px] relative z-10 leading-relaxed font-light px-2">
                ระบบมีการอัปเดต <br/>กรุณายืนยันตัวตนด้วยรหัสนักศึกษาและเบอร์โทรศัพท์เพื่อเชื่อมต่อบัญชีเดิมของคุณ
            </p>
        </div>
        
        <!-- Form Section -->
        <div class="p-8 pt-7">
            <form method="post" action="link_account.php" class="space-y-5">
                <!-- Field: Student ID -->
                <div>
                    <label for="student_personnel_id" class="block text-sm font-semibold text-gray-700 mb-2">รหัสนักศึกษา/บุคลากร</label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 group-focus-within:text-[#0052CC] transition-colors gap-2">
                            <i class="fa-solid fa-id-card text-sm"></i>
                            <div class="h-4 w-px bg-gray-200 ml-1"></div>
                        </div>
                        <input type="text" id="student_personnel_id" name="student_personnel_id" required 
                            class="w-full pl-12 pr-4 py-3.5 bg-gray-50/50 hover:bg-white rounded-xl border border-gray-200 focus:bg-white focus:ring-2 focus:ring-[#0052CC]/20 focus:border-[#0052CC] transition-all outline-none font-medium text-gray-900 placeholder:text-gray-400 placeholder:font-normal text-[15px]"
                            placeholder="กรอกรหัสนักศึกษา / รหัสบุคลากร">
                    </div>
                </div>
                
                <!-- Field: Phone Number -->
                <div>
                    <label for="phone_number" class="block text-sm font-semibold text-gray-700 mb-2">เบอร์โทรศัพท์</label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 group-focus-within:text-[#0052CC] transition-colors gap-2">
                            <i class="fa-solid fa-phone text-sm"></i>
                            <div class="h-4 w-px bg-gray-200 ml-1"></div>
                        </div>
                        <input type="tel" id="phone_number" name="phone_number" required maxlength="10"
                            class="w-full pl-12 pr-4 py-3.5 bg-gray-50/50 hover:bg-white rounded-xl border border-gray-200 focus:bg-white focus:ring-2 focus:ring-[#0052CC]/20 focus:border-[#0052CC] transition-all outline-none font-medium text-gray-900 placeholder:text-gray-400 placeholder:font-normal text-[15px]"
                            placeholder="กรอกเบอร์โทรศัพท์ (10 หลัก)">
                    </div>
                </div>
                
                <!-- Submit Button -->
                <button type="submit" class="w-full bg-[#0052CC] hover:bg-[#0047b3] text-white font-semibold py-3.5 px-4 rounded-xl transition-all shadow-md shadow-[#0052CC]/20 active:scale-[0.98] mt-8 text-[15px] flex justify-center items-center gap-2 group">
                    <span>ยืนยันข้อมูลและเชื่อมต่อ</span>
                    <i class="fa-solid fa-arrow-right-long opacity-80 group-hover:translate-x-1 transition-transform"></i>
                </button>
            </form>
            
            <div class="mt-7 text-center">
                <a href="../index.php" class="text-[13px] font-medium text-gray-400 hover:text-gray-700 transition-colors inline-block border-b border-transparent hover:border-gray-300 pb-0.5">
                    ยกเลิกและกลับหน้าหลัก
                </a>
            </div>
        </div>
    </div>
</div>


<!-- SweetAlert2 — ต้องอยู่ก่อน render_footer() เพราะ footer แค่ปิด </body></html> -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<?php if ($error_msg): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    Swal.fire({
        icon: 'error',
        title: 'ข้อมูลไม่ถูกต้อง',
        text: '<?= htmlspecialchars($error_msg, ENT_QUOTES, 'UTF-8') ?>',
        confirmButtonColor: '#0052CC',
        confirmButtonText: 'ลองอีกครั้ง',
        customClass: {
            title: 'font-prompt font-bold text-gray-800',
            htmlContainer: 'font-prompt text-gray-600',
            confirmButton: 'font-prompt px-6 rounded-xl font-medium shadow-md transition-transform active:scale-95'
        }
    });
});
</script>
<?php endif; ?>

<?php if ($success_msg): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    Swal.fire({
        icon: 'success',
        title: 'ลิงก์บัญชีสำเร็จ!',
        text: '<?= htmlspecialchars($success_msg, ENT_QUOTES, 'UTF-8') ?>',
        confirmButtonColor: '#00c300',
        confirmButtonText: 'ดำเนินการต่อ',
        allowOutsideClick: false,
        customClass: {
            title: 'font-prompt font-bold text-gray-800',
            htmlContainer: 'font-prompt text-gray-600',
            confirmButton: 'font-prompt px-6 rounded-xl font-medium shadow-md transition-transform active:scale-95'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.replace('<?= htmlspecialchars($successRedirect ?? '../index.php', ENT_QUOTES) ?>');
        }
    });
});
</script>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; render_footer(); ?>
