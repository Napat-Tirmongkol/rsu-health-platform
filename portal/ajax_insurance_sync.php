<?php
// portal/ajax_insurance_sync.php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config.php';

$adminRole = $_SESSION['admin_role'] ?? '';
$isStaff   = !empty($_SESSION['is_ecampaign_staff']);
if (($isStaff && $adminRole === '') || !in_array($adminRole, ['admin', 'superadmin', 'editor'], true)) {
    echo json_encode(['status' => 'error', 'message' => 'ไม่มีสิทธิ์เข้าถึงระบบนี้']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed']);
    exit;
}

// CSRF: accept same-origin header OR session token
$proto          = (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) ? $_SERVER['HTTP_X_FORWARDED_PROTO'] : (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http'));
$expectedOrigin = $proto . '://' . ($_SERVER['HTTP_HOST'] ?? '');
$originOk       = (($_SERVER['HTTP_ORIGIN'] ?? '') === $expectedOrigin);
$sessionOk      = verify_csrf_token($_POST['csrf_token'] ?? '');
if (!$originOk && !$sessionOk) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'CSRF validation failed กรุณาโหลดหน้าใหม่']);
    exit;
}

if (empty($_POST) && !empty($_SERVER['CONTENT_LENGTH']) && (int)$_SERVER['CONTENT_LENGTH'] > 0) {
    echo json_encode(['status' => 'error', 'message' => 'ไฟล์มีขนาดใหญ่เกิน limit (' . ini_get('post_max_size') . ')']);
    exit;
}

$action = $_POST['action'] ?? '';
$pdo    = db();

// ── Table bootstrap ───────────────────────────────────────────────────────────
function ensure_insurance_table(PDO $pdo): void
{
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS insurance_members (
            member_id        VARCHAR(20)              NOT NULL,
            full_name        VARCHAR(255)             NOT NULL DEFAULT '',
            member_status    VARCHAR(50)              NOT NULL DEFAULT '',
            citizen_id       VARCHAR(13)              NOT NULL DEFAULT '',
            date_of_birth    DATE                     NULL,
            insurance_status ENUM('Active','Inactive') NOT NULL DEFAULT 'Active',
            coverage_start   DATE                     NULL,
            coverage_end     DATE                     NULL,
            policy_number    VARCHAR(100)             NOT NULL DEFAULT '',
            remarks          TEXT                     NULL,
            updated_at       DATETIME                 NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            created_at       DATETIME                 NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (member_id),
            INDEX idx_member_status (member_status),
            INDEX idx_insurance_status (insurance_status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
}

// ── Helpers ───────────────────────────────────────────────────────────────────
function decode_csv(string $raw): string
{
    if (mb_detect_encoding($raw, ['UTF-8'], true) === 'UTF-8') return $raw;
    $c = iconv('Windows-874', 'UTF-8//TRANSLIT//IGNORE', $raw);
    return $c !== false ? $c : $raw;
}

function parse_csv(string $text): array
{
    $lines = preg_split('/\r\n|\r|\n/', trim($text));
    if (count($lines) < 2) return ['error' => 'ไฟล์ CSV ต้องมีอย่างน้อย 1 แถวข้อมูล'];

    $headerLine = ltrim(array_shift($lines), "\xEF\xBB\xBF");
    $headers    = array_map(fn($h) => strtolower(trim($h)), str_getcsv($headerLine));

    if (!in_array('member_id', $headers, true)) return ['error' => 'ไม่พบคอลัมน์ member_id'];

    $rows = [];
    $seen = [];
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '') continue;
        $cols = str_getcsv($line);
        while (count($cols) < count($headers)) $cols[] = '';
        $row = [];
        foreach ($headers as $i => $h) $row[$h] = trim($cols[$i] ?? '');
        $mid = $row['member_id'] ?? '';
        if ($mid === '' || isset($seen[$mid])) continue;
        $seen[$mid] = true;
        $rows[] = $row;
    }

    return empty($rows) ? ['error' => 'ไม่พบข้อมูลในไฟล์'] : ['rows' => $rows];
}

function norm_date(?string $d): ?string
{
    if (!$d) return null;
    foreach (['d/m/Y', 'Y-m-d', 'd-m-Y', 'd/m/y'] as $fmt) {
        $dt = DateTime::createFromFormat($fmt, $d);
        if ($dt) return $dt->format('Y-m-d');
    }
    return null;
}

