<?php

namespace App\Services;

use App\Models\User;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use InvalidArgumentException;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use JsonException;

class IdentityQrCode
{
    public function payload(User $user): string
    {
        $identity = $user->resolveIdentity();

        $data = [
            'type' => 'rsu_health_identity',
            'version' => 1,
            'clinic_id' => (int) $user->clinic_id,
            'user_id' => (int) $user->id,
            'person_type' => $user->status === 'other' ? 'general' : ($user->status ?: 'general'),
            'identity_type' => $identity['type'],
        ];

        return json_encode([
            'data' => $data,
            'signature' => $this->sign($data),
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

    public function decode(string $payload): array
    {
        try {
            $decoded = json_decode($payload, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new InvalidArgumentException('Invalid QR payload.');
        }

        if (! is_array($decoded) || ! isset($decoded['data'], $decoded['signature']) || ! is_array($decoded['data'])) {
            throw new InvalidArgumentException('Invalid QR payload.');
        }

        $data = $decoded['data'];
        $signature = (string) $decoded['signature'];

        foreach (['type', 'version', 'clinic_id', 'user_id', 'person_type', 'identity_type'] as $requiredField) {
            if (! array_key_exists($requiredField, $data)) {
                throw new InvalidArgumentException('Incomplete QR payload.');
            }
        }

        if ($data['type'] !== 'rsu_health_identity') {
            throw new InvalidArgumentException('Unsupported QR type.');
        }

        if (! hash_equals($this->sign($data), $signature)) {
            throw new InvalidArgumentException('Invalid QR signature.');
        }

        return $data;
    }

    public function verifyUser(string $payload): User
    {
        $data = $this->decode($payload);

        $user = User::with('primaryIdentity')->find($data['user_id']);

        if (! $user) {
            throw new InvalidArgumentException('User not found.');
        }

        if ((int) $user->clinic_id !== (int) $data['clinic_id']) {
            throw new InvalidArgumentException('Clinic mismatch.');
        }

        $identity = $user->resolveIdentity();
        $personType = $user->status === 'other' ? 'general' : ($user->status ?: 'general');

        if (($identity['type'] ?? null) !== $data['identity_type']) {
            throw new InvalidArgumentException('Identity mismatch.');
        }

        if ($personType !== $data['person_type']) {
            throw new InvalidArgumentException('Person type mismatch.');
        }

        return $user;
    }

    public function sign(array $data): string
    {
        $encodedData = base64_encode(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        return hash_hmac('sha256', $encodedData, $this->signingKey());
    }

    private function signingKey(): string
    {
        $key = (string) Config::get('app.key');

        return Str::startsWith($key, 'base64:')
            ? base64_decode(Str::after($key, 'base64:'), true) ?: $key
            : $key;
    }
}
