<?php
// config.php — จุดเข้าหลักข���งระบบทุกไฟล์ (canonical entry point)
// โหลด: DB connection, CSRF, Error Logger และ helper functions
require_once __DIR__ . '/config/db_connect.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/error_logger.php';
require_once __DIR__ . '/config/sentry.php'; // โหลดหลัง error_logger — Sentry wraps handler chain

// ── Log Retention Settings ────────────────────────────────────────────────────
defined('ERROR_LOG_RETENTION_DAYS')    || define('ERROR_LOG_RETENTION_DAYS',    30);  // วัน
defined('ACTIVITY_LOG_RETENTION_DAYS') || define('ACTIVITY_LOG_RETENTION_DAYS', 90);  // วัน

// ── Site Settings ─────────────────────────────────────────────────────────────
$__siteSettingsFile = __DIR__ . '/config/site_settings.json';
$__siteSettings = file_exists($__siteSettingsFile) ? json_decode(file_get_contents($__siteSettingsFile), true) : [];
defined('SITE_NAME') || define('SITE_NAME', $__siteSettings['site_name'] ?? 'e-Campaign V2');
defined('SITE_LOGO') || define('SITE_LOGO', $__siteSettings['site_logo'] ?? ''); // Empty means use default icon


/**
 * ฟังก์ชันกลางสำหรับบันทึกกิจกรรมในระบบ (Activity Logging)
 */
if (!function_exists('log_activity')) {
    function log_activity(string $action, string $description = '', ?int $user_id = null): bool {
        static $activityTableReady = false;
        try {
            if (session_status() === PHP_SESSION_NONE) session_start();
            
            $pdo = db();

            // Auto-create table if not exists (runs once per request)
            if (!$activityTableReady) {
                $pdo->exec("CREATE TABLE IF NOT EXISTS sys_activity_logs (
                    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    user_id     INT UNSIGNED NULL,
                    action      VARCHAR(100) NOT NULL,
                    description TEXT,
                    ip_address  VARCHAR(45),
                    user_agent  TEXT,
                    timestamp   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_user (user_id),
                    INDEX idx_action (action),
                    INDEX idx_timestamp (timestamp)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
                $activityTableReady = true;
            }

            if ($user_id === null) {
                $user_id = $_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? null;
                if ($user_id !== null) $user_id = (int)$user_id;
            }

            $ip = $_SERVER['REMOTE_ADDR'] ?? '';
            $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';

            $stmt = $pdo->prepare("
                INSERT INTO sys_activity_logs (user_id, action, description, ip_address, user_agent) 
                VALUES (:uid, :act, :desc, :ip, :ua)
            ");
            return $stmt->execute([
                ':uid'  => $user_id,
                ':act'  => mb_substr($action, 0, 100),
                ':desc' => $description,
                ':ip'   => $ip,
                ':ua'   => mb_substr($ua, 0, 1000)
            ]);
        } catch (Exception $e) {
            error_log("Logging Error: " . $e->getMessage());
            return false;
        }
    }
}

/**
 * ตรวจสอบว่าผู้ใช้งานกรอกข้อมูลส่วนตัวครบถ้วนหรือไม่
 */
if (!function_exists('check_user_profile')) {
    function check_user_profile(int $studentId): void {
        if ($studentId <= 0) {
            header('Location: index.php');
            exit;
        }

        try {
            $pdo = db();
            $stmt = $pdo->prepare("SELECT full_name, student_personnel_id, phone_number, status, email FROM sys_users WHERE id = :id LIMIT 1");
            $stmt->execute([':id' => $studentId]);
            $u = $stmt->fetch();

            if (!$u || empty($u['full_name']) || empty($u['phone_number']) || empty($u['status']) || ($u['status'] !== 'other' && empty($u['student_personnel_id']))) {
                header('Location: profile.php');
                exit;
            }
        } catch (PDOException $e) {
            // Silently fail or log
        }
    }
}
?>
