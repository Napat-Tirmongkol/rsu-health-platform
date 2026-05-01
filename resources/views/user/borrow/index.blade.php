<x-user-layout>
    <x-slot name="title">e-Borrow Catalog - RSU Medical</x-slot>

    <main class="space-y-8 px-6 py-8">
        <div class="space-y-2">
            <a href="{{ route('user.hub') }}" class="mb-2 flex items-center gap-1 text-xs font-bold text-slate-400">
                <i class="fa-solid fa-chevron-left"></i> กลับหน้าหลัก
            </a>
            <h2 class="text-2xl font-black tracking-tight text-slate-900">e-Borrow</h2>
            <p class="text-xs font-medium text-slate-500">เลือกหมวดอุปกรณ์ที่ต้องการยืมและส่งคำขอผ่านระบบ</p>
        </div>

        <div class="flex items-center justify-between rounded-2xl border border-slate-100 bg-white px-5 py-4 shadow-sm">
            <div>
                <p class="text-sm font-black text-slate-900">พร้อมให้ยืมตอนนี้</p>
                <p class="mt-1 text-xs font-semibold text-slate-500">เลือกจากหมวดที่มีอุปกรณ์ว่างและส่งคำขอได้ทันที</p>
            </div>
            <a href="{{ route('user.borrow.history') }}" class="rounded-xl bg-slate-900 px-4 py-2 text-[11px] font-black uppercase tracking-wider text-white">
                My History
            </a>
        </div>

        <div class="grid grid-cols-1 gap-4">
            @forelse($categories as $category)
                <div class="overflow-hidden rounded-[2rem] border border-slate-100 bg-white shadow-sm">
                    <div class="flex items-start gap-4 p-5">
                        <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-orange-50 text-orange-600 shadow-sm">
                            <i class="fa-solid fa-box-open text-xl"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <h3 class="truncate text-base font-black text-slate-900">{{ $category->name }}</h3>
                                    <p class="mt-1 text-sm font-semibold leading-relaxed text-slate-500">{{ $category->description ?: 'อุปกรณ์สำหรับการยืมใช้งานภายในระบบ' }}</p>
                                </div>
                                <span class="shrink-0 rounded-full {{ $category->available_items_count > 0 ? 'bg-emerald-50 text-emerald-600' : 'bg-slate-100 text-slate-500' }} px-3 py-1 text-[10px] font-black uppercase tracking-wider">
                                    {{ $category->available_items_count }} available
                                </span>
                            </div>

                            <div class="mt-4 flex items-center justify-between">
                                <div class="text-[11px] font-bold text-slate-400">
                                    รวม {{ $category->total_quantity }} ชิ้น · ว่าง {{ $category->available_items_count }} ชิ้น
                                </div>
                                @if($category->available_items_count > 0)
                                    <a href="{{ route('user.borrow.create', $category) }}" class="rounded-xl bg-emerald-600 px-4 py-2 text-[11px] font-black uppercase tracking-wider text-white">
                                        Request
                                    </a>
                                @else
                                    <span class="rounded-xl bg-slate-100 px-4 py-2 text-[11px] font-black uppercase tracking-wider text-slate-400">
                                        Unavailable
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="rounded-[2rem] border border-slate-100 bg-white p-8 text-center shadow-sm">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-50 text-slate-300">
                        <i class="fa-solid fa-box-open text-xl"></i>
                    </div>
                    <p class="mt-4 text-sm font-black text-slate-500">ยังไม่มีหมวดอุปกรณ์ที่เปิดให้ยืม</p>
                </div>
            @endforelse
        </div>

        @if($categories->hasPages())
            <div class="space-y-3">
                <p class="text-center text-xs font-bold text-slate-400">
                    หน้า {{ $categories->currentPage() }} / {{ $categories->lastPage() }} · รวม {{ $categories->total() }} รายการ
                </p>
                <div class="flex justify-center">
                    {{ $categories->onEachSide(1)->links() }}
                </div>
            </div>
        @endif
    </main>
</x-user-layout>
