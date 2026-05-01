<?php
/**
 * ⚠️ DELETE THIS FILE IMMEDIATELY AFTER USE
 * For one-time deployment tasks only. Never commit with secret exposed.
 */

define('SECRET', 'rsu2026deploy');
define('BASE_PATH', dirname(__DIR__));

$secret = $_GET['secret'] ?? $_POST['secret'] ?? '';
if ($secret !== SECRET) {
    http_response_code(403);
    die(json_encode(['error' => 'Unauthorized']));
}

$php = PHP_BINARY;

$allowed = [
    'migrate'         => [$php, 'artisan', 'migrate', '--force', '--no-interaction'],
    'migrate:status'  => [$php, 'artisan', 'migrate:status'],
    'seed'            => [$php, 'artisan', 'db:seed', '--force', '--no-interaction'],
    'migrate+seed'    => null,
    'key:generate'    => [$php, 'artisan', 'key:generate', '--force'],
    'optimize:clear'  => [$php, 'artisan', 'optimize:clear'],
    'config:cache'    => [$php, 'artisan', 'config:cache'],
    'route:cache'     => [$php, 'artisan', 'route:cache'],
    'view:cache'      => [$php, 'artisan', 'view:cache'],
    'storage:link'    => [$php, 'artisan', 'storage:link'],
];

$diag = $_GET['diag'] ?? null;
if ($diag !== null) {
    header('Content-Type: application/json');
    $disabledFunctions = array_map('trim', explode(',', ini_get('disable_functions')));
    echo json_encode([
        'PHP_BINARY'        => PHP_BINARY,
        'php_version'       => PHP_VERSION,
        'base_path'         => BASE_PATH,
        'artisan_exists'    => file_exists(BASE_PATH . '/artisan'),
        'exec_available'    => function_exists('exec') && !in_array('exec', $disabledFunctions),
        'disable_functions' => ini_get('disable_functions'),
        'cwd'               => getcwd(),
    ], JSON_PRETTY_PRINT);
    exit;
}

$run = $_GET['run'] ?? $_POST['run'] ?? null;

if ($run !== null) {
    header('Content-Type: application/json');

    if (!array_key_exists($run, $allowed)) {
        http_response_code(400);
        echo json_encode(['error' => 'Command not allowed']);
        exit;
    }

    chdir(BASE_PATH);

    $output = [];
    $exitCode = 0;

    if (!function_exists('exec') || in_array('exec', array_map('trim', explode(',', ini_get('disable_functions'))))) {
        echo json_encode(['error' => 'exec() ถูก disable บนเซิร์ฟเวอร์นี้']);
        exit;
    }

    if ($run === 'migrate+seed') {
        $migrateCmd = implode(' ', array_map('escapeshellarg', [$php, 'artisan', 'migrate', '--force', '--no-interaction'])) . ' 2>&1';
        exec($migrateCmd, $output, $exitCode);
        if ($exitCode === 0) {
            $seedOutput = [];
            $seedCmd = implode(' ', array_map('escapeshellarg', [$php, 'artisan', 'db:seed', '--force', '--no-interaction'])) . ' 2>&1';
            exec($seedCmd, $seedOutput, $exitCode);
            $output = array_merge($output, ['--- db:seed ---'], $seedOutput);
        }
    } else {
        $cmd = implode(' ', array_map('escapeshellarg', $allowed[$run])) . ' 2>&1';
        exec($cmd, $output, $exitCode);
    }

    echo json_encode([
        'command'  => $run,
        'output'   => implode("\n", $output),
        'exitCode' => $exitCode,
    ]);
    exit;
}

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
  #status { color:#94a3b8; margin-bottom:.5rem; }
  #output {
    background:#000; color:#86efac; padding:1rem; border-radius:8px;
    min-height:200px; white-space:pre-wrap; font-size:.85rem;
  }
</style>
</head>
<body>
<h1>Artisan Runner</h1>
<p class="warn">⚠ ลบไฟล์นี้ทันทีหลังใช้งาน!</p>

<div class="btn-group">
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
async function runCmd(cmd) {
  document.getElementById('status').textContent = 'คำสั่ง: ' + cmd;
  document.getElementById('output').textContent = 'กำลังรัน...';

  try {
    const res = await fetch('?secret=<?= SECRET ?>&run=' + encodeURIComponent(cmd));
    const data = await res.json();
    document.getElementById('output').textContent =
      data.output || data.error || '(ไม่มี output)';
    document.getElementById('status').textContent =
      'คำสั่ง: ' + cmd + ' · exit code: ' + (data.exitCode ?? '—');
  } catch (e) {
    document.getElementById('output').textContent = 'Error: ' + e.message;
  }
}
</script>
</body>
</html>