// ══════════════════════════════════════════════════════════════════════════════
// ACTION: upload — parse file, upsert Active rows, inactivate missing
// ══════════════════════════════════════════════════════════════════════════════
if ($action === 'upload') {
    if (!isset($_FILES['insurance_file']) || $_FILES['insurance_file']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['status' => 'error', 'message' => 'กรุณาเลือกไฟล์ก่อนอัปโหลด']);
        exit;
    }

    $raw    = file_get_contents($_FILES['insurance_file']['tmp_name']);
    $parsed = parse_csv(decode_csv($raw));
    if (isset($parsed['error'])) {
        echo json_encode(['status' => 'error', 'message' => $parsed['error']]);
        exit;
    }

    ensure_insurance_table($pdo);

    $rows      = $parsed['rows'];
    $csvIdSet  = array_flip(array_column($rows, 'member_id'));
    $totalCsv  = count($rows);

    // Load existing IDs
    $existing = $pdo->query("SELECT member_id FROM insurance_members")->fetchAll(PDO::FETCH_COLUMN);
    $existSet = array_flip($existing);

    $cntNew        = 0;
    $cntUpdated    = 0;
    $cntInactivated = 0;

    $upsert = $pdo->prepare("
        INSERT INTO insurance_members
            (member_id, full_name, member_status, citizen_id, date_of_birth,
             insurance_status, coverage_start, coverage_end, policy_number, remarks)
        VALUES
            (:mid, :fn, :ms, :cid, :dob, 'Active', :cs, :ce, :pn, :rem)
        ON DUPLICATE KEY UPDATE
            full_name        = VALUES(full_name),
            member_status    = VALUES(member_status),
            citizen_id       = VALUES(citizen_id),
            date_of_birth    = VALUES(date_of_birth),
            insurance_status = 'Active',
            coverage_start   = VALUES(coverage_start),
            coverage_end     = VALUES(coverage_end),
            policy_number    = VALUES(policy_number),
            remarks          = VALUES(remarks)
    ");

    $pdo->beginTransaction();
    try {
        foreach ($rows as $r) {
            $mid = $r['member_id'];
            $upsert->execute([
                ':mid' => $mid,
                ':fn'  => $r['full_name']      ?? '',
                ':ms'  => $r['member_status']  ?? '',
                ':cid' => $r['citizen_id']     ?? '',
                ':dob' => norm_date($r['date_of_birth'] ?? null),
                ':cs'  => norm_date($r['coverage_start'] ?? null),
                ':ce'  => norm_date($r['coverage_end']   ?? null),
                ':pn'  => $r['policy_number']  ?? '',
                ':rem' => $r['remarks']        ?? '',
            ]);
            if (isset($existSet[$mid])) $cntUpdated++; else $cntNew++;
        }

        // Inactivate members not in file
        $inactivate = $pdo->prepare("
            UPDATE insurance_members SET insurance_status = 'Inactive'
            WHERE member_id = :mid AND insurance_status = 'Active'
        ");
        foreach ($existing as $mid) {
            if (!isset($csvIdSet[$mid])) {
                $inactivate->execute([':mid' => $mid]);
                if ($inactivate->rowCount() > 0) $cntInactivated++;
            }
        }

        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['status' => 'error', 'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()]);
        exit;
    }

    log_activity('insurance_upload', "total={$totalCsv}, new={$cntNew}, updated={$cntUpdated}, inactivated={$cntInactivated}");

    echo json_encode([
        'status'           => 'success',
        'total_csv'        => $totalCsv,
        'total_new'        => $cntNew,
        'total_updated'    => $cntUpdated,
        'total_inactivated'=> $cntInactivated,
    ]);
    exit;
}

// ══════════════════════════════════════════════════════════════════════════════
// ACTION: list_members — paginated member list
// ══════════════════════════════════════════════════════════════════════════════
if ($action === 'list_members') {
    ensure_insurance_table($pdo);

    $page    = max(1, (int)($_POST['page'] ?? 1));
    $perPage = 20;
    $offset  = ($page - 1) * $perPage;
    $search  = trim($_POST['search']        ?? '');
    $fType   = trim($_POST['filter_type']   ?? '');
    $fStatus = trim($_POST['filter_status'] ?? '');

    $where  = [];
    $params = [];

    if ($search !== '') {
        $where[]      = '(member_id LIKE :s OR full_name LIKE :s2 OR citizen_id LIKE :s3)';
        $params[':s']  = "%{$search}%";
        $params[':s2'] = "%{$search}%";
        $params[':s3'] = "%{$search}%";
    }
    if ($fType !== '') {
        $where[]        = 'member_status = :ft';
        $params[':ft']  = $fType;
    }
    if (in_array($fStatus, ['Active', 'Inactive'], true)) {
        $where[]        = 'insurance_status = :fs';
        $params[':fs']  = $fStatus;
    }

    $wSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM insurance_members {$wSql}");
    $countStmt->execute($params);
    $total = (int)$countStmt->fetchColumn();

    $stmt = $pdo->prepare("
        SELECT member_id, full_name, member_status, insurance_status,
               coverage_start, coverage_end, citizen_id
        FROM insurance_members {$wSql}
        ORDER BY full_name ASC
        LIMIT {$perPage} OFFSET {$offset}
    ");
    $stmt->execute($params);

    echo json_encode([
        'status'   => 'ok',
        'total'    => $total,
        'page'     => $page,
        'per_page' => $perPage,
        'members'  => $stmt->fetchAll(PDO::FETCH_ASSOC),
    ]);
    exit;
}

// ══════════════════════════════════════════════════════════════════════════════
// ACTION: set_visibility — toggle insurance card on user/hub.php
// ══════════════════════════════════════════════════════════════════════════════
if ($action === 'set_visibility') {
    $active           = ($_POST['active'] ?? '0') === '1';
    $settingsFile     = __DIR__ . '/../config/site_settings.json';
    $settings         = file_exists($settingsFile) ? (json_decode(file_get_contents($settingsFile), true) ?? []) : [];
    $settings['show_insurance'] = $active;

    if (file_put_contents($settingsFile, json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) !== false) {
        log_activity('update_site_settings', 'Toggle Insurance Card: ' . ($active ? 'ON' : 'OFF'));
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'ไม่สามารถบันทึกข้อมูลได้']);
    }
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Unknown action']);
