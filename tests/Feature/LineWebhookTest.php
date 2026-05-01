<?php

namespace Tests\Feature;

use App\Jobs\ProcessLineMessage;
use App\Models\ChatbotConversation;
use App\Models\ChatbotFaq;
use App\Models\ChatbotMessage;
use App\Models\Clinic;
use App\Models\SiteSetting;
use App\Services\Chatbot\ChatbotOrchestrator;
use App\Services\Line\LineMessagingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class LineWebhookTest extends TestCase
{
    use RefreshDatabase;

    public function test_line_webhook_rejects_invalid_signature(): void
    {
        Clinic::create([
            'name' => 'RSU Medical Clinic',
            'slug' => 'medical',
            'code' => 'RSU-MED',
            'status' => 'active',
        ]);

        $payload = [
            'events' => [
                [
                    'type' => 'message',
                    'replyToken' => 'reply-token',
                    'source' => ['userId' => 'U123'],
                    'message' => ['type' => 'text', 'text' => 'สวัสดี'],
                ],
            ],
        ];

        $this->postJson(route('api.line.webhook'), $payload, [
            'X-Line-Signature' => 'invalid',
        ])->assertStatus(401);
    }

    public function test_line_webhook_dispatches_message_job_when_signature_is_valid(): void
    {
        Queue::fake();

        Clinic::create([
            'name' => 'RSU Medical Clinic',
            'slug' => 'medical',
            'code' => 'RSU-MED',
            'status' => 'active',
        ]);

        SiteSetting::withoutGlobalScopes()->create([
            'clinic_id' => 1,
            'key' => 'line_channel_secret',
            'value' => Crypt::encryptString('test-secret'),
            'type' => 'encrypted',
        ]);

        $payload = json_encode([
            'events' => [
                [
                    'type' => 'message',
                    'replyToken' => 'reply-token',
                    'source' => ['userId' => 'U123'],
                    'message' => ['type' => 'text', 'text' => 'สวัสดี'],
                ],
            ],
        ], JSON_UNESCAPED_UNICODE);

        $signature = base64_encode(hash_hmac('sha256', $payload, 'test-secret', true));

        $this->call('POST', route('api.line.webhook'), [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_LINE_SIGNATURE' => $signature,
        ], $payload)->assertOk();

        Queue::assertPushed(ProcessLineMessage::class, function (ProcessLineMessage $job) {
            return $job->clinicId === 1
                && $job->lineUserId === 'U123'
                && $job->replyToken === 'reply-token'
                && $job->messageText === 'สวัสดี';
        });
    }

    public function test_process_line_message_job_replies_and_logs_conversation(): void
    {
        Clinic::create([
            'name' => 'RSU Medical Clinic',
            'slug' => 'medical',
            'code' => 'RSU-MED',
            'status' => 'active',
        ]);

        SiteSetting::withoutGlobalScopes()->create([
            'clinic_id' => 1,
            'key' => 'line_channel_access_token',
            'value' => Crypt::encryptString('line-token'),
            'type' => 'encrypted',
        ]);

        ChatbotFaq::create([
            'clinic_id' => 1,
            'question' => 'เวลาทำการ',
            'answer' => 'คลินิกเปิดวันจันทร์-ศุกร์ 08:00-20:00 และเสาร์-อาทิตย์ 08:00-12:00',
            'keywords' => ['เวลา', 'เปิด', 'ปิด'],
            'is_active' => true,
        ]);

        Http::fake([
            'https://api.line.me/v2/bot/message/reply' => Http::response(['ok' => true], 200),
        ]);

        $job = new ProcessLineMessage(
            clinicId: 1,
            lineUserId: 'UFAQ001',
            replyToken: 'reply-token',
            messageText: 'คลินิกเปิดกี่โมง',
        );

        $job->handle(app(ChatbotOrchestrator::class), app(LineMessagingService::class));

        $conversation = ChatbotConversation::withoutGlobalScopes()->first();

        $this->assertNotNull($conversation);
        $this->assertSame('UFAQ001', $conversation->line_user_id);

        $this->assertSame(2, ChatbotMessage::withoutGlobalScopes()->count());

        Http::assertSent(function ($request) {
            $data = $request->data();

            return $request->url() === 'https://api.line.me/v2/bot/message/reply'
                && $data['replyToken'] === 'reply-token'
                && str_contains($data['messages'][0]['text'], '08:00-20:00');
        });
    }
}
