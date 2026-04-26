<?php
/**
 * user/consent.php — ยุบรวมเข้ากับ profile.php แล้ว
 * ไฟล์นี้คง redirect ไว้เพื่อรองรับ link เก่าที่อาจยังมีอยู่
 */
declare(strict_types=1);
header('Location: profile.php', true, 301); // 301 = Permanent redirect
exit;