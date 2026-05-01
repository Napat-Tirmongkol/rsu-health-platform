<?php
/**
 * Artisan Runner — ใช้ครั้งเดียวแล้วลบทิ้ง
 * ลบไฟล์นี้ทันทีหลังใช้งาน!
 */

$SECRET = 'CHANGE_THIS_SECRET';   // เปลี่ยนเป็นรหัสที่รู้แค่คนเดียว

if (($_GET['secret'] ?? '') !== $SECRET) {
    http_response_code(403);
    die('Forbidden');
}

$allowed = [
    'migrate'         => ['migrate', '--force'],
    'migrate:status'  => ['migrate', '--pretend'],
    'seed'            => ['db:seed', '--force'],
    'migrate+seed'    => null,
    'key:generate'    => ['key:generate', '--force'],
    'optimize:clear'  => ['optimize:clear'],
    'config:cache'    => ['config:cache'],
    'route:cache'     => ['route:cache'],
    'view:cache'      => ['view:cache'],
    'storage:link'    => ['storage:link'],
];

$run = $_GET['run'] ?? '';
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>Artisan Runner</title>
<style>
  body { font-family: monospace; background: #1e1e1e; color: #d4d4d4; padding: 2rem; }
  h2   { color: #4ec9b0; }
  .btn { display: inline-block; margin: .25rem; padding: .5rem 1rem;
         background: #0e639c; color: #fff; text-decoration: none; border-radius: 4px; }
  .btn:hover { background: #1177bb; }
  .btn.danger { background: #c72e2e; }
  pre  { background: #111; padding: 1rem; border-radius: 6px;
         white-space: pre-wrap; word-break: break-all; max-height: 70vh; overflow: auto; }
  .warn { color: #f14c4c; font-weight: bold; }
</style>
</head>
<body>
<h2>Artisan Runner</h2>
<p class="warn">⚠ ลบไฟล์นี้ทันทีหลังใช้งาน!</p>

<div>
<?php foreach (array_keys($allowed) as $cmd): ?>
  <a class="btn <?= $cmd === 'migrate+seed' ? 'danger' : '' ?>"
     href="?secret=<?= htmlspecialchars($SECRET) ?>&run=<?= $cmd ?>">
    <?= htmlspecialchars($cmd) ?>
  </a>
<?php endforeach; ?>
</div>

<?php if ($run !== ''): ?>
<h3>คำสั่ง: <code><?= htmlspecialchars($run) ?></code></h3>
<pre><?php

define('LARAVEL_START', microtime(true));

$app = require __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

ob_start();

if ($run === 'migrate+seed') {
    $kernel->call('migrate', ['--force' => true]);
    echo $kernel->output();
    $kernel->call('db:seed', ['--force' => true]);
    echo $kernel->output();
} elseif (isset($allowed[$run])) {
    $args = $allowed[$run];
    $cmd  = array_shift($args);
    $opts = [];
    foreach ($args as $a) {
        $a = ltrim($a, '-');
        $opts["--$a"] = true;
    }
    $kernel->call($cmd, $opts);
    echo $kernel->output();
} else {
    echo "คำสั่งไม่ถูกต้อง";
}

echo ob_get_clean();

$elapsed = round(microtime(true) - LARAVEL_START, 2);
echo "\n\n✓ เสร็จใน {$elapsed}s";

?></pre>
<?php endif; ?>

</body>
</html>
