<?php
// portal/ajax_support_chat.php — Staff Chat Controller
declare(strict_types=1);
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/includes/auth.php'; // Ensure staff auth

header('Content-Type: application/json');

$staffId = $_SESSION['admin_id'] ?? null;
if (!$staffId) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$action = $_GET['action'] ?? 'list_users';
$pdo = db();

try {
    if ($action === 'list_users') {
        // Get list of users with their latest message
        $stmt = $pdo->query("
            SELECT u.id, u.full_name, u.picture_url, m.message as last_message, m.created_at,
                   (SELECT COUNT(*) FROM sys_chat_messages WHERE user_id = u.id AND is_read = 0 AND sender_type = 'user') as unread_count
            FROM sys_users u
            JOIN (
                SELECT user_id, MAX(id) as max_id
                FROM sys_chat_messages
                GROUP BY user_id
            ) latest ON u.id = latest.user_id
            JOIN sys_chat_messages m ON latest.max_id = m.id
            ORDER BY m.created_at DESC
        ");
        $users = $stmt->fetchAll();
        echo json_encode(['success' => true, 'users' => $users]);
        exit;
    }

    if ($action === 'get_messages') {
        $targetUserId = (int)($_GET['user_id'] ?? 0);
        if (!$targetUserId) exit;

        // Mark as read
        $pdo->prepare("UPDATE sys_chat_messages SET is_read = 1 WHERE user_id = :uid AND sender_type = 'user'")->execute([':uid' => $targetUserId]);

        $stmt = $pdo->prepare("
            SELECT m.*, a.full_name as staff_name 
            FROM sys_chat_messages m
            LEFT JOIN sys_admins a ON m.staff_id = a.id
            WHERE m.user_id = :uid 
            ORDER BY m.id ASC
        ");
        $stmt->execute([':uid' => $targetUserId]);
        $messages = $stmt->fetchAll();

        foreach ($messages as &$m) {
            $m['time'] = date('H:i', strtotime($m['created_at']));
        }

        echo json_encode(['success' => true, 'messages' => $messages]);
        exit;
    }

    if ($action === 'send_reply') {
        $targetUserId = (int)($_POST['user_id'] ?? 0);
        $message = trim($_POST['message'] ?? '');

        if (!$targetUserId || empty($message)) {
            echo json_encode(['success' => false, 'error' => 'Invalid data']);
            exit;
        }

        $stmt = $pdo->prepare("INSERT INTO sys_chat_messages (sender_type, user_id, staff_id, message) VALUES ('staff', :uid, :sid, :msg)");
        $stmt->execute([':uid' => $targetUserId, ':sid' => $staffId, ':msg' => $message]);

        echo json_encode(['success' => true]);
        exit;
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
