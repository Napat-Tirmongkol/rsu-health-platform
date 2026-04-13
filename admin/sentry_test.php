<?php
// admin/sentry_test.php — ทดสอบการเชื่อมต่อ Sentry (Admin only)
declare(strict_types=1);
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/includes/auth.php';

$results = [];

// ─── 1. ตรวจสอบ DSN ──────────────────────────────────────────────────────────
$hasDsn = defined('SENTRY_BROWSER_KEY') && SENTRY_BROWSER_KEY !== '';
$results[] = [
    'label'  => 'DSN ถูกตั้งค่าใน secrets.php',
    'pass'   => $hasDsn,
    'detail' => $hasDsn ? 'Public key: ' . SENTRY_BROWSER_KEY : 'ไม่พบ SENTRY_DSN ใน config/secrets.php',
];

// ─── 2. ตรวจสอบ Composer / vendor ────────────────────────────────────────────
$vendorExists = file_exists(__DIR__ . '/../vendor/autoload.php');
$results[] = [
    'label'  => 'Composer vendor/ ถูกติดตั้ง',
    'pass'   => $vendorExists,
    'detail' => $vendorExists ? 'vendor/autoload.php พบแล้ว' : 'ยังไม่ได้รัน composer install',
];

// ─── 3. ตรวจสอบ Sentry SDK โหลดได้ ──────────────────────────────────────────
$sdkLoaded = class_exists('\Sentry\SentrySdk');
$results[] = [
    'label'  => 'Sentry PHP SDK โหลดสำเร็จ',
    'pass'   => $sdkLoaded,
    'detail' => $sdkLoaded ? 'sentry/sentry ' . \Jean85\PrettyVersions::getVersion('sentry/sentry')->getPrettyVersion() : 'ไม่พบ class \\Sentry\\SentrySdk',
];

// ─── 4. ตรวจสอบว่า Hub มี DSN อยู่ (Sentry initialized จริง) ─────────────────
$hubActive = false;
$hubDetail = 'Sentry ไม่ได้ถูก initialize (ไม่มี DSN)';
if ($sdkLoaded) {
    try {
        $client = \Sentry\SentrySdk::getCurrentHub()->getClient();
        $hubActive = $client !== null && $client->getOptions()->getDsn() !== null;
        $hubDetail = $hubActive
            ? 'Hub active — DSN: ' . preg_replace('/\/\/([^@]+)@/', '//***@', (string)$client->getOptions()->getDsn())
            : 'Client หรือ DSN เป็น null';
    } catch (Throwable $e) {
        $hubDetail = 'Error: ' . $e->getMessage();
    }
}
$results[] = [
    'label'  => 'Sentry Hub active (DSN ถูก init)',
    'pass'   => $hubActive,
    'detail' => $hubDetail,
];

// ─── 5. ส่ง Test Event (captureMessage) ─────────────────────────────────────
$eventId     = null;
$sendDetail  = 'Sentry ไม่ได้ถูก initialize — ข้ามการส่ง';
if ($hubActive && isset($_POST['send_test'])) {
    try {
        $eventId = \Sentry\captureMessage(
            '[TEST] RSU Healthcare Sentry integration test — ' . date('Y-m-d H:i:s'),
            \Sentry\Severity::info()
        );
        $sendDetail = $eventId
            ? 'Event ID: ' . (string)$eventId
            : 'captureMessage() คืนค่า null (อาจถูก before_send filter ออก)';
    } catch (Throwable $e) {
        $sendDetail = 'Error: ' . $e->getMessage();
    }
} elseif ($hubActive) {
    $sendDetail = 'กดปุ่ม "ส่ง Test Event" เพื่อทดสอบ';
}
$results[] = [
    'label'  => 'ส่ง Test Message ไปยัง Sentry',
    'pass'   => $eventId !== null,
    'detail' => $sendDetail,
    'action' => true,
];

