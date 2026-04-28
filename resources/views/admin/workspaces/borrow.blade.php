<x-admin-layout>
    <x-slot name="title">Borrow & Inventory Workspace</x-slot>

    <div class="space-y-8">
        <div class="rounded-[2.5rem] border border-sky-100 bg-gradient-to-br from-sky-50 via-white to-cyan-50 p-8 text-slate-900 shadow-xl shadow-sky-100">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="text-[10px] font-black uppercase tracking-[0.3em] text-sky-500">e-Borrow</p>
                    <h2 class="mt-3 text-3xl font-black tracking-tight">Borrow Operations Command Center</h2>
                    <p class="mt-3 max-w-2xl text-sm font-bold leading-relaxed text-slate-600">
                        รวมคำขอยืม สต็อก การคืน และค่าปรับไว้ใน workspace เดียว เพื่อให้ทีมหน้างานเดิน flow ได้ต่อเนื่องตั้งแต่รับคำขอจนปิดรายการ
                    </p>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <a href="{{ route('admin.walk_in_borrow') }}" class="rounded-2xl border border-sky-100 bg-white px-5 py-4 text-sm font-black text-slate-800 shadow-sm transition-all hover:-translate-y-0.5 hover:border-sky-200 hover:bg-sky-50">Walk-In Borrow</a>
                    <a href="{{ route('admin.borrow_requests') }}" class="rounded-2xl border border-sky-100 bg-white px-5 py-4 text-sm font-black text-slate-800 shadow-sm transition-all hover:-translate-y-0.5 hover:border-sky-200 hover:bg-sky-50">Borrow Requests</a>
                    <a href="{{ route('admin.inventory') }}" class="rounded-2xl border border-sky-100 bg-white px-5 py-4 text-sm font-black text-slate-800 shadow-sm transition-all hover:-translate-y-0.5 hover:border-sky-200 hover:bg-sky-50">Inventory</a>
                    <a href="{{ route('admin.borrow_returns') }}" class="rounded-2xl border border-sky-100 bg-white px-5 py-4 text-sm font-black text-slate-800 shadow-sm transition-all hover:-translate-y-0.5 hover:border-sky-200 hover:bg-sky-50">Returns</a>
                </div>
            </div>
        </div>

        @livewire('admin.borrow-operations-dashboard')
    </div>
</x-admin-layout>
