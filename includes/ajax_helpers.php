<?php
/**
 * includes/ajax_helpers.php
 * Shared JSON response helpers for AJAX endpoints.
 *
 * Usage:
 *   require_once __DIR__ . '/../../includes/ajax_helpers.php';
 *   json_ok(['total' => 5]);
 *   json_err('กรุณาระบุ action');
 */
declare(strict_types=1);

if (!function_exists('json_ok')) {
    /**
     * Send a successful JSON response and exit.
     * Always outputs: {"status":"ok", ...extra}
     */
    function json_ok(array $data = [], int $httpCode = 200): never
    {
        http_response_code($httpCode);
        echo json_encode(['status' => 'ok'] + $data, JSON_UNESCAPED_UNICODE);
        exit;
    }
}

if (!function_exists('json_err')) {
    /**
     * Send an error JSON response and exit.
     * Always outputs: {"status":"error","message":"..."}
     */
    function json_err(string $message, int $httpCode = 200): never
    {
        http_response_code($httpCode);
        echo json_encode(['status' => 'error', 'message' => $message], JSON_UNESCAPED_UNICODE);
        exit;
    }
}
