<?php
/**
 * migrate_status_column.php
 * สคริปต์สำหรับอัปเดตโครงสร้างฐานข้อมูลคอลัมน์ status ให้รองรับค่าใหม่ๆ
 */
require_once __DIR__ . '/config.php';

try {
    $pdo = db();
    
    // 1. เปลี่ยนประเภทคอลัมน์เป็น VARCHAR(50) เพื่อความยืดหยุ่นในอนาคต
    // และตั้งค่า Default เป็น 'active'
    $sql = "ALTER TABLE camp_list MODIFY COLUMN status VARCHAR(50) DEFAULT 'active'";
    $pdo->exec($sql);
    
    echo "<div style='font-family:sans-serif; padding:40px; text-align:center;'>";
    echo "<h1 style='color:#16a34a;'>✅ อัปเดตฐานข้อมูลสำเร็จ!</h1>";
    echo "<p style='color:#64748b;'>คอลัมน์ status ได้ถูกขยายให้รองรับสถานะใหม่ๆ เรียบร้อยแล้ว</p>";
    echo "<hr style='border:none; border-top:1px solid #e2e8f0; margin:20px 0;'>";
    echo "<p style='font-weight:bold; color:#ef4444;'>⚠️ เพื่อความปลอดภัย กรุณาลบไฟล์ migrate_status_column.php นี้ออกจาก Server ทันที</p>";
    echo "<a href='admin/campaigns.php' style='display:inline-block; background:#0052CC; color:#fff; padding:12px 25px; border-radius:12px; text-decoration:none; font-weight:bold; margin-top:10px;'>กลับไปหน้าจัดการแคมเปญ</a>";
    echo "</div>";

} catch (Exception $e) {
    echo "<div style='font-family:sans-serif; padding:40px; text-align:center;'>";
    echo "<h1 style='color:#dc2626;'>❌ เกิดข้อผิดพลาด</h1>";
    echo "<p style='color:#64748b;'>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}
