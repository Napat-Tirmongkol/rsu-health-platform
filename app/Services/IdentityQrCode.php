<?php

namespace App\Services;

use App\Models\User;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;

class IdentityQrCode
{
    public function payload(User $user): string
    {
        $data = [
            'type' => 'rsu_health_identity',
            'version' => 1,
            'clinic_id' => (int) $user->clinic_id,
            'user_id' => (int) $user->id,
            'student_personnel_id' => $user->student_personnel_id,
        ];

        $encodedData = base64_encode(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        return json_encode([
            'data' => $data,
            'signature' => hash_hmac('sha256', $encodedData, $this->signingKey()),
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    public function svg(User $user, int $size = 180): string
    {
        $renderer = new ImageRenderer(
            new RendererStyle($size),
            new SvgImageBackEnd()
        );

        return preg_replace('/<\?xml.*?\?>\s*/', '', (new Writer($renderer))->writeString($this->payload($user))) ?? '';
    }

    private function signingKey(): string
    {
        $key = (string) Config::get('app.key');

        return Str::startsWith($key, 'base64:')
            ? base64_decode(Str::after($key, 'base64:'), true) ?: $key
            : $key;
    }
}
