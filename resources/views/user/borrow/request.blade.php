<x-user-layout>
    <x-slot name="title">Borrow Request - RSU Medical</x-slot>

    <main class="space-y-8 px-6 py-8">
        <div class="space-y-2">
            <a href="{{ route('user.borrow.index') }}" class="mb-2 flex items-center gap-1 text-xs font-bold text-slate-400">
                <i class="fa-solid fa-chevron-left"></i> กลับไปเลือกอุปกรณ์
            </a>
            <h2 class="text-2xl font-black tracking-tight text-slate-900">ส่งคำขอยืมอุปกรณ์</h2>
            <p class="text-xs font-medium text-slate-500">แจ้งเหตุผลและวันที่ต้องการคืน ระบบจะส่งคำขอให้เจ้าหน้าที่ตรวจสอบ</p>
        </div>

        <section class="rounded-[2rem] border border-slate-100 bg-white p-5 shadow-sm">
            <div class="flex items-start gap-4">
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-orange-50 text-orange-600 shadow-sm">
                    <i class="fa-solid fa-box-open text-xl"></i>
                </div>
                <div class="min-w-0 flex-1">
                    <h3 class="text-base font-black text-slate-900">{{ $category->name }}</h3>
                    <p class="mt-1 text-sm font-semibold text-slate-500">{{ $category->description ?: 'อุปกรณ์สำหรับการยืมใช้งานภายในระบบ' }}</p>
                    <p class="mt-3 text-[11px] font-bold text-slate-400">ว่าง {{ $availableItems->count() }} ชิ้น</p>
                </div>
            </div>
        </section>

        <form method="POST" action="{{ route('user.borrow.store', $category) }}" class="space-y-5 rounded-[2rem] border border-slate-100 bg-white p-6 shadow-sm">
            @csrf

            @if($errors->has('category'))
                <div class="rounded-2xl border border-rose-100 bg-rose-50 px-4 py-3 text-sm font-bold text-rose-600">
                    {{ $errors->first('category') }}
                </div>
            @endif

            <div>
                <label for="reason" class="mb-2 block text-xs font-black uppercase tracking-widest text-slate-500">เหตุผลในการยืม</label>
                <textarea id="reason" name="reason" rows="5" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-700 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100" placeholder="ระบุเหตุผลหรือบริบทการใช้งาน" required>{{ old('reason') }}</textarea>
                @error('reason')
                    <p class="mt-2 text-xs font-bold text-rose-500">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="quantity" class="mb-2 block text-xs font-black uppercase tracking-widest text-slate-500">จำนวน</label>
                    <input id="quantity" name="quantity" type="number" min="1" max="1" value="{{ old('quantity', 1) }}" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-700 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100" required>
                    <p class="mt-2 text-[11px] font-bold text-slate-400">MVP ตอนนี้รองรับทีละ 1 ชิ้น</p>
                    @error('quantity')
                        <p class="mt-2 text-xs font-bold text-rose-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="due_date" class="mb-2 block text-xs font-black uppercase tracking-widest text-slate-500">วันที่คาดว่าจะคืน</label>
                    <input id="due_date" name="due_date" type="date" value="{{ old('due_date') }}" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-700 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100">
                    @error('due_date')
                        <p class="mt-2 text-xs font-bold text-rose-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <button type="submit" class="flex w-full items-center justify-center gap-2 rounded-2xl bg-emerald-600 py-4 text-sm font-black uppercase tracking-wider text-white shadow-xl shadow-emerald-100 transition-transform active:scale-95">
                <i class="fa-solid fa-paper-plane"></i>
                ส่งคำขอยืม
            </button>
        </form>
    </main>
</x-user-layout>
