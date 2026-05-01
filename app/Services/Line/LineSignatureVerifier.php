<?php

namespace App\Services\Line;

use App\Services\IntegrationSettingsService;

class LineSignatureVerifier
{
    public function __construct(private readonly IntegrationSettingsService $settings)
    {
    }

    public function isValid(string $payload, ?string $signature): bool
    {
        $secret = $this->channelSecret();

        if ($secret === '' || blank($signature)) {
            return false;
        }

        $computed = base64_encode(hash_hmac('sha256', $payload, $secret, true));

        return hash_equals($computed, (string) $signature);
    }

    private function channelSecret(): string
    {
        $settings = $this->settings->load();

        return trim((string) ($settings['line_channel_secret'] ?: config('services.line_messaging.channel_secret', '')));
    }
}
