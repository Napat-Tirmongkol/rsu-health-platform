<x-admin-layout>
    <x-slot name="title">Borrow & Inventory Workspace</x-slot>
    @php($adminUser = Auth::guard('admin')->user())

    <div class="space-y-6">
        <section class="rounded-[24px] border border-[#e8eef7] bg-white p-6 shadow-sm">
            <div class="grid gap-6 xl:grid-cols-[1.2fr,0.9fr]">
                <div>
                    <p class="text-[10px] font-black uppercase tracking-[0.16em] text-[#2e9e63]">e-Borrow</p>
                    <h2 class="mt-3 text-3xl font-black tracking-tight text-slate-900">Borrow Operations Command Center</h2>
                    <p class="mt-4 max-w-2xl text-sm font-semibold leading-relaxed text-slate-600">
                        รวมคำขอยืม สต็อก การคืน และค่าปรับไว้ใน workspace เดียว เพื่อให้ทีมหน้างานเดิน flow ได้ต่อเนื่องตั้งแต่รับคำขอจนปิดรายการ
                    </p>
                </div>

                <div class="rounded-[20px] border border-[#e8eef7] bg-[#f8fafc] p-5">
                    <p class="text-[10px] font-black uppercase tracking-[0.14em] text-slate-400">Quick Access</p>
                    <div class="mt-4 grid grid-cols-2 gap-3">
                        @if (! $adminUser || $adminUser->hasActionAccess('borrow.inventory.manage'))
                            <a href="{{ route('admin.walk_in_borrow') }}" class="rounded-[16px] border border-[#e2e8f0] bg-white px-4 py-4 text-sm font-black text-slate-800 transition-all hover:border-[#c7e8d5] hover:bg-[#f0faf4]">Walk-In Borrow</a>
                        @endif
                        @if (! $adminUser || $adminUser->hasActionAccess('borrow.request.approve'))
                            <a href="{{ route('admin.borrow_requests') }}" class="rounded-[16px] border border-[#e2e8f0] bg-white px-4 py-4 text-sm font-black text-slate-800 transition-all hover:border-[#c7e8d5] hover:bg-[#f0faf4]">Borrow Requests</a>
                        @endif
                        @if (! $adminUser || $adminUser->hasActionAccess('borrow.inventory.manage'))
                            <a href="{{ route('admin.inventory') }}" class="rounded-[16px] border border-[#e2e8f0] bg-white px-4 py-4 text-sm font-black text-slate-800 transition-all hover:border-[#c7e8d5] hover:bg-[#f0faf4]">Inventory</a>
                        @endif
                        @if (! $adminUser || $adminUser->hasActionAccess('borrow.return.process'))
                            <a href="{{ route('admin.borrow_returns') }}" class="rounded-[16px] border border-[#e2e8f0] bg-white px-4 py-4 text-sm font-black text-slate-800 transition-all hover:border-[#c7e8d5] hover:bg-[#f0faf4]">Returns</a>
                        @endif
                    </div>
                </div>
            </div>
        </section>

        @livewire('admin.borrow-operations-dashboard')
    </div>
</x-admin-layout>
