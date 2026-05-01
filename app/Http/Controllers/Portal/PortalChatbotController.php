<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\ChatbotFaq;
use App\Models\ChatbotSetting;
use App\Models\Clinic;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class PortalChatbotController extends Controller
{
    public function faqs(Request $request)
    {
        $clinics = Clinic::query()->orderBy('name')->get(['id', 'name']);
        $selectedClinic = $this->resolveClinic($request, $clinics);
        $search = trim((string) $request->query('search', ''));
        $editingFaq = null;

        if ($request->filled('edit')) {
            $editingFaq = ChatbotFaq::withoutGlobalScopes()
                ->where('clinic_id', $selectedClinic->id)
                ->findOrFail((int) $request->query('edit'));
        }

        $faqs = ChatbotFaq::withoutGlobalScopes()
            ->where('clinic_id', $selectedClinic->id)
            ->when($search !== '', function ($query) use ($search) {
                $term = '%'.$search.'%';

                return $query->where(function ($builder) use ($term) {
                    $builder
                        ->where('question', 'like', $term)
                        ->orWhere('answer', 'like', $term);
                });
            })
            ->orderByDesc('updated_at')
            ->paginate(20)
            ->withQueryString();

        return view('portal.chatbot.faqs', [
            'clinics' => $clinics,
            'selectedClinic' => $selectedClinic,
            'faqs' => $faqs,
            'editingFaq' => $editingFaq,
            'search' => $search,
        ]);
    }

    public function storeFaq(Request $request)
    {
        $payload = $request->validate([
            'clinic_id' => ['required', 'integer', 'exists:sys_clinics,id'],
            'question' => ['required', 'string', 'max:255'],
            'answer' => ['required', 'string', 'max:5000'],
            'keywords' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        ChatbotFaq::withoutGlobalScopes()->create([
            'clinic_id' => (int) $payload['clinic_id'],
            'question' => $payload['question'],
            'answer' => $payload['answer'],
            'keywords' => $this->parseKeywords($payload['keywords'] ?? ''),
            'is_active' => (bool) ($payload['is_active'] ?? false),
        ]);

        return redirect()
            ->route('portal.chatbot.faqs', ['clinic_id' => $payload['clinic_id']])
            ->with('message', 'เพิ่ม FAQ เรียบร้อยแล้ว');
    }

    public function updateFaq(Request $request, int $faqId)
    {
        $payload = $request->validate([
            'clinic_id' => ['required', 'integer', 'exists:sys_clinics,id'],
            'question' => ['required', 'string', 'max:255'],
            'answer' => ['required', 'string', 'max:5000'],
            'keywords' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $faq = ChatbotFaq::withoutGlobalScopes()
            ->where('clinic_id', (int) $payload['clinic_id'])
            ->findOrFail($faqId);

        $faq->update([
            'question' => $payload['question'],
            'answer' => $payload['answer'],
            'keywords' => $this->parseKeywords($payload['keywords'] ?? ''),
            'is_active' => (bool) ($payload['is_active'] ?? false),
        ]);

        return redirect()
            ->route('portal.chatbot.faqs', ['clinic_id' => $payload['clinic_id']])
            ->with('message', 'อัปเดต FAQ เรียบร้อยแล้ว');
    }

    public function settings(Request $request)
    {
        $clinics = Clinic::query()->orderBy('name')->get(['id', 'name']);
        $selectedClinic = $this->resolveClinic($request, $clinics);

        $setting = ChatbotSetting::withoutGlobalScopes()->firstOrCreate(
            ['clinic_id' => $selectedClinic->id],
            [
                'model' => 'gemini-2.5-flash',
                'temperature' => 0.20,
                'daily_quota' => 20,
            ]
        );

        return view('portal.chatbot.settings', [
            'clinics' => $clinics,
            'selectedClinic' => $selectedClinic,
            'setting' => $setting,
        ]);
    }

    public function updateSettings(Request $request)
    {
        $payload = $request->validate([
            'clinic_id' => ['required', 'integer', 'exists:sys_clinics,id'],
            'system_prompt' => ['nullable', 'string', 'max:10000'],
            'model' => ['required', 'string', 'max:255'],
            'temperature' => ['required', 'numeric', 'min:0', 'max:1'],
            'daily_quota' => ['required', 'integer', 'min:1', 'max:1000'],
        ]);

        ChatbotSetting::withoutGlobalScopes()->updateOrCreate(
            ['clinic_id' => (int) $payload['clinic_id']],
            [
                'system_prompt' => $payload['system_prompt'] ?: null,
                'model' => $payload['model'],
                'temperature' => (float) $payload['temperature'],
                'daily_quota' => (int) $payload['daily_quota'],
            ]
        );

        return redirect()
            ->route('portal.chatbot.settings', ['clinic_id' => $payload['clinic_id']])
            ->with('message', 'บันทึกการตั้งค่า Chatbot เรียบร้อยแล้ว');
    }

    private function resolveClinic(Request $request, Collection $clinics): Clinic
    {
        $selectedClinicId = (int) ($request->query('clinic_id') ?: $request->input('clinic_id') ?: $clinics->first()?->id);

        return $clinics->firstWhere('id', $selectedClinicId) ?? $clinics->firstOrFail();
    }

    private function parseKeywords(string $raw): array
    {
        return collect(explode(',', $raw))
            ->map(fn ($keyword) => trim($keyword))
            ->filter()
            ->values()
            ->all();
    }
}
