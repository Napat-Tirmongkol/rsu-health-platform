<?php
/**
 * includes/line_helper.php
 * ฟังก์ชันช่วยจัดการเกี่ยวกับ LINE Messaging API
 */
declare(strict_types=1);

/**
 * ตรวจสอบ Signature จาก LINE Webhook
 */
function verify_line_signature(string $payload, string $signature, string $channelSecret): bool {
    if (empty($signature) || empty($payload)) return false;
    $hash = hash_hmac('sha256', $payload, $channelSecret, true);
    return hash_equals(base64_encode($hash), $signature);
}

/**
 * ส่งข้อความ Reply (ใช้ Reply Token)
 */
function send_line_reply(string $replyToken, array $messages, string $accessToken): bool {
    $url = 'https://api.line.me/v2/bot/message/reply';
    $data = [
        'replyToken' => $replyToken,
        'messages'   => $messages
    ];
    return _send_line_curl($url, $data, $accessToken);
}

/**
 * ส่งข้อความ Push (ใช้ User ID)
 */
function send_line_push(string $to, array $messages, string $accessToken): bool {
    $url = 'https://api.line.me/v2/bot/message/push';
    $data = [
        'to'       => $to,
        'messages' => $messages
    ];
    return _send_line_curl($url, $data, $accessToken);
}

/**
 * ฟังก์ชันกลางสำหรับยิง CURL ไปยัง LINE API
 */
function _send_line_curl(string $url, array $data, string $accessToken): bool {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POSTFIELDS     => json_encode($data),
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $accessToken
        ],
        CURLOPT_TIMEOUT        => 10
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode !== 200) {
        $body = $response ?: 'No response';
        $hint = '';
        if ($httpCode === 400) {
            $hint = ' (ผู้รับอาจยังไม่ได้เพิ่ม LINE OA เป็นเพื่อน หรือบล็อก OA แล้ว)';
        } elseif ($httpCode === 401) {
            $hint = ' (Channel Access Token ไม่ถูกต้องหรือหมดอายุ)';
        } elseif ($httpCode === 403) {
            $hint = ' (ไม่มีสิทธิ์ส่งข้อความ — ตรวจสอบสิทธิ์ของ Messaging API Channel)';
        }
        $errorMsg = "LINE API Error ($httpCode)$hint: " . $body;
        error_log($errorMsg);
        if (function_exists('log_error_to_db')) {
            log_error_to_db($errorMsg, 'error', 'line_helper.php', json_encode($data));
        }
        $GLOBALS['LAST_LINE_ERROR'] = $body;
    }
    
    return $httpCode === 200;
}

/**
 * ดึงข้อความตอบกลับล่าสุดจาก LINE API กรณีเกิดข้อผิดพลาด
 */
function get_last_line_error(): string {
    return (string)($GLOBALS['LAST_LINE_ERROR'] ?? '');
}
