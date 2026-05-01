<x-admin-layout>
    <x-slot name="title">จัดการแคมเปญ</x-slot>

    <div class="mb-6 flex justify-end">
        <a href="{{ route('staff.scan') }}" target="_blank" class="inline-flex items-center gap-3 rounded-2xl bg-emerald-600 px-5 py-3 text-xs font-black uppercase tracking-[0.18em] text-white shadow-lg shadow-emerald-100 transition-all hover:bg-emerald-700">
            <i class="fa-solid fa-qrcode"></i>
            <span>Open Scanner</span>
        </a>
    </div>

    @livewire('admin.campaign-manager')
</x-admin-layout>
