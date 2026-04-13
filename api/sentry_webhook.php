<?php
/**
 * api/sentry_webhook.php — Sentry Alert Webhook receiver
 *
 * Flow:
 *   Sentry detects error → fires POST here → verify signature →
 *   Claude (Anthropic API) analyzes stack trace → generates fix →
 *   GitHub API creates PR on a new branch → log result to DB
 *
 * Required secrets (in config/secrets.php or ENV):
 *   SENTRY_WEBHOOK_SECRET  — from Sentry: Project › Settings › Integrations › Webhooks
 *   ANTHROPIC_API_KEY      — from console.anthropic.com
 *   GITHUB_TOKEN           — Personal Access Token with repo scope
 *   GITHUB_REPO            — "owner/repo" e.g. "napat-tirmongkol/rsu-healthcare-services"
 *   GITHUB_BASE_BRANCH     — default branch to branch from (default: main)
 */
declare(strict_types=1);

// ── 0. POST only ──────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}

header('Content-Type: application/json; charset=utf-8');

// ── 1. Read raw body early (before any output) ────────────────────────────────
$rawBody = (string)file_get_contents('php://input');

// ── 2. Load config (DB + error logger) ───────────────────────────────────────
// error_logger needs DB; Sentry is already loaded via config.php
require_once __DIR__ . '/../includes/error_logger.php';
require_once __DIR__ . '/../config/db_connect.php';

// ── 3. Read secrets ───────────────────────────────────────────────────────────
$_s = [];
$_secretsFile = __DIR__ . '/../config/secrets.php';
if (file_exists($_secretsFile)) {
    $_s = (array)require $_secretsFile;
}
$webhookSecret  = (string)($_s['SENTRY_WEBHOOK_SECRET']  ?? getenv('SENTRY_WEBHOOK_SECRET')  ?: '');
$anthropicKey   = (string)($_s['ANTHROPIC_API_KEY']       ?? getenv('ANTHROPIC_API_KEY')       ?: '');
$githubToken    = (string)($_s['GITHUB_TOKEN']             ?? getenv('GITHUB_TOKEN')             ?: '');
$githubRepo     = (string)($_s['GITHUB_REPO']              ?? getenv('GITHUB_REPO')              ?: '');
$githubBase     = (string)($_s['GITHUB_BASE_BRANCH']       ?? getenv('GITHUB_BASE_BRANCH')       ?: 'main');
unset($_s, $_secretsFile);

// ── 4. Verify Sentry webhook signature ────────────────────────────────────────
// Header: Sentry-Hook-Signature  →  $_SERVER['HTTP_SENTRY_HOOK_SIGNATURE']
if ($webhookSecret !== '') {
    $sigHeader = $_SERVER['HTTP_SENTRY_HOOK_SIGNATURE'] ?? '';
    if ($sigHeader === '') {
        http_response_code(401);
        echo json_encode(['status' => 'error', 'message' => 'Missing signature header']);
        exit;
    }
    $expected = hash_hmac('sha256', $rawBody, $webhookSecret);
    if (!hash_equals($expected, $sigHeader)) {
        http_response_code(401);
        echo json_encode(['status' => 'error', 'message' => 'Invalid signature']);
        exit;
    }
}

// ── 5. Parse payload ──────────────────────────────────────────────────────────
$payload = json_decode($rawBody, true);
if (!is_array($payload)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid JSON']);
    exit;
}

// Only act on "triggered" alert actions
$action = (string)($payload['action'] ?? '');
if ($action !== 'triggered') {
    echo json_encode(['status' => 'skipped', 'reason' => "action='{$action}' (not triggered)"]);
    exit;
}

$event = $payload['data']['event'] ?? [];
$issue = $payload['data']['issue'] ?? [];
if (empty($event)) {
    echo json_encode(['status' => 'skipped', 'reason' => 'no event data']);
    exit;
}

