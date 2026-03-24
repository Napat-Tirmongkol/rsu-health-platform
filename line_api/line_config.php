<?php
// line_api/line_config.php
declare(strict_types=1);

/**
 * ==========================================
 *  LINE Developers Console — Credentials
 *  https://developers.line.biz/console/
 * ==========================================
 *
 * [วิธีหาค่าต่างๆ]
 *  LINE_LOGIN_CHANNEL_ID     -> LINE Login Channel > Basic settings > Channel ID
 *  LINE_LOGIN_CHANNEL_SECRET -> LINE Login Channel > Basic settings > Channel secret
 *  LINE_LOGIN_CALLBACK_URL   -> ต้องตรงกับ Callback URL ใน LINE Login Channel > LINE Login tab
 *
 *  LINE_MESSAGING_CHANNEL_ACCESS_TOKEN -> Messaging API Channel > Messaging API tab > Channel access token
 *  LINE_MESSAGING_CHANNEL_SECRET       -> Messaging API Channel > Basic settings > Channel secret
 *
 *  LINE_LIFF_ID -> LINE Login Channel > LIFF tab > LIFF ID (format: XXXXXXXXXX-XXXXXXXX)
 */

// ============================================================
// 1. LINE Login Channel (ระบบ Login และ Callback)
// ============================================================
define('LINE_LOGIN_CHANNEL_ID',     '2009560824');
define('LINE_LOGIN_CHANNEL_SECRET', '50870e1645c14089b28897caaf3a1a84');

// ⚠️ ต้องตรงกับ Callback URL ที่ลงทะเบียนใน LINE Developers Console เป๊ะๆ
define('LINE_LOGIN_CALLBACK_URL', 'https://healthycampus.rsu.ac.th/e-campaign/campaignv2/line_api/callback.php');

// ============================================================
// 2. LINE Messaging API Channel (Webhook, Push, Reply)
// ============================================================
define('LINE_MESSAGING_CHANNEL_ACCESS_TOKEN', 'YOUR_CHANNEL_ACCESS_TOKEN');
define('LINE_MESSAGING_CHANNEL_SECRET',       'YOUR_MESSAGING_CHANNEL_SECRET');

// ============================================================
// 3. LIFF (LINE Front-end Framework) — ถ้าใช้ LIFF ควบคู่
// ============================================================
define('LINE_LIFF_ID', 'YOUR_LIFF_ID'); // เช่น 2008476166-FBKVySKi
