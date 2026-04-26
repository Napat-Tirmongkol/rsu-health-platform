<?php
/**
 * config/sentry.php — Sentry error monitoring initialization
 *
 * โหลดก่อน error_logger.php ใน config.php ไม่ได้ — ต้องโหลด หลัง
 * เพื่อให้ Sentry SDK เป็น "outer wrapper" ที่ wraps handler ของเราใน chain:
 *   error/exception → Sentry (capture) → error_logger (write to DB)
 *
 * ตั้งค่า DSN ใน config/secrets.php:
 *   'SENTRY_DSN' => 'https://KEY@SENTRY_HOST/PROJECT_ID'
 * หรือผ่าน Environment Variable: SENTRY_DSN
 */
declare(strict_types=1);

// ── 1. Load Composer autoloader ───────────────────────────────────────────────
$_autoload = __DIR__ . '/../vendor/autoload.php';
if (!file_exists($_autoload)) {
    defined('SENTRY_BROWSER_KEY') || define('SENTRY_BROWSER_KEY', '');
    return;
}
try {
    require_once $_autoload; // platform_check.php อยู่ใน autoload — throw ถ้า PHP เวอร์ชันต่ำ
} catch (\RuntimeException $e) {
    // PHP version ไม่ถึง 8.1 — ปิด Sentry อย่าง graceful ไม่ให้ทั้งระบบพัง
    defined('SENTRY_BROWSER_KEY') || define('SENTRY_BROWSER_KEY', '');
    return;
}
unset($_autoload);

// ── 2. Read DSN ───────────────────────────────────────────────────────────────
$_dsn = '';
$_secretsFile = __DIR__ . '/secrets.php';
if (file_exists($_secretsFile)) {
    $_s = require $_secretsFile;
    if (is_array($_s)) {
        $_dsn = (string)($_s['SENTRY_DSN'] ?? '');
    }
    unset($_s);
}
if ($_dsn === '') {
    $_dsn = (string)(getenv('SENTRY_DSN') ?: '');
}
unset($_secretsFile);

// ── 3. Define constant SENTRY_BROWSER_KEY (public key for browser SDK) ────────
// DSN format: https://PUBLIC_KEY@SENTRY_HOST/PROJECT_ID
$_parsed = $_dsn !== '' ? parse_url($_dsn) : [];
defined('SENTRY_BROWSER_KEY') || define('SENTRY_BROWSER_KEY', (string)($_parsed['user'] ?? ''));
unset($_parsed);

if ($_dsn === '') {
    unset($_dsn);
    return; // DSN ไม่ได้ตั้งค่า — ปิด Sentry ไว้ก่อน
}

// ── 4. Initialize Sentry PHP SDK ─────────────────────────────────────────────
\Sentry\init([
    'dsn'                => $_dsn,
    'environment'        => (string)(getenv('APP_ENV') ?: 'production'),
    'release'            => defined('APP_VERSION') ? APP_VERSION : null,
    'traces_sample_rate' => 0.1,   // 10% ของ request สำหรับ Performance Monitoring
    'send_default_pii'   => false, // ไม่ส่ง IP / cookie อัตโนมัติ — ตาม PDPA
    'max_breadcrumbs'    => 50,

    // เพิ่ม context ก่อนส่งทุก event
    'before_send' => function (
        \Sentry\Event $event,
        ?\Sentry\EventHint $hint
    ): ?\Sentry\Event {
        // User ID จาก Session (ไม่ส่ง ชื่อ / เบอร์ — แค่ตัวเลข ID)
        if (session_status() === PHP_SESSION_ACTIVE) {
            $uid = $_SESSION['admin_id']
                ?? $_SESSION['student_id']
                ?? $_SESSION['student_id']
                ?? null;
            if ($uid !== null) {
                $event->setUser(
                    \Sentry\UserDataBag::createFromArray(['id' => (string)$uid])
                );
            }
        }
        // Tag ชื่อไฟล์ปัจจุบัน (ช่วย filter ใน Sentry dashboard)
        $event->setTag('page', basename($_SERVER['PHP_SELF'] ?? 'cli'));
        return $event;
    },
]);

unset($_dsn);