// ── 6. Extract error information ──────────────────────────────────────────────
$errorTitle  = (string)($issue['title']  ?? $event['title']  ?? 'Unknown Error');
$errorLevel  = (string)($issue['level']  ?? $event['level']  ?? 'error');
$issueId     = (string)($issue['id']     ?? '');
$issueUrl    = (string)($issue['permalink'] ?? '');
$timessSeen  = (int)($issue['times_seen'] ?? 1);

// Collect stack frames from all exception values
$frames = [];
$exceptions = $event['exception']['values'] ?? [];
foreach ($exceptions as $exc) {
    $excType  = (string)($exc['type']  ?? '');
    $excValue = (string)($exc['value'] ?? '');
    $rawFrames = $exc['stacktrace']['frames'] ?? [];
    // Sentry puts innermost frame last — reverse so first = crash site
    foreach (array_reverse($rawFrames) as $f) {
        $frames[] = [
            'abs_path'    => (string)($f['abs_path']   ?? $f['filename'] ?? ''),
            'lineno'      => (int)($f['lineno']         ?? 0),
            'function'    => (string)($f['function']    ?? ''),
            'context'     => (string)($f['context_line'] ?? ''),
            'pre_context' => $f['pre_context']           ?? [],
            'post_context'=> $f['post_context']          ?? [],
            'exc_type'    => $excType,
            'exc_value'   => $excValue,
        ];
    }
}

// ── 7. Find the first actionable frame inside our project ─────────────────────
$projectRoot  = (string)realpath(__DIR__ . '/..');
$targetFrame  = null;

foreach ($frames as $f) {
    $absPath = $f['abs_path'];
    if ($absPath === '') continue;

    $realPath = realpath($absPath);
    if ($realPath === false) continue;

    // Must live inside the project and not in vendor/
    if (!str_starts_with($realPath, $projectRoot)) continue;
    if (str_contains($realPath, DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR)) continue;

    $relativePath = ltrim(str_replace($projectRoot, '', $realPath), DIRECTORY_SEPARATOR);

    $targetFrame              = $f;
    $targetFrame['realpath']  = $realPath;
    $targetFrame['relative']  = $relativePath;
    break;
}

if ($targetFrame === null) {
    _saveFix($issueId, $errorTitle, $issueUrl, null, null, null, 'skipped', 'No actionable project file in stack trace');
    echo json_encode(['status' => 'skipped', 'reason' => 'no actionable file found']);
    exit;
}

// ── 8. Read the source file ───────────────────────────────────────────────────
$sourceCode = @file_get_contents($targetFrame['realpath']);
if ($sourceCode === false) {
    _saveFix($issueId, $errorTitle, $issueUrl, $targetFrame['relative'], null, null, 'skipped', 'Cannot read source file');
    echo json_encode(['status' => 'skipped', 'reason' => 'cannot read source file']);
    exit;
}

// ── 9. Build Claude prompt ────────────────────────────────────────────────────
$stackSummary  = "Error: {$errorTitle}\n";
$stackSummary .= "Exception: {$targetFrame['exc_type']}: {$targetFrame['exc_value']}\n";
$stackSummary .= "File: {$targetFrame['relative']}  line {$targetFrame['lineno']}\n";
$stackSummary .= "Times seen: {$timessSeen}\n";
if ($issueUrl !== '') $stackSummary .= "Sentry issue: {$issueUrl}\n";

if ($targetFrame['context'] !== '') {
    $pre  = implode("\n", (array)$targetFrame['pre_context']);
    $post = implode("\n", (array)$targetFrame['post_context']);
    $stackSummary .= "\nCode context around line {$targetFrame['lineno']}:\n";
    if ($pre  !== '') $stackSummary .= $pre . "\n";
    $stackSummary .= "→ " . $targetFrame['context'] . "\n";  // crash line
    if ($post !== '') $stackSummary .= $post . "\n";
}

