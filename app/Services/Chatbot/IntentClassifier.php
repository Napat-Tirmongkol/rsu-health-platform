<?php

namespace App\Services\Chatbot;

use App\Enums\ChatIntent;

class IntentClassifier
{
    public function classify(string $message): ChatIntent
    {
        $normalized = mb_strtolower(trim($message));

        if ($normalized === '') {
            return ChatIntent::FALLBACK;
        }

        if ($this->containsAny($normalized, ['เจ็บหน้าอก', 'หายใจไม่ออก', 'หมดสติ', 'ชัก', 'เลือดออกมาก', 'แน่นหน้าอก'])) {
            return ChatIntent::EMERGENCY;
        }

        if ($this->containsAny($normalized, ['สวัสดี', 'หวัดดี', 'hello', 'hi', 'hey'])) {
            return ChatIntent::GREETING;
        }

        if ($this->containsAny($normalized, ['จอง', 'นัด', 'booking', 'appointment', 'คิว'])) {
            return ChatIntent::BOOKING;
        }

        if ($this->containsAny($normalized, ['ไข้', 'ไอ', 'เจ็บคอ', 'ปวดหัว', 'เวียนหัว', 'ท้องเสีย', 'คลื่นไส้', 'ปวดท้อง', 'ผื่น'])) {
            return ChatIntent::SYMPTOM;
        }

        if ($this->containsAny($normalized, ['เปิด', 'ปิด', 'เวลา', 'กี่โมง', 'บริการ', 'ที่ไหน', 'อยู่ตรงไหน', 'เบอร์', 'โทร', 'ติดต่อ', 'ค่าใช้จ่าย', 'ราคา'])) {
            return ChatIntent::FAQ;
        }

        return ChatIntent::FALLBACK;
    }

    private function containsAny(string $haystack, array $needles): bool
    {
        foreach ($needles as $needle) {
            if (str_contains($haystack, $needle)) {
                return true;
            }
        }

        return false;
    }
}
