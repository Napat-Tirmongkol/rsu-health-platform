<x-user-layout>
    <x-slot name="title">e-Borrow Catalog - RSU Medical</x-slot>

    <main class="space-y-8 px-6 py-8">
        <div class="space-y-3">
            <a href="{{ route('user.hub') }}" class="inline-flex items-center gap-2 text-xs font-bold text-slate-400 transition-colors hover:text-slate-600">
                <i class="fa-solid fa-chevron-left text-[10px]"></i>
                <span>กลับหน้าหลัก</span>
            </a>

            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div class="space-y-2">
                    <p class="text-[10px] font-black uppercase tracking-[0.28em] text-emerald-500">Borrow &amp; Inventory</p>
                    <h2 class="text-3xl font-black tracking-tight text-slate-950">e-Borrow Catalog</h2>
                    <p class="max-w-2xl text-sm font-bold leading-relaxed text-slate-500">
                        เลือกหมวดอุปกรณ์ที่ต้องการใช้งาน ส่งคำขอยืม และติดตามสถานะได้จากศูนย์กลางเดียว
                    </p>
                </div>

                <a href="{{ route('user.borrow.history') }}" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-slate-950 px-5 py-3 text-[11px] font-black uppercase tracking-[0.22em] text-white shadow-lg shadow-slate-200 transition-all hover:bg-slate-800">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                    <span>My History</span>
                </a>
            </div>
        </div>

        <section class="overflow-hidden rounded-[2.5rem] border border-emerald-100 bg-white shadow-[0_24px_80px_rgba(15,23,42,0.08)]">
            <div class="bg-[radial-gradient(circle_at_top_left,rgba(16,185,129,0.18),transparent_42%)] px-6 py-6 lg:px-8">
                <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-[0.24em] text-slate-400">Available Now</p>
                        <h3 class="mt-2 text-2xl font-black tracking-tight text-slate-950">พร้อมให้ยืมจากหมวดที่เปิดใช้งาน</h3>
                        <p class="mt-2 max-w-2xl text-sm font-bold leading-relaxed text-slate-500">
                            เลือกหมวดที่ยังมีอุปกรณ์ว่าง ระบบจะแสดงภาพรวมจำนวนคงเหลือและพาคุณไปยังหน้าส่งคำขอทันที
                        </p>
                    </div>

                    <div class="grid grid-cols-2 gap-3 lg:min-w-[18rem]">
                        <div class="rounded-2xl border border-white/70 bg-white/80 px-4 py-4 shadow-sm backdrop-blur">
                            <p class="text-[10px] font-black uppercase tracking-[0.24em] text-slate-400">Categories</p>
                            <p class="mt-2 text-2xl font-black tracking-tight text-slate-950">{{ number_format($categories->total()) }}</p>
                        </div>
                        <div class="rounded-2xl border border-white/70 bg-white/80 px-4 py-4 shadow-sm backdrop-blur">
                            <p class="text-[10px] font-black uppercase tracking-[0.24em] text-slate-400">Page</p>
                            <p class="mt-2 text-2xl font-black tracking-tight text-slate-950">{{ $categories->lastPage() > 0 ? $categories->currentPage() : 0 }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <div class="grid grid-cols-1 gap-5">
            @forelse($categories as $category)
                <article class="overflow-hidden rounded-[2.25rem] border border-slate-200 bg-white shadow-[0_16px_50px_rgba(15,23,42,0.06)] transition-all hover:-translate-y-0.5 hover:shadow-[0_20px_55px_rgba(15,23,42,0.10)]">
                    <div class="grid gap-0 lg:grid-cols-[15rem,minmax(0,1fr)]">
                        <div class="relative min-h-[13rem] overflow-hidden bg-slate-100">
                            @if($category->image_url)
                                <img src="{{ $category->image_url }}" alt="{{ $category->name }}" class="absolute inset-0 h-full w-full object-cover">
                                <div class="absolute inset-0 bg-gradient-to-t from-slate-950/28 via-slate-950/5 to-transparent"></div>
                            @else
                                <div class="absolute inset-0 bg-[linear-gradient(160deg,rgba(251,146,60,0.18),rgba(255,255,255,0.92)_58%)]"></div>
                                <div class="absolute inset-0 flex items-center justify-center">
                                    <div class="flex h-20 w-20 items-center justify-center rounded-[2rem] bg-white/90 text-orange-600 shadow-lg">
                                        <i class="fa-solid fa-box-open text-3xl"></i>
                                    </div>
                                </div>
                            @endif

                            <div class="absolute left-4 top-4">
                                <span class="inline-flex items-center rounded-full border border-white/70 bg-white/85 px-3 py-1.5 text-[10px] font-black uppercase tracking-[0.22em] text-slate-700 shadow-sm backdrop-blur">
                                    {{ $category->available_items_count > 0 ? 'พร้อมยืม' : 'เต็มแล้ว' }}
                                </span>
                            </div>

                            <div class="absolute bottom-4 left-4 right-4">
                                <div class="inline-flex items-center rounded-2xl bg-slate-950/72 px-4 py-3 text-white backdrop-blur">
                                    <div>
                                        <p class="text-[10px] font-black uppercase tracking-[0.22em] text-white/55">Available</p>
                                        <p class="mt-1 text-xl font-black tracking-tight">{{ $category->available_items_count }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex min-w-0 flex-col justify-between p-6 lg:p-7">
                            <div class="space-y-4">
                                <div class="flex flex-wrap items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <h3 class="text-xl font-black tracking-tight text-slate-950">{{ $category->name }}</h3>
                                        <p class="mt-2 max-w-2xl text-sm font-bold leading-relaxed text-slate-500">
                                            {{ $category->description ?: 'อุปกรณ์สำหรับการยืมใช้งานภายในระบบ โดยเจ้าหน้าที่จะตรวจสอบและยืนยันคำขออีกครั้งก่อนอนุมัติ' }}
                                        </p>
                                    </div>

                                    <span class="inline-flex items-center rounded-full {{ $category->available_items_count > 0 ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200' : 'bg-slate-100 text-slate-500 ring-1 ring-slate-200' }} px-3 py-2 text-[10px] font-black uppercase tracking-[0.22em]">
                                        {{ $category->available_items_count }} available
                                    </span>
                                </div>

                                <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                                    <div class="rounded-2xl bg-slate-50 px-4 py-4">
                                        <p class="text-[10px] font-black uppercase tracking-[0.22em] text-slate-400">ทั้งหมด</p>
                                        <p class="mt-2 text-xl font-black tracking-tight text-slate-950">{{ number_format($category->total_quantity) }}</p>
                                    </div>
                                    <div class="rounded-2xl bg-emerald-50 px-4 py-4">
                                        <p class="text-[10px] font-black uppercase tracking-[0.22em] text-emerald-500">พร้อมยืม</p>
                                        <p class="mt-2 text-xl font-black tracking-tight text-emerald-700">{{ number_format($category->available_items_count) }}</p>
                                    </div>
                                    <div class="rounded-2xl bg-slate-50 px-4 py-4">
                                        <p class="text-[10px] font-black uppercase tracking-[0.22em] text-slate-400">คงเหลือในคลัง</p>
                                        <p class="mt-2 text-xl font-black tracking-tight text-slate-950">{{ number_format($category->available_quantity) }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-6 flex flex-col gap-3 border-t border-slate-100 pt-5 sm:flex-row sm:items-center sm:justify-between">
                                <p class="text-xs font-bold text-slate-400">
                                    เจ้าหน้าที่จะตรวจสอบคำขอและจัดสรรอุปกรณ์ตามคิวที่พร้อมให้บริการ
                                </p>

                                @if($category->available_items_count > 0)
                                    <a href="{{ route('user.borrow.create', $category) }}" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-emerald-600 px-5 py-3 text-[11px] font-black uppercase tracking-[0.22em] text-white shadow-lg shadow-emerald-100 transition-all hover:bg-emerald-700">
                                        <i class="fa-solid fa-paper-plane"></i>
                                        <span>Request Borrow</span>
                                    </a>
                                @else
                                    <span class="inline-flex items-center justify-center gap-2 rounded-2xl bg-slate-100 px-5 py-3 text-[11px] font-black uppercase tracking-[0.22em] text-slate-400">
                                        <i class="fa-solid fa-clock"></i>
                                        <span>Unavailable</span>
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </article>
            @empty
                <section class="rounded-[2.25rem] border border-slate-200 bg-white px-6 py-12 text-center shadow-sm">
                    <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-[2rem] bg-slate-50 text-slate-300">
                        <i class="fa-solid fa-box-open text-3xl"></i>
                    </div>
                    <h3 class="mt-5 text-lg font-black tracking-tight text-slate-900">ยังไม่มีหมวดอุปกรณ์ที่เปิดให้ยืม</h3>
                    <p class="mt-2 text-sm font-bold text-slate-500">เมื่อเจ้าหน้าที่เปิดหมวดอุปกรณ์และเพิ่มรายการในคลัง คุณจะสามารถส่งคำขอจากหน้านี้ได้ทันที</p>
                </section>
            @endforelse
        </div>

        @if($categories->hasPages())
            <div class="space-y-3">
                <p class="text-center text-xs font-bold text-slate-400">
                    หน้า {{ $categories->currentPage() }} / {{ $categories->lastPage() }} · รวม {{ number_format($categories->total()) }} รายการ
                </p>
                <div class="flex justify-center">
                    {{ $categories->onEachSide(1)->links() }}
                </div>
            </div>
        @endif
    </main>
</x-user-layout>
