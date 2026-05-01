<?php
/**
 * ⚠️ DELETE THIS FILE IMMEDIATELY AFTER USE
 * Runs artisan in-process via Laravel bootstrap (no exec/php.exe needed).
 */

define('SECRET', 'rsu2026deploy');
define('BASE_PATH', dirname(__DIR__));

@set_time_limit(300);
@ini_set('memory_limit', '512M');

$secret = $_GET['secret'] ?? $_POST['secret'] ?? '';
if ($secret !== SECRET) {
    http_response_code(403);
    header('Content-Type: application/json');
    die(json_encode(['error' => 'Unauthorized']));
}

$allowed = [
    'migrate'         => ['migrate',        ['--force' => true]],
    'migrate:status'  => ['migrate:status', []],
    'seed'            => ['db:seed',        ['--force' => true]],
    'migrate+seed'    => null,
    'key:generate'    => ['key:generate',   ['--force' => true]],
    'optimize:clear'  => ['optimize:clear', []],
    'config:cache'    => ['config:cache',   []],
    'route:cache'     => ['route:cache',    []],
    'view:cache'      => ['view:cache',     []],
    'storage:link'    => ['storage:link',   []],
];

$diag = $_GET['diag'] ?? null;
if ($diag !== null) {
    header('Content-Type: application/json');
    echo json_encode([
        'php_version'        => PHP_VERSION,
        'base_path'          => BASE_PATH,
        'autoload_exists'    => file_exists(BASE_PATH . '/vendor/autoload.php'),
        'bootstrap_exists'   => file_exists(BASE_PATH . '/bootstrap/app.php'),
        'env_exists'         => file_exists(BASE_PATH . '/.env'),
        'storage_writable'   => is_writable(BASE_PATH . '/storage'),
        'bootstrap_writable' => is_writable(BASE_PATH . '/bootstrap/cache'),
    ], JSON_PRETTY_PRINT);
    exit;
}

$run = $_GET['run'] ?? $_POST['run'] ?? null;
if ($run === null) {
    goto render_ui;
}

header('Content-Type: application/json');

if (!array_key_exists($run, $allowed)) {
    http_response_code(400);
    echo json_encode(['error' => 'Command not allowed']);
    exit;
}

try {
    require BASE_PATH . '/vendor/autoload.php';
    $app = require BASE_PATH . '/bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();
} catch (\Throwable $e) {
    echo json_encode([
        'command'  => $run,
        'output'   => "Bootstrap failed: " . $e->getMessage() . "\n\n" . $e->getTraceAsString(),
        'exitCode' => -1,
    ]);
    exit;
}

$callArtisan = function (string $command, array $params) use ($kernel) {
    $buffer = new Symfony\Component\Console\Output\BufferedOutput();
    $code = $kernel->call($command, $params, $buffer);
    return ['output' => $buffer->fetch(), 'code' => $code];
};

if ($run === 'migrate+seed') {
    $r1 = $callArtisan('migrate', ['--force' => true]);
    $combined = $r1['output'];
    $exitCode = $r1['code'];
    if ($exitCode === 0) {
        $r2 = $callArtisan('db:seed', ['--force' => true]);
        $combined .= "\n--- db:seed ---\n" . $r2['output'];
        $exitCode = $r2['code'];
    }
} else {
    [$cmd, $params] = $allowed[$run];
    $r = $callArtisan($cmd, $params);
    $combined = $r['output'];
    $exitCode = $r['code'];
}

if (trim($combined) === '') {
    $combined = '(คำสั่งรันเสร็จ ไม่มี output)';
}

echo json_encode([
    'command'  => $run,
    'output'   => $combined,
    'exitCode' => $exitCode,
]);
exit;

render_ui:
?><!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>Artisan Runner</title>
<style>
  body { background:#111; color:#e2e8f0; font-family:monospace; padding:2rem; }
  h1 { color:#2dd4bf; margin-bottom:.5rem; }
  .warn { color:#f87171; margin-bottom:1.5rem; font-size:.9rem; }
  .btn-group { display:flex; flex-wrap:wrap; gap:.5rem; margin-bottom:1.5rem; }
  button {
    padding:.5rem 1rem; border:none; border-radius:6px;
    background:#3b82f6; color:#fff; cursor:pointer; font-family:monospace;
  }
  button:hover { background:#2563eb; }
  button.danger { background:#ef4444; }
  button.danger:hover { background:#dc2626; }
  button.muted  { background:#475569; }
  #status { color:#94a3b8; margin-bottom:.5rem; }
  #output {
    background:#000; color:#86efac; padding:1rem; border-radius:8px;
    min-height:200px; white-space:pre-wrap; font-size:.85rem;
  }
</style>
</head>
<body>
<h1>Artisan Runner (in-process)</h1>
<p class="warn">⚠ ลบไฟล์นี้ทันทีหลังใช้งาน!</p>

<div class="btn-group">
  <button class="muted" onclick="runDiag()">diag</button>
  <?php foreach (array_keys($allowed) as $cmd): ?>
    <button
      <?= $cmd === 'migrate+seed' ? 'class="danger"' : '' ?>
      onclick="runCmd(<?= htmlspecialchars(json_encode($cmd)) ?>)">
      <?= htmlspecialchars($cmd) ?>
    </button>
  <?php endforeach; ?>
</div>

<div id="status">พร้อมใช้งาน</div>
<pre id="output"></pre>

<script>
const SECRET = <?= json_encode(SECRET) ?>;

async function runCmd(cmd) {
  document.getElementById('status').textContent = 'คำสั่ง: ' + cmd;
  document.getElementById('output').textContent = 'กำลังรัน...';
  try {
    const res = await fetch('?secret=' + encodeURIComponent(SECRET) + '&run=' + encodeURIComponent(cmd));
    const data = await res.json();
    document.getElementById('output').textContent =
      data.output || data.error || '(ไม่มี output)';
    document.getElementById('status').textContent =
      'คำสั่ง: ' + cmd + ' · exit code: ' + (data.exitCode ?? '—');
  } catch (e) {
    document.getElementById('output').textContent = 'Error: ' + e.message;
  }
}

async function runDiag() {
  document.getElementById('status').textContent = 'diag';
  document.getElementById('output').textContent = 'กำลังโหลด...';
  const res = await fetch('?secret=' + encodeURIComponent(SECRET) + '&diag=1');
  document.getElementById('output').textContent = await res.text();
}
</script>
</body>
</html>
