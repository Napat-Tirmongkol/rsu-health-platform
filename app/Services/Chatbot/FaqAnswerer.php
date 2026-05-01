<?php

namespace App\Services\Chatbot;

use App\Models\ChatbotFaq;
use App\Models\SiteSetting;

class FaqAnswerer
{
    public function answer(int $clinicId, string $message): ?string
    {
        $normalized = mb_strtolower(trim($message));
        $faqAnswer = $this->answerFromFaq($clinicId, $normalized);

        if ($faqAnswer !== null) {
            return $faqAnswer;
        }

        $settings = $this->siteSettings();

        if ($this->containsAny($normalized, ['เปิด', 'ปิด', 'เวลา', 'กี่โมง'])) {
            return 'เวลาทำการของคลินิก: '.$settings['hours'];
        }

        if ($this->containsAny($normalized, ['เบอร์', 'โทร', 'ติดต่อ'])) {
            return 'ช่องทางติดต่อคลินิก: '.$settings['phone'];
        }

        if ($this->containsAny($normalized, ['ที่ไหน', 'อยู่ตรงไหน', 'location', 'แผนที่'])) {
            return 'ที่ตั้งคลินิก: '.$settings['location'];
        }

        if ($this->containsAny($normalized, ['บริการ', 'รักษา', 'service'])) {
            return 'บริการของคลินิก: '.$settings['services'];
        }

        if ($this->containsAny($normalized, ['ค่าใช้จ่าย', 'ราคา', 'ค่ารักษา'])) {
            return 'เรื่องค่าใช้จ่ายขึ้นอยู่กับบริการที่ต้องการ แนะนำติดต่อคลินิกโดยตรงที่ '.$settings['phone'].' เพื่อรับข้อมูลล่าสุดครับ';
        }

        return null;
    }

    private function answerFromFaq(int $clinicId, string $message): ?string
    {
        $faqs = ChatbotFaq::withoutGlobalScopes()
            ->where('clinic_id', $clinicId)
            ->where('is_active', true)
            ->get();

        foreach ($faqs as $faq) {
            $keywords = collect($faq->keywords ?? [])
                ->filter()
                ->map(fn ($keyword) => mb_strtolower((string) $keyword))
                ->values();

            if ($keywords->contains(fn ($keyword) => str_contains($message, $keyword))) {
                return $faq->answer;
            }

            if (str_contains($message, mb_strtolower($faq->question))) {
                return $faq->answer;
            }
        }

        return null;
    }

    private function siteSettings(): array
    {
        $pairs = SiteSetting::query()
            ->whereIn('key', ['clinic_hours', 'clinic_phone', 'clinic_location', 'clinic_services'])
            ->pluck('value', 'key');

        return [
            'hours' => (string) ($pairs['clinic_hours'] ?? 'วันจันทร์-ศุกร์ 08:00-20:00 และเสาร์-อาทิตย์ 08:00-12:00'),
            'phone' => (string) ($pairs['clinic_phone'] ?? '02-791-6000 ต่อ 4499'),
            'location' => (string) ($pairs['clinic_location'] ?? 'อาคาร 12/1 มหาวิทยาลัยรังสิต'),
            'services' => (string) ($pairs['clinic_services'] ?? 'ตรวจรักษาเบื้องต้น วัคซีน ให้คำปรึกษา และบริการสุขภาพนักศึกษา'),
        ];
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
