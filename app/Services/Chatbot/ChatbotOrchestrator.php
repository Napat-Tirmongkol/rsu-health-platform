<?php

namespace App\Services\Chatbot;

use App\Enums\ChatIntent;
use App\Models\ChatbotSetting;

class ChatbotOrchestrator
{
    public function __construct(
        private readonly IntentClassifier $classifier,
        private readonly FaqAnswerer $faqAnswerer,
        private readonly SymptomScreener $symptomScreener,
        private readonly ConversationManager $conversations,
    ) {
    }

    public function handle(int $clinicId, string $lineUserId, string $message): array
    {
        $conversation = $this->conversations->findOrCreateConversation($clinicId, $lineUserId);
        $intent = $this->classifier->classify($message);

        if (! $this->withinQuota($clinicId, $lineUserId)) {
            $reply = 'วันนี้คุณใช้งานบอทครบตามโควต้าที่กำหนดแล้ว กรุณาลองใหม่ในวันถัดไป หรือติดต่อเจ้าหน้าที่คลินิกโดยตรงครับ';
            $this->conversations->storeUserMessage($conversation, $message, $intent->value);
            $this->conversations->storeAssistantMessage($conversation, $reply, ChatIntent::FALLBACK->value);

            return ['intent' => ChatIntent::FALLBACK->value, 'reply' => $reply];
        }

        $reply = match ($intent) {
            ChatIntent::GREETING => $this->greetingReply(),
            ChatIntent::FAQ => $this->faqAnswerer->answer($clinicId, $message)
                ?? $this->fallbackReply(),
            ChatIntent::BOOKING => $this->bookingReply(),
            ChatIntent::EMERGENCY => $this->symptomScreener->emergencyResponse(),
            ChatIntent::SYMPTOM => $this->symptomScreener->respond($message),
            ChatIntent::FALLBACK => $this->fallbackReply(),
        };

        $this->conversations->storeUserMessage($conversation, $message, $intent->value);
        $this->conversations->storeAssistantMessage($conversation, $reply, $intent->value);

        return [
            'intent' => $intent->value,
            'reply' => $reply,
        ];
    }

    private function withinQuota(int $clinicId, string $lineUserId): bool
    {
        $setting = ChatbotSetting::withoutGlobalScopes()->firstOrCreate(
            ['clinic_id' => $clinicId],
            [
                'model' => 'gemini-2.5-flash',
                'temperature' => 0.20,
                'daily_quota' => (int) env('CHATBOT_DAILY_QUOTA_PER_USER', 20),
            ]
        );

        return $this->conversations->countUserMessagesToday($clinicId, $lineUserId) < $setting->daily_quota;
    }

    private function greetingReply(): string
    {
        return "สวัสดีครับ ยินดีต้อนรับสู่ผู้ช่วยคลินิก RSU Medical Hub\nคุณสามารถพิมพ์ถามเรื่องเวลาทำการ บริการ ที่ตั้ง เบอร์ติดต่อ หรือพิมพ์คำว่า จองคิว เพื่อรับลิงก์สำหรับจองบริการได้ครับ";
    }

    private function bookingReply(): string
    {
        return 'หากต้องการจองคิว สามารถเข้าใช้งานได้ที่ '.url('/user/booking').' และเข้าสู่ระบบด้วยบัญชีผู้ใช้ของคุณก่อนจองครับ';
    }

    private function fallbackReply(): string
    {
        return "ขออภัยครับ ตอนนี้ผมยังตอบคำถามนี้ได้ไม่ครบถ้วน\nลองถามเรื่องเวลาทำการ บริการ ที่ตั้ง เบอร์ติดต่อ หรือพิมพ์คำว่า จองคิว ได้เลยครับ";
    }
}
