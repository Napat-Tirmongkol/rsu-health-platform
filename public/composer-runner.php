<?php
/**
 * ⚠️ DELETE THIS FILE IMMEDIATELY AFTER USE
 * Runs composer.phar in-process (no external CLI execution).
 */

define('SECRET', 'rsu2026deploy');
define('BASE_PATH', dirname(__DIR__));
define('PHAR_PATH', BASE_PATH . '/composer.phar');

@set_time_limit(900);
@ini_set('memory_limit', '1536M');

if (function_exists('opcache_reset')) {
    @opcache_reset();
}

$secret = $_GET['secret'] ?? $_POST['secret'] ?? '';
if ($secret !== SECRET) {
    http_response_code(403);
    header('Content-Type: application/json');
    die(json_encode(['error' => 'Unauthorized']));
}

$allowed = [
    'install'      => ['command' => 'install',      '--no-dev' => true, '--optimize-autoloader' => true, '--no-interaction' => true, '--prefer-dist' => true],
    'install:dev'  => ['command' => 'install',      '--no-interaction' => true],
    'update'       => ['command' => 'update',       '--no-dev' => true, '--with-all-dependencies' => true, '--no-interaction' => true],
    'dump-autoload'=> ['command' => 'dump-autoload','--optimize' => true],
    'show'         => ['command' => 'show'],
    'diagnose'     => ['command' => 'diagnose'],
    'about'        => ['command' => 'about'],
];

$diag = $_GET['diag'] ?? null;
if ($diag !== null) {
    header('Content-Type: application/json');
    echo json_encode([
        'php_version'        => PHP_VERSION,
        'base_path'          => BASE_PATH,
        'phar_path'          => PHAR_PATH,
        'phar_exists'        => file_exists(PHAR_PATH),
        'phar_size'          => file_exists(PHAR_PATH) ? filesize(PHAR_PATH) : null,
        'composer_json'      => file_exists(BASE_PATH . '/composer.json'),
        'vendor_writable'    => is_dir(BASE_PATH . '/vendor') ? is_writable(BASE_PATH . '/vendor') : is_writable(BASE_PATH),
        'phar_readonly_ini'  => ini_get('phar.readonly'),
        'allow_url_fopen'    => ini_get('allow_url_fopen'),
        'memory_limit'       => ini_get('memory_limit'),
        'temp_dir'           => sys_get_temp_dir(),
        'temp_writable'      => is_writable(sys_get_temp_dir()),
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

if (!file_exists(PHAR_PATH)) {
    echo json_encode(['error' => 'composer.phar ไม่พบที่ ' . PHAR_PATH]);
    exit;
}

putenv('COMPOSER_HOME=' . sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'composer-home');
putenv('COMPOSER_NO_INTERACTION=1');
putenv('COMPOSER_ALLOW_SUPERUSER=1');
@mkdir(sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'composer-home', 0755, true);

chdir(BASE_PATH);

try {
    require_once 'phar://' . PHAR_PATH . '/vendor/autoload.php';

    $app = new Composer\Console\Application();
    $app->setAutoExit(false);

    $input = new Symfony\Component\Console\Input\ArrayInput($allowed[$run]);
    $output = new Symfony\Component\Console\Output\BufferedOutput(
        Symfony\Component\Console\Output\OutputInterface::VERBOSITY_NORMAL,
        false
    );

    $exitCode = $app->run($input, $output);
    $text = $output->fetch();

    if (trim($text) === '') {
        $text = '(คำสั่งรันเสร็จ ไม่มี output)';
    }

    echo json_encode([
        'command'  => $run,
        'output'   => $text,
        'exitCode' => $exitCode,
    ]);
} catch (\Throwable $e) {
    echo json_encode([
        'command'  => $run,
        'output'   => 'Exception: ' . $e->getMessage() . "\n\n" . $e->getTraceAsString(),
        'exitCode' => -1,
    ]);
}
exit;

render_ui:
?><!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>Composer Runner</title>
<style>
  body { background:#111; color:#e2e8f0; font-family:monospace; padding:2rem; }
  h1 { color:#a78bfa; margin-bottom:.5rem; }
  .warn { color:#f87171; margin-bottom:1.5rem; font-size:.9rem; }
  .btn-group { display:flex; flex-wrap:wrap; gap:.5rem; margin-bottom:1.5rem; }
  button {
    padding:.5rem 1rem; border:none; border-radius:6px;
    background:#8b5cf6; color:#fff; cursor:pointer; font-family:monospace;
  }
  button:hover { background:#7c3aed; }
  button.primary { background:#10b981; }
  button.primary:hover { background:#059669; }
  button.muted   { background:#475569; }
  #status { color:#94a3b8; margin-bottom:.5rem; }
  #output {
    background:#000; color:#86efac; padding:1rem; border-radius:8px;
    min-height:200px; white-space:pre-wrap; font-size:.85rem; max-height:600px; overflow-y:auto;
  }
</style>
</head>
<body>
<h1>Composer Runner (in-process)</h1>
<p class="warn">⚠ ลบไฟล์นี้ทันทีหลังใช้งาน! · จะรันนานหลายนาที อย่าปิด tab</p>

<div class="btn-group">
  <button class="muted" onclick="runDiag()">diag</button>
  <button onclick="runCmd('about')">about</button>
  <button onclick="runCmd('diagnose')">diagnose</button>
  <button class="primary" onclick="runCmd('install')">install (production)</button>
  <button onclick="runCmd('install:dev')">install (with dev)</button>
  <button onclick="runCmd('update')">update</button>
  <button onclick="runCmd('dump-autoload')">dump-autoload</button>
  <button onclick="runCmd('show')">show</button>
</div>

<div id="status">พร้อมใช้งาน</div>
<pre id="output"></pre>

<script>
const SECRET = <?= json_encode(SECRET) ?>;

async function runCmd(cmd) {
  document.getElementById('status').textContent = 'คำสั่ง: ' + cmd + ' (กำลังรัน อาจใช้เวลา 2-5 นาที)';
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
