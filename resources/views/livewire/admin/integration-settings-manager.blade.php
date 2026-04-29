<div class="space-y-8">
    <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
        <div>
            <h1 class="text-2xl font-black tracking-tight text-slate-800">Integration Center</h1>
            <p class="mt-1 text-sm font-bold text-slate-500">จัดการค่าการเชื่อมต่อบริการภายนอกและกฎการแจ้งเตือนของระบบในหน้าเดียว</p>
        </div>
        <div class="rounded-2xl border border-amber-100 bg-amber-50 px-5 py-3 text-xs font-bold leading-relaxed text-amber-700">
            ค่าที่เป็นความลับ เช่น API key, access token และ password จะถูกเข้ารหัสก่อนบันทึกลงฐานข้อมูล
        </div>
    </div>

    @if (session()->has('integration_settings_message'))
        <div class="flex items-center gap-3 rounded-2xl border border-emerald-100 bg-emerald-50 p-4 text-sm font-bold text-emerald-600">
            <i class="fa-solid fa-circle-check text-lg"></i>
            {{ session('integration_settings_message') }}
        </div>
    @endif

    @if (session()->has('integration_test_message'))
        <div class="flex items-center gap-3 rounded-2xl border border-sky-100 bg-sky-50 p-4 text-sm font-bold text-sky-700">
            <i class="fa-solid fa-paper-plane text-lg"></i>
            {{ session('integration_test_message') }}
        </div>
    @endif

    @if (session()->has('integration_test_error'))
        <div class="flex items-center gap-3 rounded-2xl border border-rose-100 bg-rose-50 p-4 text-sm font-bold text-rose-700">
            <i class="fa-solid fa-triangle-exclamation text-lg"></i>
            {{ session('integration_test_error') }}
        </div>
    @endif

    <form wire:submit.prevent="save" class="space-y-6">
        @foreach ($sections as $sectionKey => $section)
            <section class="overflow-hidden rounded-[2rem] border border-slate-100 bg-white shadow-sm shadow-slate-100">
                <div class="border-b border-slate-100 bg-slate-50/70 px-8 py-6">
                    <p class="text-[10px] font-black uppercase tracking-[0.24em] text-slate-400">{{ strtoupper(str_replace('_', ' ', $sectionKey)) }}</p>
                    <h2 class="mt-2 text-xl font-black text-slate-900">{{ $section['title'] }}</h2>
                    <p class="mt-2 max-w-3xl text-sm font-bold leading-relaxed text-slate-500">{{ $section['description'] }}</p>
                </div>

                <div class="grid gap-6 px-8 py-8 md:grid-cols-2">
                    @foreach ($section['fields'] as $key => $field)
                        <div class="{{ ($field['textarea'] ?? false) ? 'md:col-span-2' : '' }}">
                            <div class="mb-2 flex items-center justify-between">
                                <label class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">{{ $field['label'] }}</label>
                                @if (($field['encrypted'] ?? false) === true)
                                    <span class="rounded-full bg-slate-100 px-3 py-1 text-[10px] font-black uppercase tracking-[0.18em] text-slate-500">Encrypted</span>
                                @endif
                            </div>

                            @if (($field['type'] ?? null) === 'toggle')
                                <label class="flex items-center justify-between rounded-2xl border border-slate-100 bg-slate-50 px-5 py-4">
                                    <span class="text-sm font-bold text-slate-700">{{ $field['toggle_label'] ?? 'เปิดใช้งาน' }}</span>
                                    <input wire:model="settings.{{ $key }}" type="checkbox" class="h-5 w-5 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                                </label>
                            @elseif (($field['type'] ?? null) === 'select')
                                <select wire:model="settings.{{ $key }}" class="w-full rounded-2xl border border-slate-100 bg-slate-50 px-5 py-4 text-sm font-bold text-slate-700 transition-all focus:border-emerald-500 focus:bg-white focus:outline-none focus:ring-4 focus:ring-emerald-50">
                                    @foreach (($field['options'] ?? []) as $optionValue => $optionLabel)
                                        <option value="{{ $optionValue }}">{{ $optionLabel }}</option>
                                    @endforeach
                                </select>
                            @elseif (($field['type'] ?? null) === 'textarea' || ($field['textarea'] ?? false))
                                <textarea wire:model="settings.{{ $key }}" rows="4" class="w-full rounded-2xl border border-slate-100 bg-slate-50 px-5 py-4 text-sm font-bold text-slate-700 transition-all focus:border-emerald-500 focus:bg-white focus:outline-none focus:ring-4 focus:ring-emerald-50"></textarea>
                            @else
                                <input wire:model="settings.{{ $key }}" type="{{ $field['type'] ?? 'text' }}" class="w-full rounded-2xl border border-slate-100 bg-slate-50 px-5 py-4 text-sm font-bold text-slate-700 transition-all focus:border-emerald-500 focus:bg-white focus:outline-none focus:ring-4 focus:ring-emerald-50">
                            @endif

                            @error("settings.$key")
                                <span class="mt-2 block text-xs font-bold text-rose-500">{{ $message }}</span>
                            @enderror
                        </div>
                    @endforeach
                </div>

                @if ($sectionKey === 'smtp')
                    <div class="border-t border-slate-100 bg-slate-50/60 px-8 py-6">
                        <div class="flex flex-col gap-4 md:flex-row md:items-end">
                            <div class="flex-1">
                                <label class="mb-2 block text-xs font-black uppercase tracking-[0.18em] text-slate-400">Test Email Recipient</label>
                                <input wire:model="testEmailRecipient" type="email" class="w-full rounded-2xl border border-slate-100 bg-white px-5 py-4 text-sm font-bold text-slate-700 transition-all focus:border-emerald-500 focus:outline-none focus:ring-4 focus:ring-emerald-50">
                                @error('testEmailRecipient')
                                    <span class="mt-2 block text-xs font-bold text-rose-500">{{ $message }}</span>
                                @enderror
                            </div>
                            <button type="button" wire:click="sendTestEmail" class="inline-flex items-center justify-center gap-3 rounded-2xl bg-white px-6 py-4 text-sm font-black text-slate-800 shadow-sm shadow-slate-200 transition-all hover:-translate-y-0.5 active:scale-95">
                                <i class="fa-solid fa-envelope-circle-check"></i>
                                <span>Test SMTP</span>
                            </button>
                        </div>
                    </div>
                @endif

                @if ($sectionKey === 'line_messaging')
                    <div class="border-t border-slate-100 bg-slate-50/60 px-8 py-6">
                        <div class="flex flex-col gap-4 md:flex-row md:items-end">
                            <div class="flex-1">
                                <label class="mb-2 block text-xs font-black uppercase tracking-[0.18em] text-slate-400">Test LINE User ID</label>
                                <input wire:model="testLineRecipient" type="text" placeholder="เช่น Uxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx" class="w-full rounded-2xl border border-slate-100 bg-white px-5 py-4 text-sm font-bold text-slate-700 transition-all focus:border-emerald-500 focus:outline-none focus:ring-4 focus:ring-emerald-50">
                                @error('testLineRecipient')
                                    <span class="mt-2 block text-xs font-bold text-rose-500">{{ $message }}</span>
                                @enderror
                            </div>
                            <button type="button" wire:click="sendTestLine" class="inline-flex items-center justify-center gap-3 rounded-2xl bg-white px-6 py-4 text-sm font-black text-slate-800 shadow-sm shadow-slate-200 transition-all hover:-translate-y-0.5 active:scale-95">
                                <i class="fa-brands fa-line"></i>
                                <span>Test LINE Push</span>
                            </button>
                        </div>
                    </div>
                @endif
            </section>
        @endforeach

        <div class="flex justify-end">
            <button type="submit" class="inline-flex items-center gap-3 rounded-2xl bg-slate-900 px-8 py-4 text-sm font-black text-white shadow-lg shadow-slate-200 transition-all hover:-translate-y-0.5 hover:shadow-slate-300 active:scale-95">
                <i class="fa-solid fa-floppy-disk"></i>
                <span>บันทึกการตั้งค่า</span>
            </button>
        </div>
    </form>
</div>