$stackSummary .= "\nTop stack frames:\n";
foreach (array_slice($frames, 0, 6) as $idx => $f) {
    $stackSummary .= "  [{$idx}] {$f['abs_path']}:{$f['lineno']}";
    if ($f['function'] !== '') $stackSummary .= " in {$f['function']}()";
    $stackSummary .= "\n";
}

$prompt = <<<PROMPT
You are an expert PHP developer reviewing a production error captured by Sentry.

## Error Details
{$stackSummary}
## Source File: {$targetFrame['relative']}
```php
{$sourceCode}
```

## Your Task
1. Identify the root cause of this error in the source file
2. Apply a minimal, targeted fix — do NOT refactor, rename variables, or add features
3. Respond in this EXACT format (no deviations):

### Analysis
[2–4 sentences explaining the root cause]

### Fix
```php
[COMPLETE fixed file — every line, not just the changed section]
```

### Explanation
[1–3 sentences describing what changed and why]

## Rules
- Fix ONLY the confirmed bug — leave all other code untouched
- The fixed file must be syntactically correct and complete
- If the bug requires context you don't have (e.g., DB schema, external config), write "CANNOT_FIX" in the Fix block and explain in Analysis
- Do NOT add comments, docblocks, or new functions unless directly fixing the bug
PROMPT;

// ── 10. Call Anthropic Claude API ─────────────────────────────────────────────
if ($anthropicKey === '') {
    _saveFix($issueId, $errorTitle, $issueUrl, $targetFrame['relative'], null, null, 'skipped', 'ANTHROPIC_API_KEY not configured');
    echo json_encode(['status' => 'skipped', 'reason' => 'ANTHROPIC_API_KEY not set']);
    exit;
}

$claudeResponse = _callAnthropic($anthropicKey, $prompt);
if ($claudeResponse === null) {
    _saveFix($issueId, $errorTitle, $issueUrl, $targetFrame['relative'], null, null, 'error', 'Anthropic API call failed');
    echo json_encode(['status' => 'error', 'reason' => 'Anthropic API unreachable']);
    exit;
}

// ── 11. Parse Claude's response ───────────────────────────────────────────────
$fixedCode   = _extractCodeBlock($claudeResponse);
$analysis    = _extractSection($claudeResponse, 'Analysis');
$explanation = _extractSection($claudeResponse, 'Explanation');

// Detect explicit cannot-fix
if ($fixedCode !== null && str_contains($fixedCode, 'CANNOT_FIX')) {
    _saveFix($issueId, $errorTitle, $issueUrl, $targetFrame['relative'], $analysis, null, 'cannot_fix', $explanation);
    echo json_encode(['status' => 'cannot_fix', 'analysis' => $analysis]);
    exit;
}

// No usable fix produced
if ($fixedCode === null || trim($fixedCode) === '' || trim($fixedCode) === trim($sourceCode)) {
    _saveFix($issueId, $errorTitle, $issueUrl, $targetFrame['relative'], $analysis, null, 'no_change', 'Code unchanged after fix attempt');
    echo json_encode(['status' => 'no_change', 'analysis' => $analysis]);
    exit;
}

// ── 12. Create GitHub Pull Request ────────────────────────────────────────────
if ($githubToken === '' || $githubRepo === '') {
    // No GitHub credentials — save as pending for manual apply
    _saveFix($issueId, $errorTitle, $issueUrl, $targetFrame['relative'], $analysis, $fixedCode, 'pending', 'GitHub not configured — apply fix manually');
    echo json_encode(['status' => 'pending', 'message' => 'Fix generated but GitHub not configured. Review at admin/sentry_fixes.php']);
    exit;
}

$prResult = _createGitHubPR(
    $githubToken,
    $githubRepo,
    $githubBase,
    $targetFrame['relative'],
    $fixedCode,
    $errorTitle,
    $analysis,
    $explanation,
    $issueId,
    $issueUrl
);

$prUrl    = $prResult['pr_url']  ?? '';
$prBranch = $prResult['branch']  ?? '';
$prErr    = $prResult['error']   ?? '';

