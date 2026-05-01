<?php

namespace App\Services\Line;

use App\Services\IntegrationSettingsService;
use Illuminate\Support\Facades\Http;

class LineMessagingService
{
    public function __construct(private readonly IntegrationSettingsService $settings)
    {
    }

    public function replyText(string $replyToken, string $message): array
    {
        $response = Http::withToken($this->channelAccessToken())
            ->acceptJson()
            ->post('https://api.line.me/v2/bot/message/reply', [
                'replyToken' => $replyToken,
                'messages' => [
                    [
                        'type' => 'text',
                        'text' => mb_substr($message, 0, 4900),
                    ],
                ],
            ]);

        if ($response->failed()) {
            throw new \RuntimeException('LINE reply failed: '.$response->body());
        }

        return $response->json() ?? ['ok' => true];
    }

    private function channelAccessToken(): string
    {
        $settings = $this->settings->load();
        $token = trim((string) ($settings['line_channel_access_token'] ?: config('services.line_messaging.channel_access_token', '')));

        if ($token === '') {
            throw new \RuntimeException('LINE channel access token is not configured');
        }

        return $token;
    }
}
