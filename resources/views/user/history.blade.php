<x-user-layout>
    <x-slot name="title">รายการนัดหมาย - RSU Medical</x-slot>

    <div class="min-h-screen bg-[#F8FAFF] pb-32">
        <!-- ── Clean White Header ── -->
        <header class="bg-white/95 backdrop-blur-xl sticky top-0 z-[60] px-6 py-5 flex items-center justify-between border-b border-slate-100 shadow-sm">
            <a href="{{ route('user.hub') }}" class="w-11 h-11 flex items-center justify-center bg-slate-50 rounded-2xl text-slate-400 active:scale-90 transition-all">
                <i class="fa-solid fa-chevron-left"></i>
            </a>
            <h1 class="text-lg font-black text-slate-900 tracking-tight">รายการนัดหมาย</h1>
            <div class="w-11 h-11"></div> <!-- Spacer -->
        </header>

        <main class="px-6 py-8">
            <!-- Header Section -->
            <div class="mb-8 text-left">
                <div class="flex items-center gap-3 mb-2">
                    <div class="w-2 h-7 bg-[#2e9e63] rounded-full"></div>
                    <h2 class="text-2xl font-black text-slate-900 tracking-tight">ประวัติการจอง</h2>
                </div>
                <p class="text-sm text-slate-400 font-bold ml-5">ตรวจสอบสถานะนัดหมายและเช็คอิน</p>
            </div>

            <!-- Livewire Component (Stats, Tabs, Cards, Modal) -->
            <livewire:user.my-bookings />

        </main>
    </div>

    <!-- Script for SweetAlert Integration (if needed) -->
    <script>
        window.addEventListener('swal:success', event => {
            Swal.fire({
                title: 'สำเร็จ!',
                text: event.detail.message,
                icon: 'success',
                confirmButtonColor: '#2e9e63',
                customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl px-8 font-black' }
            });
        });

        window.addEventListener('swal:error', event => {
            Swal.fire({
                title: 'ผิดพลาด!',
                text: event.detail.message,
                icon: 'error',
                confirmButtonColor: '#2e9e63',
                customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl px-8 font-black' }
            });
        });
    </script>
</x-user-layout>
