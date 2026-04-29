<x-admin-layout>
    <x-slot name="title">Borrow & Inventory Workspace</x-slot>
    @php($adminUser = Auth::guard('admin')->user())

    <div class="space-y-8">
        <section class="overflow-hidden rounded-[2.5rem] border border-sky-100 bg-white shadow-[0_24px_80px_rgba(14,165,233,0.08)]">
            <div class="grid gap-0 xl:grid-cols-[1.25fr,0.95fr]">
                <div class="bg-[radial-gradient(circle_at_top_left,rgba(14,165,233,0.16),transparent_36%),linear-gradient(180deg,#ffffff_0%,#f8fafc_100%)] px-8 py-9 xl:px-10 xl:py-10">
                    <p class="text-[10px] font-black uppercase tracking-[0.3em] text-sky-600">e-Borrow</p>
                    <h2 class="mt-4 text-3xl font-black tracking-tight text-slate-950">Borrow Operations Command Center</h2>
                    <p class="mt-4 max-w-2xl text-sm font-bold leading-relaxed text-slate-600">
                        รวมคำขอยืม สต็อก การคืน และค่าปรับไว้ใน workspace เดียว เพื่อให้ทีมหน้าจุดบริการเดิน flow ได้ต่อเนื่องตั้งแต่รับคำขอจนปิดรายการ
                    </p>
                </div>

                <div class="border-t border-sky-100 bg-slate-50 px-8 py-9 xl:border-l xl:border-t-0 xl:px-10 xl:py-10">
                    <p class="text-[10px] font-black uppercase tracking-[0.3em] text-slate-400">Quick Access</p>
                    <div class="mt-5 grid grid-cols-2 gap-3">
                        @if (! $adminUser || $adminUser->hasActionAccess('borrow.inventory.manage'))
                            <a href="{{ route('admin.walk_in_borrow') }}" class="rounded-2xl border border-sky-100 bg-white px-5 py-4 text-sm font-black text-slate-800 shadow-sm transition-all hover:-translate-y-0.5 hover:border-sky-200 hover:bg-sky-50">Walk-In Borrow</a>
                        @endif
                        @if (! $adminUser || $adminUser->hasActionAccess('borrow.request.approve'))
                            <a href="{{ route('admin.borrow_requests') }}" class="rounded-2xl border border-sky-100 bg-white px-5 py-4 text-sm font-black text-slate-800 shadow-sm transition-all hover:-translate-y-0.5 hover:border-sky-200 hover:bg-sky-50">Borrow Requests</a>
                        @endif
                        @if (! $adminUser || $adminUser->hasActionAccess('borrow.inventory.manage'))
                            <a href="{{ route('admin.inventory') }}" class="rounded-2xl border border-sky-100 bg-white px-5 py-4 text-sm font-black text-slate-800 shadow-sm transition-all hover:-translate-y-0.5 hover:border-sky-200 hover:bg-sky-50">Inventory</a>
                        @endif
                        @if (! $adminUser || $adminUser->hasActionAccess('borrow.return.process'))
                            <a href="{{ route('admin.borrow_returns') }}" class="rounded-2xl border border-sky-100 bg-white px-5 py-4 text-sm font-black text-slate-800 shadow-sm transition-all hover:-translate-y-0.5 hover:border-sky-200 hover:bg-sky-50">Returns</a>
                        @endif
                    </div>
                </div>
            </div>
        </section>

        @livewire('admin.borrow-operations-dashboard')
    </div>
</x-admin-layout>
