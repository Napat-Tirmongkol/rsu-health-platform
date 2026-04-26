<?php
/**
 * config/db_connect.php
 * Canonical database connection — single source of truth for all modules.
 * Loads credentials from config/secrets.php.
 */
declare(strict_types=1);

if (!function_exists('db')) {
    function db(): PDO {
        static $pdo = null;
        if ($pdo !== null) return $pdo;

        $secretsPath = __DIR__ . '/secrets.php';
        if (!file_exists($secretsPath)) {
            $secretsPath = __DIR__ . '/secrets.template.php';
        }
        $secrets = require $secretsPath;

        $host = $secrets['DB_HOST'] ?? '127.0.0.1';
        $port = (int)($secrets['DB_PORT'] ?? 3306);
        $user = $secrets['DB_USER'] ?? '';
        $pass = $secrets['DB_PASS'] ?? '';
        $name = $secrets['DB_NAME'] ?? '';

        try {
            $pdo = new PDO(
                "mysql:host=$host;port=$port;dbname=$name;charset=utf8mb4",
                $user, $pass,
                [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]
            );
            return $pdo;
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            if (
                str_contains($_SERVER['REQUEST_URI'] ?? '', '/ajax/') ||
                (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest')
            ) {
                header('Content-Type: application/json');
                http_response_code(500);
                exit(json_encode(['ok' => false, 'error' => 'Database connection failed']));
            }
            http_response_code(503);
            exit("ไม่สามารถเชื่อมต่อฐานข้อมูลได้ กรุณาตรวจสอบการตั้งค่าใน config/secrets.php");
        }
    }
}
