<?php
// admin/sentry_fixes.php — Claude auto-fix history from Sentry webhooks
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/includes/auth.php';

$pdo = db();

// ── Auto-create table if not exists ──────────────────────────────────────────
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS sys_sentry_fixes (
        id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        issue_id    VARCHAR(64)   NOT NULL DEFAULT '',
        error_title VARCHAR(500)  NOT NULL DEFAULT '',
        issue_url   VARCHAR(500)  NOT NULL DEFAULT '',
        file_path   VARCHAR(400)  NOT NULL DEFAULT '',
        analysis    TEXT          NOT NULL DEFAULT '',
        fixed_code  MEDIUMTEXT    NOT NULL DEFAULT '',
        status      VARCHAR(32)   NOT NULL DEFAULT 'pending',
        note        VARCHAR(1000) NOT NULL DEFAULT '',
        created_at  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_status     (status),
        INDEX idx_created_at (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
} catch (PDOException $e) {
    $fatalErr = $e->getMessage();
}

// ── Clear history action (superadmin only) ────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'clear') {
    if (($_SESSION['admin_role'] ?? '') === 'superadmin') {
        $pdo->exec("TRUNCATE TABLE sys_sentry_fixes");
    }
    header('Location: sentry_fixes.php?cleared=1');
    exit;
}

// ── Filters ───────────────────────────────────────────────────────────────────
$page         = max(1, (int)($_GET['page']   ?? 1));
$perPage      = 20;
$offset       = ($page - 1) * $perPage;
$filterStatus = in_array($_GET['status'] ?? '', ['pending','pr_created','pr_failed','skipped','no_change','cannot_fix','error'], true)
                ? $_GET['status'] : '';
$search       = trim($_GET['search'] ?? '');

$where  = 'WHERE 1=1';
$params = [];
if ($filterStatus !== '') {
    $where   .= ' AND status = ?';
    $params[] = $filterStatus;
}
if ($search !== '') {
    $where   .= ' AND (error_title LIKE ? OR file_path LIKE ?)';
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}

// ── Fetch rows ────────────────────────────────────────────────────────────────
$totalRows = 0;
$rows      = [];
$stats     = [];

try {
    $cntStmt = $pdo->prepare("SELECT COUNT(*) FROM sys_sentry_fixes {$where}");
    $cntStmt->execute($params);
    $totalRows = (int)$cntStmt->fetchColumn();

    $stmt = $pdo->prepare(
        "SELECT id, issue_id, error_title, issue_url, file_path, analysis, status, note, created_at
         FROM sys_sentry_fixes {$where}
         ORDER BY created_at DESC
         LIMIT {$perPage} OFFSET {$offset}"
    );
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Status summary counts
    $statsRaw = $pdo->query(
        "SELECT status, COUNT(*) as cnt FROM sys_sentry_fixes GROUP BY status"
    )->fetchAll(PDO::FETCH_ASSOC);
    foreach ($statsRaw as $r) {
        $stats[$r['status']] = (int)$r['cnt'];
    }
} catch (PDOException $e) {
    $fetchErr = $e->getMessage();
}

$totalPages  = (int)ceil($totalRows / $perPage);
$isSuperAdmin = ($_SESSION['admin_role'] ?? '') === 'superadmin';

// ── Webhook URL for display ───────────────────────────────────────────────────
$webhookUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http')
    . '://' . ($_SERVER['HTTP_HOST'] ?? 'yourdomain.com')
    . rtrim(dirname(dirname($_SERVER['PHP_SELF'])), '/') . '/api/sentry_webhook.php';
?>
<?php require_once __DIR__ . '/includes/header.php'; ?>

<div class="flex h-screen overflow-hidden bg-gray-50">
<?php require_once __DIR__ . '/includes/sidebar.php'; ?>

<main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8">

<?php renderPageHeader(
    '<i class="fa-solid fa-robot mr-2 text-violet-600"></i> Claude Auto-Fix',
    'SENTRY WEBHOOK + CLAUDE API',
    '<a href="sentry_test.php" class="btn-secondary text-sm"><i class="fa-brands fa-sentry mr-1"></i>Sentry Test</a>'
); ?>

