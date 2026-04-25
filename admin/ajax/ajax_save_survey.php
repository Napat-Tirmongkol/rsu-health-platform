<?php
// admin/ajax/ajax_save_survey.php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../config.php';

// Validate input
$rating  = (int)($_POST['rating']  ?? 0);
$comment = trim($_POST['comment']  ?? '');
$context = trim($_POST['context']  ?? '');

if ($rating < 1 || $rating > 5) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'error' => 'invalid_rating']);
    exit;
}

// Sanitise
$comment = mb_substr($comment, 0, 1000);
$context = mb_substr($context, 0, 100);
$ip_hash = hash('sha256', $_SERVER['REMOTE_ADDR'] ?? '');

try {
    $pdo = db();

    // Auto-create table if missing
    $pdo->exec("CREATE TABLE IF NOT EXISTS satisfaction_surveys (
        id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        rating       TINYINT      NOT NULL,
        comment      TEXT,
        page_context VARCHAR(100) DEFAULT NULL,
        ip_hash      VARCHAR(64)  DEFAULT NULL,
        created_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_created (created_at),
        INDEX idx_rating  (rating)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $stmt = $pdo->prepare(
        "INSERT INTO satisfaction_surveys (rating, comment, page_context, ip_hash)
         VALUES (:rating, :comment, :context, :ip)"
    );
    $stmt->execute([
        ':rating'  => $rating,
        ':comment' => $comment !== '' ? $comment : null,
        ':context' => $context !== '' ? $context : null,
        ':ip'      => $ip_hash,
    ]);

    echo json_encode(['ok' => true, 'id' => (int)$pdo->lastInsertId()]);

} catch (PDOException $e) {
    error_log('survey save error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'db_error']);
}
