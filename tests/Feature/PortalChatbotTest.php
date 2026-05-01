<?php

namespace Tests\Feature;

use App\Models\ChatbotFaq;
use App\Models\ChatbotSetting;
use App\Models\Clinic;
use App\Models\Portal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PortalChatbotTest extends TestCase
{
    use RefreshDatabase;

    public function test_portal_chatbot_pages_render(): void
    {
        $clinic = Clinic::create([
            'name' => 'RSU Medical Clinic',
            'slug' => 'medical',
            'code' => 'RSU-MED',
            'status' => 'active',
        ]);

        $portal = Portal::create([
            'name' => 'Portal Admin',
            'email' => 'portal-chatbot@example.com',
            'password' => Hash::make('password'),
        ]);

        ChatbotFaq::withoutGlobalScopes()->create([
            'clinic_id' => $clinic->id,
            'question' => 'เปิดกี่โมง',
            'answer' => 'เปิดวันจันทร์ถึงศุกร์',
            'keywords' => ['เวลาเปิด'],
            'is_active' => true,
        ]);

        $this->actingAs($portal, 'portal')
            ->get(route('portal.chatbot.faqs', ['clinic_id' => $clinic->id]))
            ->assertOk()
            ->assertSee('FAQ Manager')
            ->assertSee('เปิดกี่โมง');

        $this->actingAs($portal, 'portal')
            ->get(route('portal.chatbot.settings', ['clinic_id' => $clinic->id]))
            ->assertOk()
            ->assertSee('Chatbot Settings')
            ->assertSee($clinic->name);
    }

    public function test_portal_can_create_chatbot_faq(): void
    {
        $clinic = Clinic::create([
            'name' => 'RSU Medical Clinic',
            'slug' => 'medical',
            'code' => 'RSU-MED',
            'status' => 'active',
        ]);

        $portal = Portal::create([
            'name' => 'Portal Admin',
            'email' => 'portal-create-faq@example.com',
            'password' => Hash::make('password'),
        ]);

        $response = $this->actingAs($portal, 'portal')->post(route('portal.chatbot.faqs.store'), [
            'clinic_id' => $clinic->id,
            'question' => 'ติดต่อคลินิกอย่างไร',
            'answer' => 'โทร 02-791-6000 ต่อ 4499',
            'keywords' => 'โทร, ติดต่อ, contact',
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('portal.chatbot.faqs', ['clinic_id' => $clinic->id]));
        $response->assertSessionHas('message', 'เพิ่ม FAQ เรียบร้อยแล้ว');

        $this->assertDatabaseHas('chatbot_faqs', [
            'clinic_id' => $clinic->id,
            'question' => 'ติดต่อคลินิกอย่างไร',
            'is_active' => 1,
        ]);
    }

    public function test_portal_can_update_chatbot_faq(): void
    {
        $clinic = Clinic::create([
            'name' => 'RSU Medical Clinic',
            'slug' => 'medical',
            'code' => 'RSU-MED',
            'status' => 'active',
        ]);

        $portal = Portal::create([
            'name' => 'Portal Admin',
            'email' => 'portal-update-faq@example.com',
            'password' => Hash::make('password'),
        ]);

        $faq = ChatbotFaq::withoutGlobalScopes()->create([
            'clinic_id' => $clinic->id,
            'question' => 'จองได้ไหม',
            'answer' => 'จองผ่านหน้าเว็บไซต์ได้',
            'keywords' => ['จอง'],
            'is_active' => true,
        ]);

        $response = $this->actingAs($portal, 'portal')->put(route('portal.chatbot.faqs.update', $faq->id), [
            'clinic_id' => $clinic->id,
            'question' => 'จองได้ทางไหน',
            'answer' => 'จองผ่านหน้าเว็บหรือ LINE ได้',
            'keywords' => 'จอง, booking, line',
        ]);

        $response->assertRedirect(route('portal.chatbot.faqs', ['clinic_id' => $clinic->id]));
        $response->assertSessionHas('message', 'อัปเดต FAQ เรียบร้อยแล้ว');

        $faq->refresh();

        $this->assertSame('จองได้ทางไหน', $faq->question);
        $this->assertSame('จองผ่านหน้าเว็บหรือ LINE ได้', $faq->answer);
        $this->assertSame(['จอง', 'booking', 'line'], $faq->keywords);
    }

    public function test_portal_can_update_chatbot_settings_per_clinic(): void
    {
        $clinic = Clinic::create([
            'name' => 'RSU Medical Clinic',
            'slug' => 'medical',
            'code' => 'RSU-MED',
            'status' => 'active',
        ]);

        $portal = Portal::create([
            'name' => 'Portal Admin',
            'email' => 'portal-settings@example.com',
            'password' => Hash::make('password'),
        ]);

        $response = $this->actingAs($portal, 'portal')->post(route('portal.chatbot.settings.update'), [
            'clinic_id' => $clinic->id,
            'model' => 'gemini-2.5-flash',
            'temperature' => '0.35',
            'daily_quota' => '40',
            'system_prompt' => 'You are the clinic assistant.',
        ]);

        $response->assertRedirect(route('portal.chatbot.settings', ['clinic_id' => $clinic->id]));
        $response->assertSessionHas('message', 'บันทึกการตั้งค่า Chatbot เรียบร้อยแล้ว');

        $setting = ChatbotSetting::withoutGlobalScopes()
            ->where('clinic_id', $clinic->id)
            ->first();

        $this->assertNotNull($setting);
        $this->assertSame('gemini-2.5-flash', $setting->model);
        $this->assertSame(0.35, (float) $setting->temperature);
        $this->assertSame(40, $setting->daily_quota);
        $this->assertSame('You are the clinic assistant.', $setting->system_prompt);
    }
}
