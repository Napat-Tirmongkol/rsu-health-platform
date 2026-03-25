<?php
// admin/manage_students.php (แก้ไข V3.4 - กู้ชีพหน้าผู้ใช้งานและพนักงาน V2)
include('../includes/check_session.php'); 
require_once(__DIR__ . '/../../../config/db_connect.php');

$pdo = db();

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: index.php");
    exit;
}

// 1. ดึงข้อมูลผู้ใช้งาน (sys_users) - ตัด status ออก
try {
    $sql_students = "SELECT s.* FROM sys_users s ORDER BY s.full_name ASC";
    $stmt_students = $pdo->prepare($sql_students);
    $stmt_students->execute();
    $students = $stmt_students->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $student_error = "เกิดข้อผิดพลาดในการดึงข้อมูลผู้ใช้งาน: " . $e->getMessage();
    $students = [];
}

// 2. ดึงข้อมูลพนักงาน (sys_staff)
try {
    $sql_staff = "SELECT u.id, u.username, u.full_name, u.role FROM sys_staff u ORDER BY u.role ASC, u.username ASC";
    $stmt_staff = $pdo->prepare($sql_staff);
    $stmt_staff->execute();
    $staff_accounts = $stmt_staff->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $staff_error = "เกิดข้อผิดพลาดในการดึงข้อมูลพนักงาน: " . $e->getMessage();
    $staff_accounts = [];
}

$page_title = "จัดการผู้ใช้งาน";
$current_page = "manage_user";
include('../includes/header.php');
?>

<div class="admin-wrap" style="padding:20px;">
    <!-- ส่วนผู้ใช้ทั่วไป -->
    <div class="header-row" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <h2><i class="fas fa-users"></i> ผู้ใช้งานทั้งหมดในระบบ (Portal)</h2>
    </div>

    <div class="card mb-4" style="border-radius:12px; border:none; box-shadow:0 4px 15px rgba(0,0,0,0.05);">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th style="padding:15px;">ชื่อ-นามสกุล</th>
                        <th>รหัสประจำตัว</th>
                        <th>เบอร์โทรศัพท์</th>
                        <th>เชื่อมต่อ</th>
                        <th>จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($students)): ?>
                        <tr><td colspan="5" style="text-align: center; padding:40px; color:#999;">ไม่พบข้อมูลผู้ใช้งาน</td></tr>
                    <?php else: ?>
                        <?php foreach ($students as $student): ?>
                            <tr>
                                <td style="padding:15px;"><strong><?php echo htmlspecialchars($student['full_name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($student['student_personnel_id'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($student['phone_number'] ?? '-'); ?></td>
                                <td>
                                    <?php echo ($student['line_user_id']) 
                                        ? '<span class="badge bg-success"><i class="fab fa-line"></i> LINE</span>' 
                                        : '<span class="badge bg-secondary">System</span>'; ?>
                                </td>
                                <td>
                                    <button onclick="openEditStudentPopup(<?php echo $student['id']; ?>)" class="btn btn-sm btn-outline-primary">แก้ไข</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ส่วนบัญชีพนักงาน -->
    <div class="header-row" style="display:flex; justify-content:space-between; align-items:center; margin:40px 0 20px 0;">
        <h2><i class="fas fa-user-shield"></i> บัญชีพนักงาน (Admin/Staff)</h2>
    </div>

    <div class="card" style="border-radius:12px; border:none; box-shadow:0 4px 15px rgba(0,0,0,0.05);">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th style="padding:15px;">Username</th>
                        <th>ชื่อ-นามสกุล</th>
                        <th>ระดับสิทธิ์</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($staff_accounts)): ?>
                        <tr><td colspan="3" style="text-align: center; padding:40px; color:#999;">ไม่พบข้อมูลบัญชีพนักงาน</td></tr>
                    <?php else: ?>
                        <?php foreach ($staff_accounts as $staff): ?>
                            <tr>
                                <td style="padding:15px;"><code><?php echo htmlspecialchars($staff['username']); ?></code></td>
                                <td><?php echo htmlspecialchars($staff['full_name']); ?></td>
                                <td>
                                    <span class="badge <?php echo ($staff['role'] == 'admin') ? 'bg-danger' : 'bg-primary'; ?>">
                                        <?php echo strtoupper($staff['role']); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function openEditStudentPopup(id) {
    Swal.fire({
        title: 'แก้ไขข้อมูลผู้ใช้งาน',
        text: 'ฟังก์ชันแก้ไขข้อมูลส่วนบุคคลกำลังปรับปรุงให้เชื่อมโยงกับโปรไฟล์พอร์ทัล',
        icon: 'info'
    });
}
</script>

<?php include('../includes/footer.php'); ?>
