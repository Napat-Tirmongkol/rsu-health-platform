<x-user-layout>
    <x-slot name="title">NCD Clinic - RSU Medical</x-slot>

    <main class="space-y-8 px-6 py-8">
        <div class="space-y-2">
            <a href="{{ route('user.hub') }}" class="mb-2 flex items-center gap-1 text-xs font-bold text-slate-400">
                <i class="fa-solid fa-chevron-left"></i> กลับหน้าหลัก
            </a>
            <h2 class="text-2xl font-black tracking-tight text-slate-900">NCD Clinic</h2>
            <p class="text-xs font-medium text-slate-500">บริการติดตามสุขภาพสำหรับผู้ที่ต้องการดูแลโรคไม่ติดต่อเรื้อรังอย่างต่อเนื่อง</p>
        </div>

        <section class="relative overflow-hidden rounded-[2rem] border border-cyan-100 bg-gradient-to-br from-cyan-50 via-white to-emerald-50 p-6 shadow-xl shadow-cyan-100">
            <div class="absolute -right-10 -top-10 h-36 w-36 rounded-full bg-cyan-200/40 blur-2xl"></div>
            <div class="absolute -bottom-12 left-0 h-32 w-32 rounded-full bg-emerald-200/40 blur-2xl"></div>

            <div class="relative">
                <div class="mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-white text-cyan-600 ring-1 ring-cyan-100">
                    <i class="fa-solid fa-heart-pulse text-2xl"></i>
                </div>
                <h3 class="text-xl font-black leading-tight text-slate-900">ดูแลต่อเนื่องแบบเป็นระบบ</h3>
                <p class="mt-2 max-w-[26rem] text-sm font-semibold leading-relaxed text-slate-600">
                    เหมาะสำหรับการติดตามความดัน เบาหวาน น้ำหนัก พฤติกรรมสุขภาพ และการนัดหมายซ้ำกับทีมคลินิก
                </p>
            </div>
        </section>

        <section class="space-y-4 rounded-[2rem] border border-slate-100 bg-white p-6 shadow-sm">
            <h3 class="text-sm font-black uppercase tracking-widest text-slate-900">เหมาะกับใคร</h3>
            <div class="space-y-3 text-sm font-semibold text-slate-600">
                <div class="flex items-start gap-3">
                    <i class="fa-solid fa-check mt-1 text-emerald-500"></i>
                    <p>ผู้ที่ต้องการติดตามค่าความดันโลหิตหรือระดับน้ำตาลเป็นประจำ</p>
                </div>
                <div class="flex items-start gap-3">
                    <i class="fa-solid fa-check mt-1 text-emerald-500"></i>
                    <p>ผู้ที่ต้องการวางแผนดูแลสุขภาพร่วมกับเจ้าหน้าที่คลินิก</p>
                </div>
                <div class="flex items-start gap-3">
                    <i class="fa-solid fa-check mt-1 text-emerald-500"></i>
                    <p>ผู้ที่ต้องการคำแนะนำเรื่องพฤติกรรมสุขภาพระยะยาว</p>
                </div>
            </div>
        </section>

        <section class="space-y-3 rounded-[2rem] border border-slate-100 bg-white p-6 shadow-sm">
            <h3 class="text-sm font-black uppercase tracking-widest text-slate-900">เริ่มต้นใช้งาน</h3>
            <a href="https://line.me/R/ti/p/@115vbibe?oat_content=url&ts=12222134" target="_blank" rel="noopener noreferrer" class="flex items-center justify-between rounded-2xl bg-emerald-50 px-4 py-4 font-black text-emerald-700">
                <span><i class="fa-brands fa-line mr-2"></i>ติดต่อ NCD Clinic ทาง LINE</span>
                <i class="fa-solid fa-arrow-up-right-from-square text-xs"></i>
            </a>
            <a href="{{ route('user.booking') }}" class="flex items-center justify-between rounded-2xl bg-slate-50 px-4 py-4 font-black text-slate-700">
                <span><i class="fa-solid fa-calendar-plus mr-2"></i>ดูบริการที่เปิดจอง</span>
                <i class="fa-solid fa-chevron-right text-xs"></i>
            </a>
        </section>

        <p class="text-center text-[11px] font-bold text-slate-400">
            บริการนี้ดำเนินการภายใต้ {{ $clinic?->name ?? 'RSU Medical Clinic' }}
        </p>
    </main>
</x-user-layout>
