<?php
/**
 * portal/line_settings.php — หน้าตั้งค่า LINE Messaging API (Standalone Wrapper)
 */
declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/includes/auth.php';

if (($_SESSION['admin_role'] ?? '') !== 'superadmin') {
    header('Location: index.php'); exit;
}

$secrets = require __DIR__ . '/../config/secrets.php';
$_GET['layout'] = 'none'; 
require_once __DIR__ . '/../admin/includes/header.php';

// เรียกใช้ Partial
include __DIR__ . '/_partials/line_settings.php';

require_once __DIR__ . '/../admin/includes/footer.php';
