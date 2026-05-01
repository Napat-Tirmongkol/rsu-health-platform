<x-user-layout>
    <x-slot name="title">Contact - RSU Medical</x-slot>

    <main class="space-y-8 px-6 py-8">
        <div class="space-y-2">
            <a href="{{ route('user.hub') }}" class="mb-2 flex items-center gap-1 text-xs font-bold text-slate-400">
                <i class="fa-solid fa-chevron-left"></i> กลับหน้าหลัก
            </a>
            <h2 class="text-2xl font-black tracking-tight text-slate-900">Contact</h2>
            <p class="text-xs font-medium text-slate-500">ช่องทางติดต่อและข้อมูลการเดินทางของคลินิก</p>
        </div>

        <section class="grid gap-4">
            <a href="tel:027916000,4499" class="rounded-[2rem] border border-emerald-100 bg-emerald-50 p-5 shadow-sm">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="text-sm font-black text-emerald-700">โทรติดต่อคลินิก</p>
                        <p class="mt-1 text-lg font-black tracking-wide text-emerald-800">02-791-6000 ต่อ 4499</p>
                        <p class="mt-1 text-xs font-semibold text-emerald-600">แตะเพื่อโทรออกจากอุปกรณ์ของคุณ</p>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white text-emerald-600 shadow-sm">
                        <i class="fa-solid fa-phone-flip text-lg"></i>
                    </div>
                </div>
            </a>

            <div class="rounded-[2rem] border border-slate-100 bg-white p-5 shadow-sm">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-black text-slate-800">Location</p>
                        <p class="mt-2 text-sm font-semibold leading-relaxed text-slate-600">
                            อาคาร 12/1<br>
                            52/347 Building 12/1 Amphoe Muang Pathum Thani, Pathum Thani
                        </p>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-50 text-slate-500">
                        <i class="fa-solid fa-location-dot text-lg"></i>
                    </div>
                </div>
            </div>

            <div class="rounded-[2rem] border border-slate-100 bg-white p-5 shadow-sm">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-black text-slate-800">เวลาเปิดบริการ</p>
                        <div class="mt-2 space-y-1 text-sm font-semibold text-slate-600">
                            <p>จ-ศ 8.00-20.00</p>
                            <p>ส-อ 8.00-12.00</p>
                        </div>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-50 text-slate-500">
                        <i class="fa-regular fa-clock text-lg"></i>
                    </div>
                </div>
            </div>
        </section>

        <section class="rounded-[2rem] border border-slate-100 bg-white p-6 shadow-sm">
            <h3 class="text-sm font-black uppercase tracking-widest text-slate-900">ช่องทางเพิ่มเติม</h3>
            <div class="mt-4 space-y-3">
                <a href="{{ route('user.chat') }}" class="flex items-center justify-between rounded-2xl bg-slate-50 px-4 py-4 font-black text-slate-700">
                    <span><i class="fa-solid fa-comment-dots mr-2"></i>แชทกับเจ้าหน้าที่</span>
                    <i class="fa-solid fa-chevron-right text-xs"></i>
                </a>
            </div>
        </section>
    </main>
</x-user-layout>
