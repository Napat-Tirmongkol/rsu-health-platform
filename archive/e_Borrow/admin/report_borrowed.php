<?php
// admin/report_borrowed.php (แก้ไข V3.4 - กู้ชีพหน้าประวัติรายงาน V2)
include('../includes/check_session.php');
require_once(__DIR__ . '/../../../config/db_connect.php');

$pdo = db();

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: index.php");
    exit;
}

$start_date = $_GET['start_date'] ?? null;
$end_date = $_GET['end_date'] ?? null;
$status_filter = $_GET['status'] ?? '';

// SQL ฉบับปรับปรุง (เอา s.status ออก เพราะ V2 ไม่มี)
$sql = "SELECT 
            t.id as transaction_id, t.borrow_date, t.due_date, t.return_date, t.status,
            bc.name as type_name, 
            bi.serial_number,
            s.full_name as student_name, s.student_personnel_id, s.phone_number as student_phone
        FROM borrow_records t
        LEFT JOIN borrow_categories bc ON t.type_id = bc.id
        LEFT JOIN borrow_items bi ON t.equipment_id = bi.id
        LEFT JOIN sys_users s ON t.borrower_student_id = s.id
        WHERE 1=1 ";

$params = [];
if (!empty($start_date)) { $sql .= " AND DATE(t.borrow_date) >= ?"; $params[] = $start_date; }
if (!empty($end_date)) { $sql .= " AND DATE(t.borrow_date) <= ?"; $params[] = $end_date; }
if (!empty($status_filter)) { $sql .= " AND t.status = ?"; $params[] = $status_filter; }

$sql .= " ORDER BY t.borrow_date DESC";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "SQL ERROR: " . $e->getMessage();
    $transactions = [];
}

// AJAX Handler
if (isset($_GET['ajax_table'])) {
    if (empty($transactions)) {
        echo '<tr><td colspan="6" style="text-align: center; padding: 20px; color: #888;">ไม่พบข้อมูลในช่วงเวลานี้</td></tr>';
    } else {
        foreach ($transactions as $item) {
            $status_badge = ($item['status'] == 'returned') 
                ? '<span class="badge bg-success"><i class="fas fa-check-circle"></i> คืนแล้ว</span>' 
                : '<span class="badge bg-warning text-dark"><i class="fas fa-clock"></i> ยืมอยู่</span>';
            
            $return_txt = $item['return_date'] ? date('d/m/Y', strtotime($item['return_date'])) : '-';

            echo '<tr>
                <td style="padding:15px;">
                    <strong>'.htmlspecialchars($item['type_name'] ?? 'N/A').'</strong><br>
                    <small class="text-muted">S/N: '.htmlspecialchars($item['serial_number'] ?? '-').'</small>
                </td>
                <td style="padding:15px;">
                    '.htmlspecialchars($item['student_name'] ?? '[Deleted User]').'<br>
                    <small>'.htmlspecialchars($item['student_personnel_id'] ?? '').'</small>
                </td>
                <td>'.date('d/m/Y', strtotime($item['borrow_date'])).'</td>
                <td>'.date('d/m/Y', strtotime($item['due_date'])).'</td>
                <td>'.$return_txt.'</td>
                <td>'.$status_badge.'</td>
            </tr>';
        }
    }
    exit;
}

$page_title = "รายงานการยืม-คืน";
$current_page = "report";
include('../includes/header.php');
?>

<div class="admin-wrap" style="padding:20px;">
    <div class="header-row" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <h2><i class="fas fa-chart-line"></i> รายงานการยืม-คืน</h2>
        <div style="display:flex; gap:10px;">
            <input type="date" id="report_start" class="form-control form-control-sm" value="<?php echo $start_date; ?>">
            <input type="date" id="report_end" class="form-control form-control-sm" value="<?php echo $end_date; ?>">
            <button onclick="refreshTable()" class="btn btn-primary btn-sm"><i class="fas fa-search"></i> กรอง</button>
            <button onclick="window.print()" class="btn btn-outline-secondary btn-sm"><i class="fas fa-print"></i> พิมพ์</button>
        </div>
    </div>

    <div class="card" style="border-radius:12px; border:none; box-shadow:0 4px 15px rgba(0,0,0,0.05);">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th style="padding:15px;">อุปกรณ์</th>
                        <th style="padding:15px;">ผู้ยืม</th>
                        <th>วันที่ยืม</th>
                        <th>กำหนดคืน</th>
                        <th>วันที่คืนจริง</th>
                        <th>สถานะ</th>
                    </tr>
                </thead>
                <tbody id="reportTableBody">
                    <!-- โหลดผ่าน AJAX หรือ Loop ด้านบน -->
                    <?php if (empty($transactions)): ?>
                        <tr><td colspan="6" style="text-align: center; padding:40px; color:#999;">ไม่พบประวัติการทำรายการ</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function refreshTable() {
    const start = document.getElementById('report_start').value;
    const end = document.getElementById('report_end').value;
    fetch(`admin/report_borrowed.php?ajax_table=1&start_date=${start}&end_date=${end}`)
        .then(r => r.text())
        .then(html => {
            document.getElementById('reportTableBody').innerHTML = html;
        });
}
document.addEventListener('DOMContentLoaded', () => refreshTable());
</script>

<?php include('../includes/footer.php'); ?>