<?php if (isset($_GET['cleared'])): ?>
<div class="mb-4 rounded-xl bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">
    <i class="fa-solid fa-check-circle mr-1"></i> ล้างประวัติเรียบร้อยแล้ว
</div>
<?php endif; ?>

<?php if (isset($fatalErr)): ?>
<div class="mb-4 rounded-xl bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">
    <strong>DB Error:</strong> <?= htmlspecialchars($fatalErr) ?>
</div>
<?php endif; ?>

<!-- ── Webhook Setup Card ──────────────────────────────────────────────────── -->
<div class="mb-6 rounded-2xl bg-white border border-gray-100 shadow-sm overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
        <h2 class="font-bold text-gray-800 text-sm flex items-center gap-2">
            <i class="fa-solid fa-plug text-violet-500"></i>
            Webhook URL
        </h2>
        <span class="text-[10px] font-black uppercase tracking-widest text-violet-500">ตั้งค่าใน Sentry</span>
    </div>
    <div class="px-5 py-4 space-y-3">
        <div class="flex items-center gap-2 bg-gray-50 rounded-xl px-4 py-3 border border-gray-200">
            <code id="webhook-url" class="text-xs text-gray-700 flex-1 break-all font-mono select-all">
                <?= htmlspecialchars($webhookUrl) ?>
            </code>
            <button onclick="copyWebhook()" class="flex-shrink-0 text-violet-600 hover:text-violet-800 transition-colors" title="คัดลอก">
                <i class="fa-regular fa-copy"></i>
            </button>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-[11px] text-gray-600">
            <div class="flex items-start gap-2">
                <span class="mt-0.5 w-5 h-5 rounded-full bg-violet-100 text-violet-600 flex items-center justify-center font-black text-[10px] flex-shrink-0">1</span>
                <span>เพิ่ม <code class="bg-gray-100 px-1 rounded">SENTRY_WEBHOOK_SECRET</code>, <code class="bg-gray-100 px-1 rounded">ANTHROPIC_API_KEY</code>, <code class="bg-gray-100 px-1 rounded">GITHUB_TOKEN</code>, <code class="bg-gray-100 px-1 rounded">GITHUB_REPO</code> ใน <code class="bg-gray-100 px-1 rounded">config/secrets.php</code></span>
            </div>
            <div class="flex items-start gap-2">
                <span class="mt-0.5 w-5 h-5 rounded-full bg-violet-100 text-violet-600 flex items-center justify-center font-black text-[10px] flex-shrink-0">2</span>
                <span>ไปที่ Sentry → Project → Settings → Integrations → Webhooks → Add Webhook URL ด้านบน</span>
            </div>
            <div class="flex items-start gap-2">
                <span class="mt-0.5 w-5 h-5 rounded-full bg-violet-100 text-violet-600 flex items-center justify-center font-black text-[10px] flex-shrink-0">3</span>
                <span>สร้าง Alert Rule ใน Sentry ที่ส่ง notification ไปยัง webhook เมื่อมี error ใหม่</span>
            </div>
        </div>
    </div>
</div>

