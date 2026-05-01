<x-portal-layout title="Chatbot Settings">
    <div class="py-10">
        <div class="mx-auto max-w-7xl space-y-6 px-6 lg:px-8">
            @if (session('message'))
                <div class="rounded-[2rem] border border-emerald-200 bg-emerald-50 px-6 py-5 text-sm font-bold text-emerald-700 shadow-sm">
                    {{ session('message') }}
                </div>
            @endif

            <section class="rounded-[2.5rem] border border-slate-200 bg-white p-6 shadow-sm lg:p-8">
                <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-[0.24em] text-sky-500">LINE Chatbot</p>
                        <h2 class="mt-3 text-3xl font-black tracking-tight text-slate-950">Chatbot Settings</h2>
                        <p class="mt-2 max-w-3xl text-sm font-bold text-slate-500">
                            กำหนด model, quota, และ system prompt แยกตามคลินิก เพื่อควบคุมพฤติกรรมของ bot ให้เหมาะกับบริบทหน้างาน
                        </p>
                    </div>

                    <form method="GET" action="{{ route('portal.chatbot.settings') }}">
                        <select name="clinic_id" class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-700" onchange="this.form.submit()">
                            @foreach($clinics as $clinic)
                                <option value="{{ $clinic->id }}" @selected($selectedClinic->id === $clinic->id)>{{ $clinic->name }}</option>
                            @endforeach
                        </select>
                    </form>
                </div>
            </section>

            <div class="grid grid-cols-1 gap-6 xl:grid-cols-[minmax(0,1.2fr),minmax(22rem,0.8fr)]">
                <section class="rounded-[2.5rem] border border-slate-200 bg-white p-6 shadow-sm lg:p-8">
                    <form method="POST" action="{{ route('portal.chatbot.settings.update') }}" class="space-y-5">
                        @csrf
                        <input type="hidden" name="clinic_id" value="{{ $selectedClinic->id }}">

                        <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
                            <div>
                                <label class="mb-2 block text-xs font-black uppercase tracking-[0.22em] text-slate-500">Model</label>
                                <input name="model" type="text" value="{{ old('model', $setting->model) }}" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4 text-sm font-bold text-slate-700">
                                @error('model') <p class="mt-2 text-sm font-bold text-rose-500">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="mb-2 block text-xs font-black uppercase tracking-[0.22em] text-slate-500">Daily Quota / User</label>
                                <input name="daily_quota" type="number" min="1" max="1000" value="{{ old('daily_quota', $setting->daily_quota) }}" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4 text-sm font-bold text-slate-700">
                                @error('daily_quota') <p class="mt-2 text-sm font-bold text-rose-500">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div>
                            <label class="mb-2 block text-xs font-black uppercase tracking-[0.22em] text-slate-500">Temperature</label>
                            <input name="temperature" type="number" min="0" max="1" step="0.01" value="{{ old('temperature', number_format($setting->temperature, 2, '.', '')) }}" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4 text-sm font-bold text-slate-700">
                            @error('temperature') <p class="mt-2 text-sm font-bold text-rose-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="mb-2 block text-xs font-black uppercase tracking-[0.22em] text-slate-500">System Prompt</label>
                            <textarea name="system_prompt" rows="10" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4 text-sm font-bold text-slate-700">{{ old('system_prompt', $setting->system_prompt) }}</textarea>
                            <p class="mt-2 text-xs font-bold text-slate-400">ใช้สำหรับกำหนดบุคลิก ข้อจำกัด และขอบเขตการตอบของ bot ต่อคลินิกนี้</p>
                            @error('system_prompt') <p class="mt-2 text-sm font-bold text-rose-500">{{ $message }}</p> @enderror
                        </div>

                        <button type="submit" class="rounded-2xl bg-sky-600 px-6 py-4 text-[11px] font-black uppercase tracking-[0.22em] text-white shadow-lg shadow-sky-100">
                            Save Chatbot Settings
                        </button>
                    </form>
                </section>

                <section class="space-y-6">
                    <div class="rounded-[2.5rem] border border-slate-200 bg-white p-6 shadow-sm">
                        <p class="text-[10px] font-black uppercase tracking-[0.24em] text-slate-400">Current Clinic</p>
                        <h3 class="mt-3 text-2xl font-black tracking-tight text-slate-950">{{ $selectedClinic->name }}</h3>
                        <p class="mt-2 text-sm font-bold text-slate-500">ค่าที่บันทึกจะมีผลกับ chatbot ของคลินิกนี้โดยตรง</p>
                    </div>

                    <div class="rounded-[2.5rem] border border-amber-200 bg-amber-50 p-6 shadow-sm">
                        <p class="text-[10px] font-black uppercase tracking-[0.24em] text-amber-600">Safety Note</p>
                        <p class="mt-3 text-sm font-bold leading-relaxed text-amber-800">
                            แนะนำให้ system prompt ระบุชัดเจนว่า bot ให้ข้อมูลเบื้องต้นเท่านั้น ไม่วินิจฉัยโรค และต้องส่งต่อ emergency case ไปยังเจ้าหน้าที่หรือ 1669 ทันที
                        </p>
                    </div>

                    <div class="rounded-[2.5rem] border border-slate-200 bg-slate-950 p-6 text-white shadow-[0_24px_80px_rgba(15,23,42,0.18)]">
                        <p class="text-[10px] font-black uppercase tracking-[0.24em] text-white/45">Recommended Defaults</p>
                        <div class="mt-4 space-y-4 text-sm font-bold text-white/78">
                            <div>Model: <span class="text-white">gemini-2.5-flash</span></div>
                            <div>Temperature: <span class="text-white">0.20</span></div>
                            <div>Daily Quota: <span class="text-white">20</span></div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
</x-portal-layout>