// ─── 6. ส่ง Test Exception ────────────────────────────────────────────────────
$exEventId   = null;
$exDetail    = $hubActive ? 'กดปุ่ม "ส่ง Test Event" เพื่อทดสอบ' : 'Sentry ไม่ได้ถูก initialize — ข้าม';
if ($hubActive && isset($_POST['send_test'])) {
    try {
        $exEventId = \Sentry\captureException(
            new RuntimeException('[TEST] RSU Healthcare Sentry exception test — ' . date('Y-m-d H:i:s'))
        );
        $exDetail = $exEventId
            ? 'Event ID: ' . (string)$exEventId
            : 'captureException() คืนค่า null';
    } catch (Throwable $e) {
        $exDetail = 'Error: ' . $e->getMessage();
    }
}
$results[] = [
    'label'  => 'ส่ง Test Exception ไปยัง Sentry',
    'pass'   => $exEventId !== null,
    'detail' => $exDetail,
    'action' => true,
];

$overallPass = $hasDsn && $vendorExists && $sdkLoaded && $hubActive;
require_once __DIR__ . '/includes/header.php';
?>
<style>
.test-row-pass  { background:#f0fdf4; border-color:#bbf7d0; }
.test-row-fail  { background:#fef2f2; border-color:#fecaca; }
.test-row-skip  { background:#f9fafb; border-color:#e5e7eb; }
</style>

<?php
$statusIcon = $overallPass ? 'fa-circle-check' : 'fa-triangle-exclamation';
$statusColor = $overallPass ? '#2e9e63' : '#f59e0b';
$statusText = $overallPass ? 'Sentry เชื่อมต่อสำเร็จ' : 'ต้องการการตั้งค่าเพิ่มเติม';
renderPageHeader(
    '<i class="fa-brands fa-sentry mr-2" style="color:#362d59"></i>Sentry Connection Test',
    'ตรวจสอบการเชื่อมต่อ Sentry Error Monitoring'
);
?>

<!-- Status Banner -->
<div class="mb-6 flex items-center gap-4 p-4 rounded-2xl border-2 animate-slide-up"
     style="background:<?= $overallPass ? '#f0fdf4' : '#fffbeb' ?>;border-color:<?= $overallPass ? '#86efac' : '#fcd34d' ?>">
    <div class="w-12 h-12 rounded-2xl flex items-center justify-center flex-shrink-0"
         style="background:<?= $overallPass ? '#dcfce7' : '#fef3c7' ?>">
        <i class="fa-solid <?= $statusIcon ?> text-xl" style="color:<?= $statusColor ?>"></i>
    </div>
    <div>
        <div class="font-black text-gray-900 text-lg"><?= $statusText ?></div>
        <div class="text-sm text-gray-500 mt-0.5">
            <?= $overallPass
                ? 'ระบบพร้อมรับ error reports จาก PHP และ Browser แล้ว'
                : 'กรุณาตรวจสอบรายการที่แสดง <span class="text-red-500 font-bold">ล้มเหลว</span> ด้านล่าง' ?>
        </div>
    </div>
</div>

<!-- Checklist -->
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm mb-6 overflow-hidden animate-slide-up">
    <div class="px-5 py-4 border-b border-gray-50">
        <h3 class="font-bold text-gray-900">ผลการตรวจสอบ</h3>
    </div>
    <div class="divide-y divide-gray-50">
        <?php foreach ($results as $r):
            $isAction = $r['action'] ?? false;
            $rowClass = $isAction && !isset($_POST['send_test']) ? 'test-row-skip' : ($r['pass'] ? 'test-row-pass' : 'test-row-fail');
            $icon = $isAction && !isset($_POST['send_test']) ? 'fa-minus text-gray-400' : ($r['pass'] ? 'fa-circle-check text-green-500' : 'fa-circle-xmark text-red-500');
        ?>
        <div class="flex items-start gap-4 p-4 border <?= $rowClass ?>">
            <i class="fa-solid <?= $icon ?> text-base mt-0.5 flex-shrink-0"></i>
            <div class="min-w-0 flex-1">
                <div class="font-semibold text-gray-800 text-sm"><?= htmlspecialchars($r['label']) ?></div>
                <div class="text-xs text-gray-500 mt-0.5 font-mono break-all"><?= htmlspecialchars($r['detail']) ?></div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Actions -->
<div class="grid grid-cols-1 sm:grid-cols-2 gap-4 animate-slide-up">

    <!-- Send Test Event Button -->
    <form method="post">
        <input type="hidden" name="send_test" value="1">
        <?php csrf_field(); ?>
        <button type="submit"
            class="w-full flex items-center justify-center gap-2 py-3 px-5 rounded-xl font-bold text-sm transition-all
                   <?= $hubActive ? 'bg-[#362d59] hover:bg-[#2d2547] text-white shadow hover:shadow-md' : 'bg-gray-100 text-gray-400 cursor-not-allowed' ?>"
            <?= $hubActive ? '' : 'disabled' ?>>
            <i class="fa-solid fa-paper-plane"></i>
            ส่ง Test Events ไปยัง Sentry
        </button>
        <?php if (!$hubActive): ?>
        <p class="text-xs text-gray-400 text-center mt-2">ต้องตั้งค่า SENTRY_DSN ก่อน</p>
        <?php endif; ?>
    </form>

    <!-- Link to Sentry Dashboard -->
    <a href="https://sentry.io" target="_blank" rel="noopener"
       class="flex items-center justify-center gap-2 py-3 px-5 rounded-xl font-bold text-sm border border-gray-200 bg-white text-gray-700 hover:bg-gray-50 transition-all">
        <i class="fa-solid fa-arrow-up-right-from-square"></i>
        เปิด Sentry Dashboard
    </a>
</div>

<?php if (isset($_POST['send_test']) && $hubActive): ?>
<!-- Result after sending -->
<div class="mt-4 p-4 rounded-2xl border animate-slide-up
     <?= ($eventId || $exEventId) ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200' ?>">
    <div class="flex items-center gap-2 font-bold text-sm mb-2
         <?= ($eventId || $exEventId) ? 'text-green-800' : 'text-red-700' ?>">
        <i class="fa-solid <?= ($eventId || $exEventId) ? 'fa-circle-check' : 'fa-circle-xmark' ?>"></i>
        <?= ($eventId || $exEventId) ? 'Events ถูกส่งสำเร็จ!' : 'ส่ง Events ไม่สำเร็จ' ?>
    </div>
    <?php if ($eventId || $exEventId): ?>
    <p class="text-xs text-green-700">ไปที่ <strong>sentry.io → Issues</strong> เพื่อดู events ที่มีข้อความ <code class="bg-green-100 px-1 rounded">[TEST] RSU Healthcare</code></p>
    <p class="text-xs text-green-600 mt-1">อาจใช้เวลา 1–2 วินาทีก่อนปรากฏใน dashboard</p>
    <?php endif; ?>
</div>
<?php endif; ?>

<!-- Setup Guide (shown when not connected) -->
<?php if (!$overallPass): ?>
<div class="mt-6 bg-white rounded-2xl border border-gray-100 shadow-sm p-5 animate-slide-up">
    <h4 class="font-bold text-gray-900 mb-3 flex items-center gap-2">
        <i class="fa-solid fa-book text-[#362d59]"></i> วิธีตั้งค่า
    </h4>
    <ol class="space-y-3 text-sm text-gray-700">
        <li class="flex gap-3">
            <span class="w-6 h-6 bg-[#362d59] text-white rounded-full flex items-center justify-center text-xs font-black flex-shrink-0">1</span>
            <span>สมัคร / Login ที่ <strong>sentry.io</strong> แล้วสร้าง Project ใหม่ (เลือก Platform: <strong>PHP</strong>)</span>
        </li>
        <li class="flex gap-3">
            <span class="w-6 h-6 bg-[#362d59] text-white rounded-full flex items-center justify-center text-xs font-black flex-shrink-0">2</span>
            <span>ไปที่ Project → <strong>Settings → Client Keys (DSN)</strong> → คัดลอก DSN</span>
        </li>
        <li class="flex gap-3">
            <span class="w-6 h-6 bg-[#362d59] text-white rounded-full flex items-center justify-center text-xs font-black flex-shrink-0">3</span>
            <span>เปิด <code class="bg-gray-100 px-1.5 py-0.5 rounded">config/secrets.php</code> บน production แล้วใส่:</span>
        </li>
    </ol>
    <pre class="mt-3 bg-gray-900 text-green-400 text-xs p-4 rounded-xl overflow-x-auto"><code>'SENTRY_DSN' => 'https://YOUR_KEY@o0.ingest.sentry.io/YOUR_PROJECT_ID',</code></pre>
    <p class="text-xs text-gray-400 mt-3">หลังจากเพิ่ม DSN แล้ว reload หน้านี้แล้วกด <strong>ส่ง Test Events</strong></p>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
