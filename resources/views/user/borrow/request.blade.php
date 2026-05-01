<x-user-layout>
    <x-slot name="title">Borrow Request - RSU Medical</x-slot>

    <main class="space-y-8 px-6 py-8">
        <div class="space-y-3">
            <a href="{{ route('user.borrow.index') }}" class="inline-flex items-center gap-2 text-xs font-bold text-slate-400 transition-colors hover:text-slate-600">
                <i class="fa-solid fa-chevron-left text-[10px]"></i>
                <span>กลับไปเลือกอุปกรณ์</span>
            </a>

            <div class="space-y-2">
                <p class="text-[10px] font-black uppercase tracking-[0.28em] text-emerald-500">Borrow Request</p>
                <h2 class="text-3xl font-black tracking-tight text-slate-950">ส่งคำขอยืมอุปกรณ์</h2>
                <p class="max-w-2xl text-sm font-bold leading-relaxed text-slate-500">
                    ระบุเหตุผลการใช้งานและวันที่คาดว่าจะคืน เจ้าหน้าที่จะตรวจสอบคำขอและยืนยันสถานะให้ผ่านระบบ
                </p>
            </div>
        </div>

        <section class="overflow-hidden rounded-[2.5rem] border border-slate-200 bg-white shadow-[0_18px_60px_rgba(15,23,42,0.08)]">
            <div class="grid gap-0 lg:grid-cols-[18rem,minmax(0,1fr)]">
                <div class="relative min-h-[16rem] overflow-hidden bg-slate-100">
                    @if($category->image_url)
                        <img src="{{ $category->image_url }}" alt="{{ $category->name }}" class="absolute inset-0 h-full w-full object-cover">
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-950/35 via-slate-950/6 to-transparent"></div>
                    @else
                        <div class="absolute inset-0 bg-[linear-gradient(160deg,rgba(16,185,129,0.16),rgba(255,255,255,0.92)_60%)]"></div>
                        <div class="absolute inset-0 flex items-center justify-center">
                            <div class="flex h-24 w-24 items-center justify-center rounded-[2rem] bg-white/92 text-emerald-600 shadow-lg">
                                <i class="fa-solid fa-box-open text-4xl"></i>
                            </div>
                        </div>
                    @endif

                    <div class="absolute left-4 top-4">
                        <span class="inline-flex items-center rounded-full border border-white/70 bg-white/85 px-3 py-1.5 text-[10px] font-black uppercase tracking-[0.22em] text-slate-700 shadow-sm backdrop-blur">
                            {{ $availableItems->count() > 0 ? 'พร้อมยืม' : 'รออุปกรณ์' }}
                        </span>
                    </div>

                    <div class="absolute bottom-4 left-4 right-4">
                        <div class="inline-flex items-center rounded-2xl bg-slate-950/72 px-4 py-3 text-white backdrop-blur">
                            <div>
                                <p class="text-[10px] font-black uppercase tracking-[0.22em] text-white/55">Available Items</p>
                                <p class="mt-1 text-2xl font-black tracking-tight">{{ number_format($availableItems->count()) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="space-y-5 p-6 lg:p-8">
                    <div class="space-y-2">
                        <h3 class="text-2xl font-black tracking-tight text-slate-950">{{ $category->name }}</h3>
                        <p class="max-w-2xl text-sm font-bold leading-relaxed text-slate-500">
                            {{ $category->description ?: 'อุปกรณ์สำหรับการยืมใช้งานภายในระบบ โดยเจ้าหน้าที่จะตรวจสอบและยืนยันคำขออีกครั้งก่อนอนุมัติ' }}
                        </p>
                    </div>

                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                        <div class="rounded-2xl bg-slate-50 px-4 py-4">
                            <p class="text-[10px] font-black uppercase tracking-[0.22em] text-slate-400">หมวด</p>
                            <p class="mt-2 text-base font-black tracking-tight text-slate-950">{{ $category->name }}</p>
                        </div>
                        <div class="rounded-2xl bg-emerald-50 px-4 py-4">
                            <p class="text-[10px] font-black uppercase tracking-[0.22em] text-emerald-500">พร้อมยืม</p>
                            <p class="mt-2 text-base font-black tracking-tight text-emerald-700">{{ number_format($availableItems->count()) }} ชิ้น</p>
                        </div>
                        <div class="rounded-2xl bg-slate-50 px-4 py-4">
                            <p class="text-[10px] font-black uppercase tracking-[0.22em] text-slate-400">รูปแบบ</p>
                            <p class="mt-2 text-base font-black tracking-tight text-slate-950">ยืมครั้งละ 1 ชิ้น</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <form method="POST" action="{{ route('user.borrow.store', $category) }}" class="space-y-6 rounded-[2.5rem] border border-slate-200 bg-white p-6 shadow-[0_18px_60px_rgba(15,23,42,0.06)] lg:p-8">
            @csrf

            @if($errors->has('category'))
                <div class="rounded-2xl border border-rose-100 bg-rose-50 px-5 py-4 text-sm font-bold text-rose-600">
                    {{ $errors->first('category') }}
                </div>
            @endif

            <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr),22rem]">
                <div class="space-y-5">
                    <div>
                        <label for="reason" class="mb-2 block text-xs font-black uppercase tracking-[0.22em] text-slate-500">เหตุผลในการยืม</label>
                        <textarea id="reason" name="reason" rows="6" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4 text-sm font-semibold text-slate-700 outline-none transition-all focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-100" placeholder="ระบุวัตถุประสงค์หรือบริบทการใช้งาน เพื่อให้เจ้าหน้าที่ตรวจสอบคำขอได้เร็วขึ้น" required>{{ old('reason') }}</textarea>
                        @error('reason')
                            <p class="mt-2 text-sm font-bold text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="rounded-2xl border border-slate-100 bg-slate-50 px-5 py-4">
                        <p class="text-xs font-black uppercase tracking-[0.22em] text-slate-400">หมายเหตุ</p>
                        <p class="mt-2 text-sm font-bold leading-relaxed text-slate-500">
                            หากต้องการใช้งานต่อเนื่องหลายวัน แนะนำให้ระบุวันที่คาดว่าจะคืนให้ชัดเจน เพื่อให้เจ้าหน้าที่วางแผนคิวอุปกรณ์ได้เหมาะสม
                        </p>
                    </div>
                </div>

                <div class="space-y-5">
                    <div>
                        <label for="quantity" class="mb-2 block text-xs font-black uppercase tracking-[0.22em] text-slate-500">จำนวน</label>
                        <input id="quantity" name="quantity" type="number" min="1" max="1" value="{{ old('quantity', 1) }}" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4 text-sm font-semibold text-slate-700 outline-none transition-all focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-100" required>
                        <p class="mt-2 text-[11px] font-bold text-slate-400">MVP ตอนนี้รองรับการยืมครั้งละ 1 ชิ้น</p>
                        @error('quantity')
                            <p class="mt-2 text-sm font-bold text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="due_date" class="mb-2 block text-xs font-black uppercase tracking-[0.22em] text-slate-500">วันที่คาดว่าจะคืน</label>
                        <input id="due_date" name="due_date" type="date" value="{{ old('due_date') }}" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4 text-sm font-semibold text-slate-700 outline-none transition-all focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-100">
                        @error('due_date')
                            <p class="mt-2 text-sm font-bold text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="rounded-2xl bg-emerald-50 px-5 py-4">
                        <p class="text-[10px] font-black uppercase tracking-[0.22em] text-emerald-500">Flow</p>
                        <ol class="mt-3 space-y-2 text-sm font-bold text-emerald-700">
                            <li>1. ส่งคำขอผ่านระบบ</li>
                            <li>2. เจ้าหน้าที่ตรวจสอบรายการ</li>
                            <li>3. รับผลอนุมัติจากประวัติการยืม</li>
                        </ol>
                    </div>
                </div>
            </div>

            <div class="flex flex-col gap-3 border-t border-slate-100 pt-6 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-sm font-bold text-slate-400">
                    เมื่อส่งคำขอแล้ว คุณสามารถติดตามสถานะได้จากหน้า My History
                </p>

                <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-emerald-600 px-6 py-4 text-[11px] font-black uppercase tracking-[0.22em] text-white shadow-xl shadow-emerald-100 transition-all hover:bg-emerald-700 active:scale-[0.99]">
                    <i class="fa-solid fa-paper-plane"></i>
                    <span>ส่งคำขอยืม</span>
                </button>
            </div>
        </form>
    </main>
</x-user-layout>
