<?php
// Bridge file: ป้องกันอดีตไฟล์ที่อ้างอิงถึง config.php เดิมพัง
require_once __DIR__ . '/config/db_connect.php';
require_once __DIR__ . '/includes/csrf.php';

/**
 * 🛰️ ฟังก์ชันกลางสำหรับบันทึกกิจกรรมในระบบ (Activity Logging)
 * ย้ายมาไว้ที่ config.php เพื่อเป็นประตูที่เข้าถึงได้จากทุกทิศทาง
 */
if (!function_exists('log_activity')) {
    function log_activity(string $action, string $description = '', ?int $user_id = null): bool {
        try {
            if (session_status() === PHP_SESSION_NONE) session_start();
            
            if ($user_id === null && isset($_SESSION['admin_id'])) {
                $user_id = (int)$_SESSION['admin_id'];
            }
            if ($user_id === null) return false;

            $pdo = db();
            $stmt = $pdo->prepare("INSERT INTO sys_activity_logs (user_id, action, description) VALUES (:uid, :act, :desc)");
            return $stmt->execute([
                ':uid'  => $user_id,
                ':act'  => $action,
                ':desc' => $description
            ]);
        } catch (Exception $e) {
            error_log("Logging Error: " . $e->getMessage());
            return false;
        }
    }
}
?>
