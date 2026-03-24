<?php
// includes/csrf.php
declare(strict_types=1);

/**
 * Generate a CSRF token and store it in the session if it doesn't exist.
 * @return string
 */
function get_csrf_token(): string {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    
    return $_SESSION['csrf_token'];
}

/**
 * Verify if the provided token matches the one in the session.
 * @param string|null $token
 * @return bool
 */
function verify_csrf_token(?string $token): bool {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (empty($token) || empty($_SESSION['csrf_token'])) {
        return false;
    }
    
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Output a hidden input field with the CSRF token.
 */
function csrf_field(): void {
    echo '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(get_csrf_token()) . '">';
}

/**
 * Helper to die if CSRF verification fails.
 */
function validate_csrf_or_die(): void {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = $_POST['csrf_token'] ?? '';
        if (!verify_csrf_token($token)) {
            header('HTTP/1.1 403 Forbidden');
            die("Error: Invalid CSRF Token. Please refresh the page and try again.");
        }
    }
}
