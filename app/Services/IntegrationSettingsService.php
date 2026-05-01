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
                'description' => 'กำหนดค่าเซิร์ฟเวอร์อีเมลสำหรับการส่งข้อความแจ้งเตือนและอีเมลของระบบ',
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
                    'line_messaging_enabled' => ['label' => 'Enabled', 'type' => 'toggle', 'rules' => 'nullable|boolean', 'toggle_label' => 'เปิดใช้งาน LINE Messaging API'],
                    'line_channel_id' => ['label' => 'Channel ID', 'type' => 'text', 'rules' => 'nullable|string|max:255'],
                    'line_channel_secret' => ['label' => 'Channel Secret', 'type' => 'password', 'rules' => 'nullable|string|max:255', 'encrypted' => true],
                    'line_channel_access_token' => ['label' => 'Channel Access Token', 'type' => 'password', 'rules' => 'nullable|string|max:4000', 'encrypted' => true, 'textarea' => true],
                    'line_webhook_url' => ['label' => 'Webhook URL', 'type' => 'url', 'rules' => 'nullable|url|max:255'],
                ],
            ],
            'gemini' => [
                'title' => 'Gemini API',
                'description' => 'ใช้สำหรับงาน AI ภายในระบบ เช่น ผู้ช่วยตอบคำถาม สรุปข้อมูล หรือ workflow อัตโนมัติ',
                'fields' => [
                    'gemini_enabled' => ['label' => 'Enabled', 'type' => 'toggle', 'rules' => 'nullable|boolean', 'toggle_label' => 'เปิดใช้งาน Gemini API'],
                    'gemini_api_key' => ['label' => 'API Key', 'type' => 'password', 'rules' => 'nullable|string|max:4000', 'encrypted' => true],
                    'gemini_model' => ['label' => 'Default Model', 'type' => 'text', 'rules' => 'nullable|string|max:255'],
                    'gemini_base_url' => ['label' => 'Base URL', 'type' => 'url', 'rules' => 'nullable|url|max:255'],
                    'gemini_system_prompt' => ['label' => 'Default Instruction', 'type' => 'textarea', 'rules' => 'nullable|string|max:10000'],
                ],
            ],
            'notification_rules_campaign' => [
                'title' => 'Notification Rules · e-Campaign',
                'description' => 'กำหนดได้ว่าเหตุการณ์ใดใน e-Campaign ควรส่งแจ้งเตือนผ่าน LINE หรืออีเมล โดยไม่ต้องแก้โค้ดทุกครั้ง',
                'fields' => [
                    'campaign_booking_submitted_line_enabled' => ['label' => 'ส่งคำขอจองสำเร็จ -> LINE', 'type' => 'toggle', 'rules' => 'nullable|boolean', 'toggle_label' => 'ส่ง LINE เมื่อผู้ใช้จองคิวสำเร็จ'],
                    'campaign_booking_submitted_email_enabled' => ['label' => 'ส่งคำขอจองสำเร็จ -> Email', 'type' => 'toggle', 'rules' => 'nullable|boolean', 'toggle_label' => 'ส่งอีเมลยืนยันเมื่อผู้ใช้จองคิวสำเร็จ'],
                    'campaign_booking_checked_in_line_enabled' => ['label' => 'Check-in สำเร็จ -> LINE', 'type' => 'toggle', 'rules' => 'nullable|boolean', 'toggle_label' => 'ส่ง LINE เมื่อผู้ใช้ check-in สำเร็จ'],
                    'campaign_booking_checked_in_email_enabled' => ['label' => 'Check-in สำเร็จ -> Email', 'type' => 'toggle', 'rules' => 'nullable|boolean', 'toggle_label' => 'ส่งอีเมลยืนยันเมื่อผู้ใช้ check-in สำเร็จ'],
                    'campaign_booking_cancelled_line_enabled' => ['label' => 'การจองถูกยกเลิก -> LINE', 'type' => 'toggle', 'rules' => 'nullable|boolean', 'toggle_label' => 'ส่ง LINE เมื่อมีการยกเลิกการจอง'],
                    'campaign_booking_cancelled_email_enabled' => ['label' => 'การจองถูกยกเลิก -> Email', 'type' => 'toggle', 'rules' => 'nullable|boolean', 'toggle_label' => 'ส่งอีเมลเมื่อมีการยกเลิกการจอง'],
                    'campaign_booking_confirmed_line_enabled' => ['label' => 'การจองได้รับการยืนยัน -> LINE', 'type' => 'toggle', 'rules' => 'nullable|boolean', 'toggle_label' => 'ส่ง LINE เมื่อยืนยันการจอง'],
                    'campaign_booking_confirmed_email_enabled' => ['label' => 'การจองได้รับการยืนยัน -> Email', 'type' => 'toggle', 'rules' => 'nullable|boolean', 'toggle_label' => 'ส่งอีเมลเมื่อยืนยันการจอง'],
                    'campaign_booking_reminder_line_enabled' => ['label' => 'เตือนก่อนวันนัด -> LINE', 'type' => 'toggle', 'rules' => 'nullable|boolean', 'toggle_label' => 'ส่ง LINE เตือนก่อนถึงวันนัด'],
                    'campaign_booking_reminder_email_enabled' => ['label' => 'เตือนก่อนวันนัด -> Email', 'type' => 'toggle', 'rules' => 'nullable|boolean', 'toggle_label' => 'ส่งอีเมลเตือนก่อนถึงวันนัด'],
                ],
            ],
            'notification_rules_borrow' => [
                'title' => 'Notification Rules · e-Borrow',
                'description' => 'กำหนดได้ว่าเหตุการณ์ใดใน e-Borrow ควรส่งแจ้งเตือนผ่าน LINE หรืออีเมล เพื่อให้ทีมคลังควบคุมการสื่อสารได้เอง',
                'fields' => [
                    'borrow_request_approved_line_enabled' => ['label' => 'อนุมัติคำขอยืม -> LINE', 'type' => 'toggle', 'rules' => 'nullable|boolean', 'toggle_label' => 'ส่ง LINE เมื่อคำขอยืมได้รับการอนุมัติ'],
                    'borrow_request_approved_email_enabled' => ['label' => 'อนุมัติคำขอยืม -> Email', 'type' => 'toggle', 'rules' => 'nullable|boolean', 'toggle_label' => 'ส่งอีเมลเมื่อคำขอยืมได้รับการอนุมัติ'],
                    'borrow_overdue_line_enabled' => ['label' => 'เกินกำหนดคืน -> LINE', 'type' => 'toggle', 'rules' => 'nullable|boolean', 'toggle_label' => 'ส่ง LINE เมื่อรายการยืมเกินกำหนดคืน'],
                    'borrow_overdue_email_enabled' => ['label' => 'เกินกำหนดคืน -> Email', 'type' => 'toggle', 'rules' => 'nullable|boolean', 'toggle_label' => 'ส่งอีเมลเมื่อรายการยืมเกินกำหนดคืน'],
                    'borrow_fine_created_line_enabled' => ['label' => 'มีค่าปรับใหม่ -> LINE', 'type' => 'toggle', 'rules' => 'nullable|boolean', 'toggle_label' => 'ส่ง LINE เมื่อมีการสร้างค่าปรับใหม่'],
                    'borrow_fine_created_email_enabled' => ['label' => 'มีค่าปรับใหม่ -> Email', 'type' => 'toggle', 'rules' => 'nullable|boolean', 'toggle_label' => 'ส่งอีเมลเมื่อมีการสร้างค่าปรับใหม่'],
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
                $defaults[$key] = ($field['type'] ?? null) === 'toggle' ? false : '';
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
            $raw = Arr::get($values, $key, ($field['type'] ?? null) === 'toggle' ? false : null);
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

    public function notificationEnabled(string $module, string $event, string $channel): bool
    {
        $key = "{$module}_{$event}_{$channel}_enabled";

        return (bool) Arr::get($this->load(), $key, false);
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
