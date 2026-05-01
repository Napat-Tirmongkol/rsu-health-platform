<x-user-layout>
    <x-slot name="title">รายการนัดหมาย - RSU Medical</x-slot>

    <div class="min-h-screen bg-[#F8FAFF] pb-32">
        <header class="sticky top-0 z-[60] flex items-center justify-between border-b border-slate-100 bg-white/95 px-6 py-5 shadow-sm backdrop-blur-xl">
            <a href="{{ route('user.hub') }}" class="flex h-11 w-11 items-center justify-center rounded-2xl bg-slate-50 text-slate-400 transition-all active:scale-90">
                <i class="fa-solid fa-chevron-left"></i>
            </a>
            <h1 class="text-lg font-black tracking-tight text-slate-900">รายการนัดหมาย</h1>
            <div class="h-11 w-11"></div>
        </header>

        <main class="px-6 py-8">
            <div class="mb-8 text-left">
                <div class="mb-2 flex items-center gap-3">
                    <div class="h-7 w-2 rounded-full bg-[#2e9e63]"></div>
                    <h2 class="text-2xl font-black tracking-tight text-slate-900">ประวัติการจอง</h2>
                </div>
                <p class="ml-5 text-sm font-bold text-slate-400">ตรวจสอบสถานะนัดหมาย ดูรายละเอียด และจัดการคิวที่ยังรอเข้ารับบริการ</p>
            </div>

            <livewire:user.my-bookings />
        </main>
    </div>

    <script>
        window.addEventListener('swal:success', event => {
            Swal.fire({
                title: 'สำเร็จ',
                text: event.detail.message,
                icon: 'success',
                confirmButtonColor: '#2e9e63',
                customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl px-8 font-black' }
            });
        });

        window.addEventListener('swal:error', event => {
            Swal.fire({
                title: 'เกิดข้อผิดพลาด',
                text: event.detail.message,
                icon: 'error',
                confirmButtonColor: '#2e9e63',
                customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl px-8 font-black' }
            });
        });
    </script>
</x-user-layout>
