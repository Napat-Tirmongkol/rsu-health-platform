<?php
/**
 * Migration: Add gender column to sys_users
 * Run once: php database/migrate_add_gender.php
 */
require_once __DIR__ . '/../config.php';

$pdo = db();

// Check if column already exists
$cols = $pdo->query("SHOW COLUMNS FROM sys_users LIKE 'gender'")->fetchAll();
if (!empty($cols)) {
    echo "Column 'gender' already exists. Nothing to do.\n";
    exit(0);
}

$pdo->exec("ALTER TABLE sys_users ADD COLUMN gender ENUM('male','female','other') NULL DEFAULT NULL AFTER status");
echo "Migration complete: 'gender' column added to sys_users.\n";