<!-- ── Status Summary ─────────────────────────────────────────────────────── -->
<?php
$statusMeta = [
    'pr_created'  => ['label' => 'PR Created',    'icon' => 'fa-code-pull-request', 'color' => 'green'],
    'pending'     => ['label' => 'Pending Review', 'icon' => 'fa-clock',             'color' => 'yellow'],
    'pr_failed'   => ['label' => 'PR Failed',      'icon' => 'fa-triangle-exclamation','color' => 'red'],
    'cannot_fix'  => ['label' => 'Cannot Fix',     'icon' => 'fa-ban',               'color' => 'orange'],
    'no_change'   => ['label' => 'No Change',      'icon' => 'fa-minus-circle',      'color' => 'gray'],
    'skipped'     => ['label' => 'Skipped',        'icon' => 'fa-forward',           'color' => 'gray'],
    'error'       => ['label' => 'API Error',      'icon' => 'fa-circle-xmark',      'color' => 'red'],
];
$totalFixes = array_sum($stats);
?>
<div class="mb-6 grid grid-cols-2 sm:grid-cols-4 gap-3">
    <div class="rounded-2xl bg-white border border-gray-100 shadow-sm px-4 py-4">
        <div class="text-2xl font-[950] text-gray-900"><?= number_format($totalFixes) ?></div>
        <div class="text-[11px] text-gray-500 font-medium mt-0.5">ทั้งหมด</div>
    </div>
    <?php foreach (['pr_created','pending','pr_failed','cannot_fix'] as $st): ?>
    <?php $meta = $statusMeta[$st]; $cnt = $stats[$st] ?? 0; ?>
    <a href="?status=<?= $st ?>" class="rounded-2xl bg-white border border-gray-100 shadow-sm px-4 py-4 hover:border-<?= $meta['color'] ?>-300 transition-colors <?= $filterStatus === $st ? 'ring-2 ring-'.$meta['color'].'-400' : '' ?>">
        <div class="text-2xl font-[950] text-<?= $meta['color'] === 'gray' ? 'gray-500' : $meta['color'].'-600' ?>"><?= number_format($cnt) ?></div>
        <div class="text-[11px] text-gray-500 font-medium mt-0.5"><?= $meta['label'] ?></div>
    </a>
    <?php endforeach; ?>
</div>

<!-- ── Filters ────────────────────────────────────────────────────────────── -->
<div class="mb-4 flex flex-wrap gap-3 items-center">
    <form method="GET" class="flex gap-2 flex-wrap flex-1">
        <input type="hidden" name="status" value="<?= htmlspecialchars($filterStatus) ?>">
        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>"
               placeholder="ค้นหา error / ไฟล์…"
               class="border border-gray-200 rounded-lg px-3 py-1.5 text-sm w-56 focus:outline-none focus:ring-2 focus:ring-violet-300">
        <button type="submit" class="btn-secondary text-sm px-3 py-1.5">ค้นหา</button>
        <?php if ($filterStatus !== '' || $search !== ''): ?>
        <a href="sentry_fixes.php" class="btn-secondary text-sm px-3 py-1.5 text-gray-500">
            <i class="fa-solid fa-xmark mr-1"></i>ล้าง
        </a>
        <?php endif; ?>
    </form>

    <!-- Status filter tabs -->
    <div class="flex flex-wrap gap-1.5">
        <a href="sentry_fixes.php<?= $search ? '?search='.urlencode($search) : '' ?>"
           class="text-[11px] px-2.5 py-1 rounded-full font-semibold border transition-colors <?= $filterStatus === '' ? 'bg-gray-800 text-white border-gray-800' : 'border-gray-200 text-gray-600 hover:bg-gray-50' ?>">
            ทั้งหมด
        </a>
        <?php foreach ($statusMeta as $st => $meta): ?>
        <?php if (($stats[$st] ?? 0) > 0): ?>
        <a href="?status=<?= $st ?><?= $search ? '&search='.urlencode($search) : '' ?>"
           class="text-[11px] px-2.5 py-1 rounded-full font-semibold border transition-colors <?= $filterStatus === $st ? 'bg-gray-800 text-white border-gray-800' : 'border-gray-200 text-gray-600 hover:bg-gray-50' ?>">
            <?= $meta['label'] ?> <span class="opacity-60"><?= $stats[$st] ?></span>
        </a>
        <?php endif; ?>
        <?php endforeach; ?>
    </div>

    <?php if ($isSuperAdmin && $totalFixes > 0): ?>
    <form method="POST" onsubmit="return confirm('ล้างประวัติทั้งหมด?')">
        <input type="hidden" name="action" value="clear">
        <button type="submit" class="text-[11px] px-2.5 py-1 rounded-full border border-red-200 text-red-600 hover:bg-red-50 font-semibold transition-colors">
            <i class="fa-solid fa-trash mr-1"></i>ล้างทั้งหมด
        </button>
    </form>
    <?php endif; ?>
</div>

<!-- ── Table ──────────────────────────────────────────────────────────────── -->
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
<?php if (empty($rows)): ?>
    <div class="py-16 text-center text-gray-400">
        <i class="fa-solid fa-robot text-4xl mb-3 block opacity-30"></i>
        <p class="font-semibold text-gray-500">ยังไม่มีการ auto-fix</p>
        <p class="text-xs mt-1">เมื่อ Sentry webhook ทำงาน Claude จะวิเคราะห์ error อัตโนมัติ</p>
    </div>
