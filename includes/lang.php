<?php
declare(strict_types=1);
/**
 * includes/lang.php
 * Language helper for e-Campaign pages.
 *
 * Switch language: navigate to any page with ?lang=en or ?lang=th
 * Preference is stored in $_SESSION['lang'] (default: 'th')
 */

if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}

// ── Handle ?lang= switch ─────────────────────────────────────────────────────
if (isset($_GET['lang'])) {
    $requested = strtolower(trim($_GET['lang']));
    if (in_array($requested, ['th', 'en'], true)) {
        $_SESSION['lang'] = $requested;
    }
    // Redirect back without ?lang= to keep URLs clean
    $params = $_GET;
    unset($params['lang']);
    $path = strtok($_SERVER['REQUEST_URI'], '?');
    $qs   = http_build_query($params);
    header('Location: ' . $path . ($qs !== '' ? '?' . $qs : ''), true, 303);
    exit;
}

// ── Resolve language ─────────────────────────────────────────────────────────
$GLOBALS['_lang'] = $_SESSION['lang'] ?? 'th';

$_langFile = __DIR__ . '/../lang/' . $GLOBALS['_lang'] . '.php';
$GLOBALS['_tr'] = file_exists($_langFile)
    ? require $_langFile
    : require __DIR__ . '/../lang/th.php';

// ── Helpers ──────────────────────────────────────────────────────────────────

/** Translate a key; supports sprintf placeholders (__('time.available', 5)) */
function __(string $key, mixed ...$args): string
{
    $str = $GLOBALS['_tr'][$key] ?? $key;
    return $args ? vsprintf($str, $args) : $str;
}

/** Current language code: 'th' or 'en' */
function current_lang(): string
{
    return $GLOBALS['_lang'] ?? 'th';
}

/** URL that switches to the other language, preserving current query params */
function lang_switch_url(): string
{
    $other  = current_lang() === 'th' ? 'en' : 'th';
    $params = $_GET;
    unset($params['lang']);
    $params['lang'] = $other;
    $path = strtok($_SERVER['REQUEST_URI'], '?');
    return $path . '?' . http_build_query($params);
}

/**
 * Format Y-m-d date for display.
 * Thai  → "5 ม.ค. 2568"  (Buddhist year)
 * English → "5 Jan 2025"
 */
function ecampaign_format_date(string $dateStr): string
{
    if ($dateStr === '') return '';
    [$y, $m, $d] = explode('-', $dateStr);
    $tr      = $GLOBALS['_tr'];
    $months  = $tr['bookings.months_short']  ?? ['','Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
    $buddhist = $tr['bookings.date_buddhist'] ?? false;
    $year    = $buddhist ? (int)$y + 543 : (int)$y;
    return (int)$d . ' ' . ($months[(int)$m] ?? '') . ' ' . $year;
}

/**
 * Day-of-week name for a Y-m-d string.
 * Thai: "อาทิตย์" … "เสาร์"   English: "Sun" … "Sat"
 */
function ecampaign_format_dow(string $dateStr): string
{
    if ($dateStr === '') return '';
    $tr  = $GLOBALS['_tr'];
    $dow = $tr['bookings.dow'] ?? ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
    return $dow[(int)date('w', strtotime($dateStr))] ?? '';
}