if ($prUrl !== '') {
    _saveFix($issueId, $errorTitle, $issueUrl, $targetFrame['relative'], $analysis, $fixedCode, 'pr_created', $prUrl);
    echo json_encode(['status' => 'success', 'pr_url' => $prUrl, 'branch' => $prBranch]);
} else {
    _saveFix($issueId, $errorTitle, $issueUrl, $targetFrame['relative'], $analysis, $fixedCode, 'pr_failed', $prErr);
    echo json_encode(['status' => 'pr_failed', 'error' => $prErr]);
}

// ═══════════════════════════════════════════════════════════════════════════════
// Helper Functions
// ═══════════════════════════════════════════════════════════════════════════════

/**
 * Call Anthropic Claude API and return the assistant's text response.
 */
function _callAnthropic(string $apiKey, string $prompt): ?string
{
    $body = json_encode([
        'model'      => 'claude-sonnet-4-6',
        'max_tokens' => 8192,
        'messages'   => [['role' => 'user', 'content' => $prompt]],
    ]);

    $ch = curl_init('https://api.anthropic.com/v1/messages');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $body,
        CURLOPT_TIMEOUT        => 90,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'x-api-key: '           . $apiKey,
            'anthropic-version: 2023-06-01',
        ],
    ]);

    $response = curl_exec($ch);
    $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr  = curl_error($ch);
    curl_close($ch);

    if ($curlErr || $httpCode !== 200 || $response === false) {
        log_error_to_db(
            "Sentry webhook: Anthropic API error HTTP={$httpCode} " . $curlErr,
            'error', 'api/sentry_webhook.php'
        );
        return null;
    }

    $data = json_decode((string)$response, true);
    return (string)($data['content'][0]['text'] ?? '');
}

/**
 * Extract the first ```php ... ``` code block from Claude's response.
 */
function _extractCodeBlock(string $text): ?string
{
    if (preg_match('/```php\s*([\s\S]*?)```/m', $text, $m)) {
        return trim($m[1]);
    }
    // Fallback: plain ``` block
    if (preg_match('/```\s*([\s\S]*?)```/m', $text, $m)) {
        return trim($m[1]);
    }
    return null;
}

/**
 * Extract a named section (### Section\n...) from Claude's response.
 */
function _extractSection(string $text, string $sectionName): string
{
    $pattern = '/###\s+' . preg_quote($sectionName, '/') . '\s*\n([\s\S]*?)(?=###|$)/i';
    if (preg_match($pattern, $text, $m)) {
        // Strip any embedded code fences from the section
        $content = preg_replace('/```[\s\S]*?```/', '', $m[1]);
        return trim((string)$content);
    }
    return '';
}

/**
 * Create a GitHub PR via the REST API.
 * Returns ['pr_url'=>..., 'branch'=>...] on success or ['error'=>...] on failure.
 *
 * @param array<string,mixed> $options
 * @return array<string,string>
 */