<?php else: ?>
    <div class="overflow-x-auto">
    <table class="w-full text-sm">
        <thead>
            <tr class="text-[10px] uppercase tracking-widest text-gray-400 border-b border-gray-100">
                <th class="px-5 py-3 text-left font-black">Error</th>
                <th class="px-4 py-3 text-left font-black">ไฟล์</th>
                <th class="px-4 py-3 text-left font-black">สถานะ</th>
                <th class="px-4 py-3 text-left font-black">เวลา</th>
                <th class="px-4 py-3 text-left font-black">รายละเอียด</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
        <?php foreach ($rows as $row): ?>
        <?php
            $st   = $row['status'];
            $meta = $statusMeta[$st] ?? ['label' => $st, 'icon' => 'fa-circle', 'color' => 'gray'];
            $colorMap = [
                'green'  => 'text-green-700  bg-green-50  border-green-200',
                'yellow' => 'text-yellow-700 bg-yellow-50 border-yellow-200',
                'red'    => 'text-red-700    bg-red-50    border-red-200',
                'orange' => 'text-orange-700 bg-orange-50 border-orange-200',
                'gray'   => 'text-gray-500   bg-gray-50   border-gray-200',
            ];
            $badgeClass = $colorMap[$meta['color']] ?? $colorMap['gray'];
        ?>
            <tr class="hover:bg-gray-50/60 transition-colors">
                <td class="px-5 py-3 max-w-xs">
                    <div class="font-semibold text-gray-800 text-xs leading-snug line-clamp-2">
                        <?= htmlspecialchars($row['error_title']) ?>
                    </div>
                    <?php if ($row['issue_id'] !== ''): ?>
                    <div class="text-[10px] text-gray-400 mt-0.5">#<?= htmlspecialchars($row['issue_id']) ?></div>
                    <?php endif; ?>
                </td>
                <td class="px-4 py-3">
                    <code class="text-[10px] text-gray-600 bg-gray-100 px-1.5 py-0.5 rounded break-all">
                        <?= htmlspecialchars($row['file_path']) ?>
                    </code>
                </td>
                <td class="px-4 py-3">
                    <span class="inline-flex items-center gap-1 text-[10px] font-black uppercase tracking-wide px-2 py-1 rounded-full border <?= $badgeClass ?>">
                        <i class="fa-solid <?= $meta['icon'] ?> text-[9px]"></i>
                        <?= $meta['label'] ?>
                    </span>
                </td>
                <td class="px-4 py-3 text-[11px] text-gray-500 whitespace-nowrap">
                    <?= htmlspecialchars(date('d M H:i', strtotime($row['created_at']))) ?>
                </td>
                <td class="px-4 py-3">
                    <?php if ($st === 'pr_created' && str_starts_with($row['note'], 'https://')): ?>
                        <a href="<?= htmlspecialchars($row['note']) ?>" target="_blank" rel="noopener"
                           class="text-[11px] text-violet-600 hover:underline font-semibold">
                            <i class="fa-solid fa-code-pull-request mr-1"></i>View PR
                        </a>
                    <?php elseif ($row['analysis'] !== ''): ?>
                        <button onclick="toggleAnalysis(<?= $row['id'] ?>)"
                                class="text-[11px] text-gray-500 hover:text-violet-600 transition-colors">
                            <i class="fa-solid fa-chevron-down mr-1" id="icon-<?= $row['id'] ?>"></i>Analysis
                        </button>
                    <?php else: ?>
                        <span class="text-[11px] text-gray-400"><?= htmlspecialchars(substr($row['note'], 0, 60)) ?></span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php if ($row['analysis'] !== ''): ?>
            <tr id="analysis-<?= $row['id'] ?>" class="hidden bg-violet-50/40">
                <td colspan="5" class="px-5 py-4">
                    <div class="text-xs text-gray-700 leading-relaxed">
                        <strong class="text-violet-700 block mb-1">Root Cause Analysis</strong>
                        <?= nl2br(htmlspecialchars($row['analysis'])) ?>
                    </div>
                    <?php if ($row['note'] !== '' && !str_starts_with($row['note'], 'https://')): ?>
                    <div class="mt-2 text-[11px] text-gray-500">
                        <strong>Note:</strong> <?= htmlspecialchars($row['note']) ?>
                    </div>
                    <?php endif; ?>
                    <?php if ($row['issue_url'] !== ''): ?>
                    <a href="<?= htmlspecialchars($row['issue_url']) ?>" target="_blank" rel="noopener"
                       class="mt-2 inline-flex items-center gap-1 text-[11px] text-violet-600 hover:underline">
                        <i class="fa-brands fa-sentry text-[10px]"></i> View in Sentry
                    </a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endif; ?>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
    <div class="px-5 py-4 border-t border-gray-100 flex items-center justify-between text-sm text-gray-500">
        <span class="text-xs"><?= number_format($totalRows) ?> รายการ</span>
        <div class="flex gap-1">
            <?php if ($page > 1): ?>
            <a href="?page=<?= $page-1 ?>&status=<?= urlencode($filterStatus) ?>&search=<?= urlencode($search) ?>"
               class="px-3 py-1 rounded-lg border border-gray-200 hover:bg-gray-50 text-xs">← ก่อนหน้า</a>
            <?php endif; ?>
            <span class="px-3 py-1 text-xs">หน้า <?= $page ?> / <?= $totalPages ?></span>
            <?php if ($page < $totalPages): ?>
            <a href="?page=<?= $page+1 ?>&status=<?= urlencode($filterStatus) ?>&search=<?= urlencode($search) ?>"
               class="px-3 py-1 rounded-lg border border-gray-200 hover:bg-gray-50 text-xs">ถัดไป →</a>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
