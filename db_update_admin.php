<?php
require_once __DIR__ . '/config/db_connect.php';
try {
    $pdo = db();
    
    // 1. เพิ่มฟีลด์ password และ username ถ้าไม่มี
    $pdo->exec("ALTER TABLE sys_admins ADD COLUMN username VARCHAR(50) UNIQUE AFTER full_name");
    $pdo->exec("ALTER TABLE sys_admins ADD COLUMN password VARCHAR(255) AFTER email");
    
    echo "Table 'sys_admins' updated successfully.\n";

    // 2. ล็อกอินเริ่มแรก
    $plainPass = '1234';
    $hashedPass = password_hash($plainPass, PASSWORD_DEFAULT);
    $check = $pdo->query("SELECT COUNT(*) FROM sys_admins")->fetchColumn();
    if ($check == 0) {
        $stmt = $pdo->prepare("INSERT INTO sys_admins (full_name, username, password, email, role) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute(['Administrator', 'admin', $hashedPass, 'admin@example.com', 'admin']);
        echo "Default admin created: admin / 1234\n";
    }

} catch (PDOException $e) {
    echo "Status: " . $e->getMessage() . "\n";
}

