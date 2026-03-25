<?php
// manage_equipment.php (กู้ชีพ: แก้ไข SQL ให้คำนวณจำนวนอุปกรณ์อัตโนมัติ)
include('../includes/check_session.php'); 
require_once(__DIR__ . '/../../../config/db_connect.php');

$pdo = db();

// 1. Guard สิทธิ์การเข้าถึง
$allowed_roles = ['admin', 'editor'];
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], $allowed_roles)) {
    header("Location: index.php");
    exit;
}

// 2. จัดการข้อความแจ้งเตือน
$message = $_GET['message'] ?? '';
$message_type = $_GET['type'] ?? 'success';

// 3. ตั้งค่าหน้าเพจ
$page_title = "จัดการประเภทอุปกรณ์"; 
$current_page = "manage_equip";

include('../includes/header.php');

// 4. ดึงข้อมูลประเภทอุปกรณ์ พร้อมนับจำนวน (Sub-queries)
try {
    $search_query = $_GET['search'] ?? '';
    
    // SQL อัจฉริยะ: นับจำนวนอุปกรณ์ทั้งหมด และอุปกรณ์ที่ว่าง (Status = 'available')
    $sql = "SELECT 
                bc.*,
                (SELECT COUNT(*) FROM borrow_items bi WHERE bi.type_id = bc.id) as total_quantity,
                (SELECT COUNT(*) FROM borrow_items bi WHERE bi.type_id = bc.id AND bi.status = 'available') as available_quantity
            FROM borrow_categories bc";

    $params = [];
    if (!empty($search_query)) {
        $sql .= " WHERE bc.name LIKE ? OR bc.description LIKE ?";
        $params[] = "%$search_query%";
        $params[] = "%$search_query%";
    }

    $sql .= " ORDER BY bc.name ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $equipment_types = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("SQL Error in manage_equipment: " . $e->getMessage());
    $equipment_types = [];
    $error_msg = "ไม่สามารถเชื่อมต่อฐานข้อมูลได้ในขณะนี้";
}
?>

<div class="admin-wrap" style="padding: 20px;">
    <?php if (isset($error_msg)): ?>
        <div class="alert alert-danger"><?php echo $error_msg; ?></div>
    <?php endif; ?>

    <div class="header-row" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
        <h2><i class="fas fa-tools"></i> จัดการประเภทอุปกรณ์</h2>
        <div style="display: flex; gap: 10px;">
            <button class="btn btn-primary" onclick="openAddTypePopup()">
                <i class="fas fa-plus"></i> เพิ่มประเภทอุปกรณ์
            </button>
        </div>
    </div>

    <!-- ส่วนเนื้อหาหลัก (ตาราง) -->
    <div class="table-container" style="background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
        <table class="table table-hover" style="width: 100%; margin: 0;">
            <thead style="background: #f8f9fa;">
                <tr>
                    <th style="padding: 15px;">ชื่อประเภทอุปกรณ์</th>
                    <th style="padding: 15px;">รายละเอียด</th>
                    <th style="padding: 15px; text-align: center;">จำนวน (ว่าง / ทั้งหมด)</th>
                    <th style="padding: 15px; text-align: center;">จัดการ</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($equipment_types)): ?>
                    <tr>
                        <td colspan="4" style="text-align: center; padding: 40px; color: #999;">ไม่พบข้อมูลประเภทอุปกรณ์</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($equipment_types as $type): ?>
                        <tr>
                            <td style="padding: 15px;"><strong><?php echo htmlspecialchars($type['name']); ?></strong></td>
                            <td style="padding: 15px; color: #666; font-size: 0.9em;"><?php echo htmlspecialchars($type['description'] ?? '-'); ?></td>
                            <td style="padding: 15px; text-align: center;">
                                <span class="badge" style="background: #e1f5fe; color: #01579b; font-size: 14px; padding: 6px 12px; border-radius: 20px;">
                                    <?php echo $type['available_quantity']; ?> / <?php echo $type['total_quantity']; ?>
                                </span>
                            </td>
                            <td style="padding: 15px; text-align: center;">
                                <div style="display: flex; justify-content: center; gap: 8px;">
                                    <a href="admin/manage_items.php?type_id=<?php echo $type['id']; ?>" class="btn btn-sm btn-outline-primary" style="padding: 5px 10px;">
                                        <i class="fas fa-list-ol"></i> อุปกรณ์รายชิ้น
                                    </a>
                                    <button class="btn btn-sm btn-outline-secondary" onclick="openEditTypePopup(<?php echo $type['id']; ?>)">
                                        <i class="fas fa-edit"></i> แก้ไข
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Popup สำหรับเพิ่มประเภทอุปกรณ์
    function openAddTypePopup() {
        Swal.fire({
            title: 'เพิ่มประเภทอุปกรณ์ใหม่',
            html: `
                <input id="swal-name" class="swal2-input" placeholder="ชื่อประเภท">
                <textarea id="swal-desc" class="swal2-textarea" placeholder="รายละเอียด"></textarea>
            `,
            showCancelButton: true,
            confirmButtonText: 'บันทึก',
            preConfirm: () => {
                const name = document.getElementById('swal-name').value;
                if (!name) return Swal.showValidationMessage('โปรดระบุชื่อประเภท');
                return { name: name, desc: document.getElementById('swal-desc').value };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                // ส่ง AJAX ไปบันทึก (ตัวอย่าง)
                Swal.fire('กำลังสร้าง...', '', 'info');
                // ... logic การส่งบันทึก ...
            }
        });
    }
</script>

<?php include('../includes/footer.php'); ?>