<?php endif; ?>
</div>

<!-- How it works -->
<div class="mt-6 rounded-2xl bg-white border border-gray-100 shadow-sm p-5">
    <h3 class="font-bold text-sm text-gray-800 mb-3 flex items-center gap-2">
        <i class="fa-solid fa-diagram-project text-violet-500"></i>
        วิธีการทำงาน
    </h3>
    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-2 text-[11px] text-gray-600">
        <?php
        $steps = [
            ['fa-bug',              'Sentry ตรวจพบ error'],
            ['fa-paper-plane',      'ส่ง webhook มาที่เซิร์ฟเวอร์นี้'],
            ['fa-robot',            'Claude วิเคราะห์ stack trace'],
            ['fa-file-code',        'Claude อ่านและแก้ไขโค้ด'],
            ['fa-code-pull-request','สร้าง GitHub PR อัตโนมัติ'],
            ['fa-user-check',       'Admin review & merge'],
        ];
        foreach ($steps as $i => $step):
        ?>
        <div class="flex items-center gap-1.5">
            <span class="w-5 h-5 rounded-full bg-violet-100 text-violet-600 flex items-center justify-center font-black text-[9px] flex-shrink-0"><?= $i+1 ?></span>
            <i class="fa-solid <?= $step[0] ?> text-violet-400 text-[10px]"></i>
            <span><?= $step[1] ?></span>
        </div>
        <?php if ($i < count($steps)-1): ?>
        <i class="fa-solid fa-arrow-right text-gray-300 hidden sm:block"></i>
        <?php endif; ?>
        <?php endforeach; ?>
    </div>
</div>

</main>
</div>

<script>
function toggleAnalysis(id) {
    const row  = document.getElementById('analysis-' + id);
    const icon = document.getElementById('icon-' + id);
    if (!row) return;
    const hidden = row.classList.contains('hidden');
    row.classList.toggle('hidden', !hidden);
    icon.classList.toggle('fa-chevron-down', !hidden);
    icon.classList.toggle('fa-chevron-up', hidden);
}

function copyWebhook() {
    const url = document.getElementById('webhook-url').textContent.trim();
    navigator.clipboard.writeText(url).then(() => {
        const btn = event.currentTarget;
        const orig = btn.innerHTML;
        btn.innerHTML = '<i class="fa-solid fa-check text-green-500"></i>';
        setTimeout(() => btn.innerHTML = orig, 2000);
    });
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
