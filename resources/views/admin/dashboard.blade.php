<x-admin-layout>
    <x-slot name="title">Platform Home</x-slot>

    <div class="space-y-8">
        <div class="rounded-[2.5rem] bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 p-8 text-white shadow-xl shadow-slate-200">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                <div class="max-w-3xl">
                    <p class="text-[10px] font-black uppercase tracking-[0.3em] text-white/50">Platform Admin</p>
                    <h2 class="mt-3 text-3xl font-black tracking-tight">RSU Operations Platform</h2>
                    <p class="mt-3 text-sm font-bold leading-relaxed text-white/75">
                        หน้านี้เป็นจุดเริ่มต้นสำหรับเลือก workspace ให้ตรงกับงานที่กำลังทำอยู่ เพื่อให้แอดมินที่ดูหลายระบบไม่ต้องสลับความคิดไปมาระหว่างงานคลินิกและงานยืมอุปกรณ์
                    </p>
                </div>
                <div class="rounded-[2rem] border border-white/10 bg-white/5 p-5">
                    <p class="text-[10px] font-black uppercase tracking-[0.24em] text-white/50">Design Intent</p>
                    <p class="mt-3 max-w-xs text-sm font-bold leading-relaxed text-white/80">เริ่มจาก context ก่อน แล้วค่อยพาเข้า workflow ย่อยของแต่ละโมดูล จะช่วยลดเมนูรกและลดโอกาสเข้าเมนูผิดระบบ</p>
                </div>
            </div>
        </div>

        <div class="grid gap-6 {{ Auth::guard('admin')->user()?->hasModuleAccess('campaign') && Auth::guard('admin')->user()?->hasModuleAccess('borrow') ? 'xl:grid-cols-2' : 'xl:grid-cols-1' }}">
            @if (Auth::guard('admin')->user()?->hasModuleAccess('campaign'))
            <a href="{{ route('admin.workspace.campaign') }}" class="group rounded-[2rem] bg-white p-8 shadow-sm shadow-slate-100 transition-all hover:-translate-y-1 hover:shadow-xl hover:shadow-emerald-100/70">
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600 shadow-sm shadow-emerald-100">
                    <i class="fa-solid fa-calendar-check text-xl"></i>
                </div>
                <p class="mt-6 text-[10px] font-black uppercase tracking-[0.24em] text-emerald-500">e-Campaign</p>
                <h3 class="mt-3 text-2xl font-black text-slate-900">Clinic Services</h3>
                <p class="mt-3 text-sm font-bold leading-relaxed text-slate-500">แคมเปญ การจอง รอบเวลา ผู้รับบริการ รายงาน และ scanner สำหรับการเช็กอินหน้างาน</p>
                <div class="mt-6 flex items-center justify-between text-sm font-black text-slate-700">
                    <span>เปิด workspace</span>
                    <i class="fa-solid fa-arrow-right transition-transform group-hover:translate-x-1"></i>
                </div>
            </a>
            @endif

            @if (Auth::guard('admin')->user()?->hasModuleAccess('borrow'))
            <a href="{{ route('admin.workspace.borrow') }}" class="group rounded-[2rem] bg-white p-8 shadow-sm shadow-slate-100 transition-all hover:-translate-y-1 hover:shadow-xl hover:shadow-sky-100/70">
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-sky-50 text-sky-600 shadow-sm shadow-sky-100">
                    <i class="fa-solid fa-box-open text-xl"></i>
                </div>
                <p class="mt-6 text-[10px] font-black uppercase tracking-[0.24em] text-sky-500">e-Borrow</p>
                <h3 class="mt-3 text-2xl font-black text-slate-900">Borrow & Inventory</h3>
                <p class="mt-3 text-sm font-bold leading-relaxed text-slate-500">คำขอยืม สต็อก การคืน ค่าปรับ walk-in borrow และ dashboard ติดตามงานอุปกรณ์</p>
                <div class="mt-6 flex items-center justify-between text-sm font-black text-slate-700">
                    <span>เปิด workspace</span>
                    <i class="fa-solid fa-arrow-right transition-transform group-hover:translate-x-1"></i>
                </div>
            </a>
            @endif
        </div>

        <div class="grid gap-6 xl:grid-cols-3">
            <div class="rounded-[2rem] bg-white p-7 shadow-sm shadow-slate-100">
                <p class="text-[10px] font-black uppercase tracking-[0.24em] text-slate-400">How To Use</p>
                <h3 class="mt-3 text-xl font-black text-slate-900">การไหลงานที่แนะนำ</h3>
                <ol class="mt-6 space-y-4 text-sm font-bold leading-relaxed text-slate-600">
                    <li>1. เลือก workspace ให้ตรงกับประเภทงานก่อน</li>
                    <li>2. ใช้ sidebar ในบริบทนั้นเพื่อนำทางงานหลัก</li>
                    <li>3. กลับมาที่ Platform Home เมื่อต้องสลับไปอีกระบบ</li>
                </ol>
            </div>

            <div class="rounded-[2rem] bg-white p-7 shadow-sm shadow-slate-100">
                <p class="text-[10px] font-black uppercase tracking-[0.24em] text-slate-400">Shared Admin</p>
                <h3 class="mt-3 text-xl font-black text-slate-900">ส่วนกลางที่ยังใช้ร่วมกัน</h3>
                <div class="mt-6 space-y-3">
                    <a href="{{ route('admin.manage_staff') }}" class="flex items-center justify-between rounded-2xl bg-slate-50 px-5 py-4 text-sm font-bold text-slate-700 transition-all hover:bg-slate-100">
                        <span>ทีมเจ้าหน้าที่</span>
                        <i class="fa-solid fa-arrow-right"></i>
                    </a>
                    <a href="{{ route('admin.activity_logs') }}" class="flex items-center justify-between rounded-2xl bg-slate-50 px-5 py-4 text-sm font-bold text-slate-700 transition-all hover:bg-slate-100">
                        <span>Activity Logs</span>
                        <i class="fa-solid fa-arrow-right"></i>
                    </a>
                </div>
            </div>

            <div class="rounded-[2rem] bg-gradient-to-br from-amber-50 to-orange-50 p-7 shadow-sm shadow-orange-100/70">
                <p class="text-[10px] font-black uppercase tracking-[0.24em] text-amber-500">Scalable Foundation</p>
                <h3 class="mt-3 text-xl font-black text-slate-900">พร้อมสำหรับโมดูลถัดไป</h3>
                <p class="mt-4 text-sm font-bold leading-relaxed text-slate-600">
                    โครงนี้เปิดทางให้เราเพิ่มโมดูลใหม่ เช่น e-Insurance หรือ e-Lab โดยเพิ่ม workspace กับ navigation group ใหม่ได้โดยไม่ต้องรื้อ admin shell ทั้งก้อน
                </p>
            </div>
        </div>
    </div>
</x-admin-layout>
