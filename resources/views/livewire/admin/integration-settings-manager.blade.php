<div class="space-y-6">
    <section class="rounded-[24px] border border-[#e8eef7] bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-[10px] font-black uppercase tracking-[0.14em] text-slate-400">Integration Center</p>
                <h2 class="mt-3 text-3xl font-black tracking-tight text-slate-900">ตั้งค่าการเชื่อมต่อระบบภายนอก</h2>
                <p class="mt-3 max-w-3xl text-sm font-semibold leading-relaxed text-slate-600">
                    จัดการ SMTP, LINE Messaging API และ Gemini API ในหน้าจอเดียว พร้อมเครื่องมือทดสอบเพื่อยืนยันว่าค่าที่บันทึกไว้ใช้งานได้จริง
                </p>
            </div>
            <div class="rounded-[18px] border border-amber-200 bg-amber-50 px-5 py-4 text-xs font-bold leading-relaxed text-amber-700">
                ค่าที่เป็นความลับ เช่น API key, access token และ password จะถูกเข้ารหัสก่อนบันทึกลงฐานข้อมูล
            </div>
        </div>
    </section>

    @if (session()->has('integration_settings_message'))
        <div class="rounded-[18px] border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-bold text-emerald-700">
            <i class="fa-solid fa-circle-check mr-2"></i>
            {{ session('integration_settings_message') }}
        </div>
    @endif

    @if (session()->has('integration_test_message'))
        <div class="rounded-[18px] border border-sky-200 bg-sky-50 px-5 py-4 text-sm font-bold text-sky-700">
            <i class="fa-solid fa-paper-plane mr-2"></i>
            {{ session('integration_test_message') }}
        </div>
    @endif

    @if (session()->has('integration_test_error'))
        <div class="rounded-[18px] border border-rose-200 bg-rose-50 px-5 py-4 text-sm font-bold text-rose-700">
            <i class="fa-solid fa-triangle-exclamation mr-2"></i>
            {{ session('integration_test_error') }}
        </div>
    @endif

    <form wire:submit.prevent="save" class="space-y-6">
        @foreach ($sections as $sectionKey => $section)
            <section class="overflow-hidden rounded-[24px] border border-[#e8eef7] bg-white shadow-sm">
                <div class="border-b border-[#f1f5f9] bg-[#f8fafc] px-6 py-5">
                    <p class="text-[10px] font-black uppercase tracking-[0.14em] text-slate-400">{{ strtoupper(str_replace('_', ' ', $sectionKey)) }}</p>
                    <h3 class="mt-2 text-xl font-black text-slate-900">{{ $section['title'] }}</h3>
                    <p class="mt-2 max-w-3xl text-sm font-semibold leading-relaxed text-slate-600">{{ $section['description'] }}</p>
                </div>

                <div class="grid gap-5 px-6 py-6 md:grid-cols-2">
                    @foreach ($section['fields'] as $key => $field)
                        <div class="{{ ($field['textarea'] ?? false) ? 'md:col-span-2' : '' }}">
                            <div class="mb-2 flex items-center justify-between">
                                <label class="text-[10px] font-black uppercase tracking-[0.14em] text-slate-400">{{ $field['label'] }}</label>
                                @if (($field['encrypted'] ?? false) === true)
                                    <span class="rounded-full border border-[#e2e8f0] bg-[#f8fafc] px-3 py-1 text-[10px] font-black uppercase tracking-[0.14em] text-slate-500">Encrypted</span>
                                @endif
                            </div>

                            @if (($field['type'] ?? null) === 'toggle')
                                <label class="flex items-center justify-between rounded-[16px] border border-[#e2e8f0] bg-[#f8fafc] px-5 py-4">
                                    <span class="text-sm font-semibold text-slate-700">{{ $field['toggle_label'] ?? 'เปิดใช้งาน' }}</span>
                                    <input wire:model="settings.{{ $key }}" type="checkbox" class="h-5 w-5 rounded border-slate-300 text-[#2e9e63] focus:ring-[#2e9e63]">
                                </label>
                            @elseif (($field['type'] ?? null) === 'select')
                                <select wire:model="settings.{{ $key }}" class="w-full rounded-[16px] border border-[#e2e8f0] bg-[#f8fafc] px-5 py-4 text-sm font-semibold text-slate-700 transition-all focus:border-[#2e9e63] focus:bg-white focus:outline-none focus:ring-4 focus:ring-[#e8f8f0]">
                                    @foreach (($field['options'] ?? []) as $optionValue => $optionLabel)
                                        <option value="{{ $optionValue }}">{{ $optionLabel }}</option>
                                    @endforeach
                                </select>
                            @elseif (($field['type'] ?? null) === 'textarea' || ($field['textarea'] ?? false))
                                <textarea wire:model="settings.{{ $key }}" rows="4" class="w-full rounded-[16px] border border-[#e2e8f0] bg-[#f8fafc] px-5 py-4 text-sm font-semibold text-slate-700 transition-all focus:border-[#2e9e63] focus:bg-white focus:outline-none focus:ring-4 focus:ring-[#e8f8f0]"></textarea>
                            @else
                                <input wire:model="settings.{{ $key }}" type="{{ $field['type'] ?? 'text' }}" class="w-full rounded-[16px] border border-[#e2e8f0] bg-[#f8fafc] px-5 py-4 text-sm font-semibold text-slate-700 transition-all focus:border-[#2e9e63] focus:bg-white focus:outline-none focus:ring-4 focus:ring-[#e8f8f0]">
                            @endif

                            @error("settings.$key")
                                <span class="mt-2 block text-xs font-bold text-rose-500">{{ $message }}</span>
                            @enderror
                        </div>
                    @endforeach
                </div>

                @if ($sectionKey === 'smtp')
                    <div class="border-t border-[#f1f5f9] bg-[#f8fafc] px-6 py-5">
                        <div class="flex flex-col gap-4 md:flex-row md:items-end">
                            <div class="flex-1">
                                <label class="mb-2 block text-[10px] font-black uppercase tracking-[0.14em] text-slate-400">Test Email Recipient</label>
                                <input wire:model="testEmailRecipient" type="email" class="w-full rounded-[16px] border border-[#e2e8f0] bg-white px-5 py-4 text-sm font-semibold text-slate-700 transition-all focus:border-[#2e9e63] focus:outline-none focus:ring-4 focus:ring-[#e8f8f0]">
                                @error('testEmailRecipient')
                                    <span class="mt-2 block text-xs font-bold text-rose-500">{{ $message }}</span>
                                @enderror
                            </div>
                            <button type="button" wire:click="sendTestEmail" class="inline-flex items-center justify-center gap-3 rounded-[16px] border border-[#e2e8f0] bg-white px-6 py-4 text-sm font-black text-slate-800 transition-all hover:bg-[#f0faf4] hover:text-[#2e9e63]">
                                <i class="fa-solid fa-envelope-circle-check"></i>
                                <span>Test SMTP</span>
                            </button>
                        </div>
                    </div>
                @endif

                @if ($sectionKey === 'line_messaging')
                    <div class="border-t border-[#f1f5f9] bg-[#f8fafc] px-6 py-5">
                        <div class="flex flex-col gap-4 md:flex-row md:items-end">
                            <div class="flex-1">
                                <label class="mb-2 block text-[10px] font-black uppercase tracking-[0.14em] text-slate-400">Test LINE User ID</label>
                                <input wire:model="testLineRecipient" type="text" placeholder="เช่น Uxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx" class="w-full rounded-[16px] border border-[#e2e8f0] bg-white px-5 py-4 text-sm font-semibold text-slate-700 transition-all focus:border-[#2e9e63] focus:outline-none focus:ring-4 focus:ring-[#e8f8f0]">
                                @error('testLineRecipient')
                                    <span class="mt-2 block text-xs font-bold text-rose-500">{{ $message }}</span>
                                @enderror
                            </div>
                            <button type="button" wire:click="sendTestLine" class="inline-flex items-center justify-center gap-3 rounded-[16px] border border-[#e2e8f0] bg-white px-6 py-4 text-sm font-black text-slate-800 transition-all hover:bg-[#f0faf4] hover:text-[#2e9e63]">
                                <i class="fa-brands fa-line"></i>
                                <span>Test LINE Push</span>
                            </button>
                        </div>
                    </div>
                @endif

                @if ($sectionKey === 'gemini')
                    <div class="border-t border-[#f1f5f9] bg-[#f8fafc] px-6 py-5">
                        <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                            <div class="flex-1">
                                <p class="text-[10px] font-black uppercase tracking-[0.14em] text-slate-400">Test Gemini Connection</p>
                                <p class="mt-2 text-sm font-semibold leading-relaxed text-slate-600">ระบบจะลองเรียกโมเดลที่ตั้งค่าไว้และคาดหวังข้อความตอบกลับสั้นๆ เพื่อยืนยันว่า API key, model และ base URL ใช้งานได้จริง</p>
                                <div wire:loading wire:target="sendTestGemini" class="mt-3 rounded-[16px] border border-sky-200 bg-white px-4 py-3 text-sm font-bold text-sky-700">
                                    กำลังทดสอบการเชื่อมต่อ Gemini...
                                </div>
                                @if ($testGeminiStatus !== '')
                                    <div class="mt-3 rounded-[16px] border border-emerald-200 bg-white px-4 py-3 text-sm font-bold text-slate-700">
                                        <span class="text-emerald-600">Latest result:</span>
                                        <span>{{ $testGeminiStatus }}</span>
                                    </div>
                                @endif
                                @if ($testGeminiError !== '')
                                    <div class="mt-3 rounded-[16px] border border-rose-200 bg-white px-4 py-3 text-sm font-bold text-rose-700">
                                        <span class="text-rose-600">Latest error:</span>
                                        <span>{{ $testGeminiError }}</span>
                                    </div>
                                @endif
                            </div>
                            <button type="button" wire:click="sendTestGemini" class="inline-flex items-center justify-center gap-3 rounded-[16px] border border-[#e2e8f0] bg-white px-6 py-4 text-sm font-black text-slate-800 transition-all hover:bg-[#f0faf4] hover:text-[#2e9e63]">
                                <i class="fa-solid fa-wand-magic-sparkles"></i>
                                <span>Test Gemini</span>
                            </button>
                        </div>
                    </div>
                @endif
            </section>
        @endforeach

        <div class="flex justify-end">
            <button type="submit" class="inline-flex items-center gap-3 rounded-[16px] bg-[#2e9e63] px-8 py-4 text-sm font-black text-white shadow-lg shadow-[#2e9e63]/20 transition-all hover:-translate-y-0.5 hover:bg-[#2a8455]">
                <i class="fa-solid fa-floppy-disk"></i>
                <span>บันทึกการตั้งค่า</span>
            </button>
        </div>
    </form>
</div>