function _createGitHubPR(
    string $token,
    string $repo,
    string $base,
    string $filePath,
    string $fixedCode,
    string $errorTitle,
    string $analysis,
    string $explanation,
    string $issueId,
    string $issueUrl
): array {
    $slug = preg_replace('/[^a-z0-9\-]/', '-', strtolower(substr($errorTitle, 0, 40)));
    $branchName = 'sentry-fix/' . date('Ymd-His') . ($issueId !== '' ? '-' . $issueId : '') . '-' . $slug;

    // 1. Get current file SHA on base branch
    $fileInfo = _ghRequest($token, 'GET', "repos/{$repo}/contents/{$filePath}?ref={$base}");
    $fileSha  = (string)($fileInfo['sha'] ?? '');
    if ($fileSha === '') {
        return ['error' => "Cannot get file SHA for {$filePath}"];
    }

    // 2. Get base branch HEAD SHA
    $refInfo = _ghRequest($token, 'GET', "repos/{$repo}/git/ref/heads/{$base}");
    $headSha = (string)($refInfo['object']['sha'] ?? '');
    if ($headSha === '') {
        return ['error' => "Cannot get HEAD SHA for branch {$base}"];
    }

    // 3. Create new branch
    $createRef = _ghRequest($token, 'POST', "repos/{$repo}/git/refs", [
        'ref' => "refs/heads/{$branchName}",
        'sha' => $headSha,
    ]);
    if (isset($createRef['message']) && str_contains((string)$createRef['message'], 'already exists')) {
        return ['error' => "Branch {$branchName} already exists"];
    }

    // 4. Update file on the new branch
    $commitMsg = "fix: auto-fix Sentry issue #{$issueId}\n\n" . substr($errorTitle, 0, 72);
    _ghRequest($token, 'PUT', "repos/{$repo}/contents/{$filePath}", [
        'message' => $commitMsg,
        'content' => base64_encode($fixedCode),
        'sha'     => $fileSha,
        'branch'  => $branchName,
    ]);

    // 5. Create PR
    $prBody = "## Auto-fix by Claude\n\n";
    $prBody .= "> Generated automatically from a Sentry alert. **Review carefully before merging.**\n\n";
    if ($issueUrl !== '') $prBody .= "**Sentry Issue:** {$issueUrl}\n\n";
    $prBody .= "### Root Cause\n{$analysis}\n\n";
    $prBody .= "### What Changed\n{$explanation}\n\n";
    $prBody .= "### Checklist\n- [ ] Reviewed the diff manually\n- [ ] Tested on staging\n- [ ] Sentry issue resolved after deploy";

    $pr = _ghRequest($token, 'POST', "repos/{$repo}/pulls", [
        'title' => "fix: auto-fix Sentry #{$issueId} — " . substr($errorTitle, 0, 60),
        'body'  => $prBody,
        'head'  => $branchName,
        'base'  => $base,
    ]);

    $prUrl = (string)($pr['html_url'] ?? '');
    if ($prUrl === '') {
        $errMsg = (string)($pr['message'] ?? 'Unknown GitHub API error');
        return ['error' => $errMsg];
    }

    return ['pr_url' => $prUrl, 'branch' => $branchName];
}

/**
 * Make a GitHub API request.
 *
 * @param array<string,mixed> $body
 * @return array<string,mixed>
 */
function _ghRequest(string $token, string $method, string $path, array $body = []): array
{
    $url = 'https://api.github.com/' . ltrim($path, '/');
    $ch  = curl_init($url);

    $headers = [
        'Authorization: Bearer ' . $token,
        'Accept: application/vnd.github+json',
        'X-GitHub-Api-Version: 2022-11-28',
        'User-Agent: RSU-HealthHub-SentryBot/1.0',
        'Content-Type: application/json',
    ];

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_HTTPHEADER     => $headers,
    ]);

    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
    } elseif ($method === 'PUT') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
    }

    $response = (string)curl_exec($ch);
    curl_close($ch);

    return (array)(json_decode($response, true) ?? []);
}

/**
 * Persist a fix record to sys_sentry_fixes table (auto-created).
 */
function _saveFix(
    string  $issueId,
    string  $errorTitle,
    string  $issueUrl,
    ?string $filePath,
    ?string $analysis,
    ?string $fixedCode,
    string  $status,
    string  $note
): void {
    try {
        $pdo = db();
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

        $pdo->prepare(
            "INSERT INTO sys_sentry_fixes
             (issue_id, error_title, issue_url, file_path, analysis, fixed_code, status, note)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
        )->execute([
            substr($issueId,    0, 64),
            substr($errorTitle, 0, 500),
            substr($issueUrl,   0, 500),
            substr((string)$filePath,   0, 400),
            (string)$analysis,
            (string)$fixedCode,
            substr($status,     0, 32),
            substr($note,       0, 1000),
        ]);
    } catch (Throwable $e) {
        log_error_to_db('sys_sentry_fixes insert failed: ' . $e->getMessage(), 'error', 'api/sentry_webhook.php');
    }
}
