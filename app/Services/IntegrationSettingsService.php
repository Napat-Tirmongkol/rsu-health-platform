<?php

namespace App\Services;

use App\Models\SiteSetting;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Crypt;

class IntegrationSettingsService
{
    public function sections(): array
    {
        return [
            'smtp' => [
                'title' => 'Email / SMTP',
                'description' => 'กำหนดค่าเซิร์ฟเวอร์อีเมลสำหรับการส่งข้อความแจ้งเตือนและงานอีเมลของระบบ',
                'fields' => [
                    'mail_mailer' => ['label' => 'Mailer', 'type' => 'select', 'options' => ['smtp' => 'SMTP', 'sendmail' => 'Sendmail', 'log' => 'Log'], 'rules' => 'required|in:smtp,sendmail,log'],
                    'mail_host' => ['label' => 'Host', 'type' => 'text', 'rules' => 'nullable|string|max:255'],
                    'mail_port' => ['label' => 'Port', 'type' => 'number', 'rules' => 'nullable|integer|min:1|max:65535'],
                    'mail_username' => ['label' => 'Username', 'type' => 'text', 'rules' => 'nullable|string|max:255'],
                    'mail_password' => ['label' => 'Password', 'type' => 'password', 'rules' => 'nullable|string|max:255', 'encrypted' => true],
                    'mail_from_address' => ['label' => 'From Address', 'type' => 'email', 'rules' => 'nullable|email|max:255'],
                    'mail_from_name' => ['label' => 'From Name', 'type' => 'text', 'rules' => 'nullable|string|max:255'],
                    'mail_scheme' => ['label' => 'Scheme', 'type' => 'select', 'options' => ['' => 'None', 'tls' => 'TLS', 'ssl' => 'SSL'], 'rules' => 'nullable|in:,tls,ssl'],
                ],
            ],
            'line_messaging' => [
                'title' => 'LINE Messaging API',
                'description' => 'ใช้สำหรับส่งข้อความแจ้งเตือนผ่าน LINE Official Account และเชื่อม webhook ของระบบ',
                'fields' => [
                    'line_messaging_enabled' => ['label' => 'Enabled', 'type' => 'toggle', 'rules' => 'nullable|boolean'],
                    'line_channel_id' => ['label' => 'Channel ID', 'type' => 'text', 'rules' => 'nullable|string|max:255'],
                    'line_channel_secret' => ['label' => 'Channel Secret', 'type' => 'password', 'rules' => 'nullable|string|max:255', 'encrypted' => true],
                    'line_channel_access_token' => ['label' => 'Channel Access Token', 'type' => 'password', 'rules' => 'nullable|string|max:4000', 'encrypted' => true, 'textarea' => true],
                    'line_webhook_url' => ['label' => 'Webhook URL', 'type' => 'url', 'rules' => 'nullable|url|max:255'],
                ],
            ],
            'gemini' => [
                'title' => 'Gemini API',
                'description' => 'ใช้สำหรับงาน AI ภายในระบบ เช่นผู้ช่วยตอบคำถาม สรุปข้อมูล หรือ workflow อัตโนมัติ',
                'fields' => [
                    'gemini_enabled' => ['label' => 'Enabled', 'type' => 'toggle', 'rules' => 'nullable|boolean'],
                    'gemini_api_key' => ['label' => 'API Key', 'type' => 'password', 'rules' => 'nullable|string|max:4000', 'encrypted' => true],
                    'gemini_model' => ['label' => 'Default Model', 'type' => 'text', 'rules' => 'nullable|string|max:255'],
                    'gemini_base_url' => ['label' => 'Base URL', 'type' => 'url', 'rules' => 'nullable|url|max:255'],
                    'gemini_system_prompt' => ['label' => 'Default Instruction', 'type' => 'textarea', 'rules' => 'nullable|string|max:10000'],
                ],
            ],
        ];
    }

    public function rules(): array
    {
        $rules = [];

        foreach ($this->sections() as $section) {
            foreach ($section['fields'] as $key => $field) {
                $rules["settings.{$key}"] = $field['rules'] ?? 'nullable';
            }
        }

        return $rules;
    }

    public function defaults(): array
    {
        $defaults = [];

        foreach ($this->sections() as $section) {
            foreach ($section['fields'] as $key => $field) {
                $defaults[$key] = $field['type'] === 'toggle' ? false : '';
            }
        }

        return $defaults;
    }

    public function load(): array
    {
        $definitions = $this->fieldDefinitions();
        $settings = $this->defaults();

        $stored = SiteSetting::query()
            ->whereIn('key', array_keys($definitions))
            ->get()
            ->keyBy('key');

        foreach ($definitions as $key => $field) {
            $setting = $stored->get($key);

            if (! $setting) {
                continue;
            }

            $settings[$key] = $this->decodeValue($setting->value, $field, $setting->type);
        }

        return $settings;
    }

    public function save(array $values): void
    {
        $definitions = $this->fieldDefinitions();

        foreach ($definitions as $key => $field) {
            $raw = Arr::get($values, $key, $field['type'] === 'toggle' ? false : null);
            $encoded = $this->encodeValue($raw, $field);

            SiteSetting::query()->updateOrCreate(
                ['key' => $key],
                [
                    'value' => $encoded['value'],
                    'type' => $encoded['type'],
                ]
            );
        }
    }

    private function fieldDefinitions(): array
    {
        $definitions = [];

        foreach ($this->sections() as $section) {
            foreach ($section['fields'] as $key => $field) {
                $definitions[$key] = $field;
            }
        }

        return $definitions;
    }

    private function encodeValue(mixed $value, array $field): array
    {
        if (($field['type'] ?? null) === 'toggle') {
            return [
                'type' => 'boolean',
                'value' => $value ? '1' : '0',
            ];
        }

        if (($field['encrypted'] ?? false) === true) {
            return [
                'type' => 'encrypted',
                'value' => blank($value) ? null : Crypt::encryptString((string) $value),
            ];
        }

        return [
            'type' => 'string',
            'value' => blank($value) ? null : (string) $value,
        ];
    }

    private function decodeValue(?string $value, array $field, string $storedType): mixed
    {
        if (($field['type'] ?? null) === 'toggle') {
            return in_array($value, ['1', 'true', 'on'], true);
        }

        if (($field['encrypted'] ?? false) === true || $storedType === 'encrypted') {
            if (blank($value)) {
                return '';
            }

            try {
                return Crypt::decryptString($value);
            } catch (\Throwable) {
                return '';
            }
        }

        return $value ?? '';
    }
}
