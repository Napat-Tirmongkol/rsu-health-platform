<x-user-layout>
    <x-slot name="title">จองคิวนัดหมาย - RSU Health</x-slot>

    <div class="mb-8 space-y-2">
        <a href="{{ route('user.hub') }}" class="mb-2 flex items-center gap-1 text-xs font-bold text-slate-400">
            <i class="fa-solid fa-chevron-left"></i> กลับหน้าหลัก
        </a>
        <h2 class="text-2xl font-black tracking-tight text-slate-800">จองคิวนัดหมาย</h2>
        <p class="text-xs font-medium text-slate-500">เลือกแคมเปญ วันที่ และช่วงเวลาที่สะดวกเข้ารับบริการ</p>
    </div>

    <livewire:user.booking-calendar />
    <livewire:user.time-slot-picker />
</x-user-layout>
