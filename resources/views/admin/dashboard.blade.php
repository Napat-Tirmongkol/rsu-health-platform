<x-admin-layout>
    <x-slot name="title">Platform Home</x-slot>

    @php($adminUser = Auth::guard('admin')->user())

    <div class="space-y-8">
        <section class="overflow-hidden rounded-[2.5rem] border border-slate-200 bg-white shadow-[0_24px_80px_rgba(15,23,42,0.08)]">
            <div class="grid gap-0 xl:grid-cols-[1.35fr,0.9fr]">
                <div class="bg-[radial-gradient(circle_at_top_left,rgba(16,185,129,0.16),transparent_32%),linear-gradient(180deg,#ffffff_0%,#f8fafc_100%)] px-8 py-9 xl:px-10 xl:py-10">
                    <p class="text-[10px] font-black uppercase tracking-[0.32em] text-slate-400">Platform Admin</p>
                    <h2 class="mt-4 max-w-3xl text-4xl font-black tracking-tight text-slate-950">RSU Operations Platform</h2>
                    <p class="mt-4 max-w-3xl text-sm font-bold leading-relaxed text-slate-600">
                        หน้าแรกนี้ออกแบบมาเพื่อให้แอดมินคลินิกที่ต้องสลับหลายระบบเริ่มจากบริบทก่อน แล้วค่อยลงไปยัง workflow ของแต่ละโมดูลโดยไม่เสียจังหวะการทำงาน
                    </p>

                    <div class="mt-8 grid gap-4 sm:grid-cols-3">
                        <div class="rounded-[1.75rem] border border-slate-200 bg-white/90 p-5 shadow-sm">
                            <p class="text-[10px] font-black uppercase tracking-[0.24em] text-slate-400">Context First</p>
                            <p class="mt-3 text-sm font-bold leading-relaxed text-slate-700">เริ่มจาก workspace ที่ตรงกับงานปัจจุบัน ก่อนเข้าเมนูย่อย</p>
                        </div>
                        <div class="rounded-[1.75rem] border border-slate-200 bg-white/90 p-5 shadow-sm">
                            <p class="text-[10px] font-black uppercase tracking-[0.24em] text-slate-400">Shared Control</p>
                            <p class="mt-3 text-sm font-bold leading-relaxed text-slate-700">สิทธิ์ ระบบตั้งค่า และทีมเจ้าหน้าที่ ยังอยู่ในจุดเดียวกัน</p>
                        </div>
                        <div class="rounded-[1.75rem] border border-slate-200 bg-white/90 p-5 shadow-sm">
                            <p class="text-[10px] font-black uppercase tracking-[0.24em] text-slate-400">Scalable Shell</p>
                            <p class="mt-3 text-sm font-bold leading-relaxed text-slate-700">พร้อมรองรับโมดูลใหม่โดยไม่ต้องรื้อ admin ทั้งก้อน</p>
                        </div>
                    </div>
                </div>

                <div class="border-t border-slate-100 bg-slate-50 px-8 py-9 xl:border-l xl:border-t-0 xl:px-10 xl:py-10">
                    <p class="text-[10px] font-black uppercase tracking-[0.32em] text-slate-400">Design Intent</p>
                    <div class="mt-5 space-y-4">
                        <div class="rounded-[1.75rem] border border-slate-200 bg-white p-5 shadow-sm">
                            <h3 class="text-base font-black text-slate-900">Modern Enterprise Quality</h3>
                            <p class="mt-2 text-sm font-bold leading-relaxed text-slate-600">นิ่ง อ่านง่าย และคุมข้อมูลจำนวนมากได้ดี โดยยังคงความ premium ของผลิตภัณฑ์สุขภาพ</p>
                        </div>
                        <div class="rounded-[1.75rem] border border-slate-200 bg-white p-5 shadow-sm">
                            <h3 class="text-base font-black text-slate-900">Built For Multi-System Admins</h3>
                            <p class="mt-2 text-sm font-bold leading-relaxed text-slate-600">ลดภาระในการจำว่าอยู่ระบบไหนด้วย workspace ที่ชัด และเมนูที่สะท้อนสิทธิ์จริง</p>
                        </div>
                        <div class="rounded-[1.75rem] border border-slate-200 bg-white p-5 shadow-sm">
                            <h3 class="text-base font-black text-slate-900">Flagship Shell</h3>
                            <p class="mt-2 text-sm font-bold leading-relaxed text-slate-600">ให้แพลตฟอร์มนี้เป็นฐานสำหรับต่อยอด e-Campaign, e-Borrow และโมดูลใหม่ในอนาคต</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="grid gap-6 {{ $adminUser?->hasModuleAccess('campaign') && $adminUser?->hasModuleAccess('borrow') ? 'xl:grid-cols-2' : 'xl:grid-cols-1' }}">
            @if ($adminUser?->hasModuleAccess('campaign'))
                <a href="{{ route('admin.workspace.campaign') }}" class="group overflow-hidden rounded-[2.25rem] border border-emerald-100 bg-white shadow-[0_20px_70px_rgba(16,185,129,0.08)] transition-all hover:-translate-y-1 hover:shadow-[0_28px_80px_rgba(16,185,129,0.16)]">
                    <div class="flex h-full flex-col justify-between gap-6 bg-[radial-gradient(circle_at_top_left,rgba(16,185,129,0.16),transparent_36%),linear-gradient(180deg,#ffffff_0%,#f8fafc_100%)] p-8">
                        <div>
                            <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-600 text-white shadow-lg shadow-emerald-200/80">
                                <i class="fa-solid fa-calendar-check text-xl"></i>
                            </div>
                            <p class="mt-6 text-[10px] font-black uppercase tracking-[0.28em] text-emerald-600">e-Campaign</p>
                            <h3 class="mt-3 text-3xl font-black tracking-tight text-slate-950">Clinic Services Workspace</h3>
                            <p class="mt-4 max-w-2xl text-sm font-bold leading-relaxed text-slate-600">
                                คุมแคมเปญ การจอง รอบเวลา รายงาน และ scanner ใน workspace เดียวสำหรับทีมบริการคลินิก
                            </p>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex flex-wrap gap-2">
                                <span class="rounded-full bg-white px-3 py-2 text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 shadow-sm">Bookings</span>
                                <span class="rounded-full bg-white px-3 py-2 text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 shadow-sm">Slots</span>
                                <span class="rounded-full bg-white px-3 py-2 text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 shadow-sm">Scanner</span>
                            </div>
                            <span class="inline-flex items-center gap-2 text-sm font-black text-slate-800">Open Workspace <i class="fa-solid fa-arrow-right transition-transform group-hover:translate-x-1"></i></span>
                        </div>
                    </div>
                </a>
            @endif

            @if ($adminUser?->hasModuleAccess('borrow'))
                <a href="{{ route('admin.workspace.borrow') }}" class="group overflow-hidden rounded-[2.25rem] border border-sky-100 bg-white shadow-[0_20px_70px_rgba(14,165,233,0.08)] transition-all hover:-translate-y-1 hover:shadow-[0_28px_80px_rgba(14,165,233,0.16)]">
                    <div class="flex h-full flex-col justify-between gap-6 bg-[radial-gradient(circle_at_top_left,rgba(14,165,233,0.16),transparent_36%),linear-gradient(180deg,#ffffff_0%,#f8fafc_100%)] p-8">
                        <div>
                            <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-sky-600 text-white shadow-lg shadow-sky-200/80">
                                <i class="fa-solid fa-box-open text-xl"></i>
                            </div>
                            <p class="mt-6 text-[10px] font-black uppercase tracking-[0.28em] text-sky-600">e-Borrow</p>
                            <h3 class="mt-3 text-3xl font-black tracking-tight text-slate-950">Borrow &amp; Inventory Workspace</h3>
                            <p class="mt-4 max-w-2xl text-sm font-bold leading-relaxed text-slate-600">
                                ดูคำขอยืม สต็อก การคืน ค่าปรับ และ flow หน้าจุดบริการโดยไม่ต้องกระโดดข้ามหน้าหลายชุด
                            </p>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex flex-wrap gap-2">
                                <span class="rounded-full bg-white px-3 py-2 text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 shadow-sm">Requests</span>
                                <span class="rounded-full bg-white px-3 py-2 text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 shadow-sm">Inventory</span>
                                <span class="rounded-full bg-white px-3 py-2 text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 shadow-sm">Returns</span>
                            </div>
                            <span class="inline-flex items-center gap-2 text-sm font-black text-slate-800">Open Workspace <i class="fa-solid fa-arrow-right transition-transform group-hover:translate-x-1"></i></span>
                        </div>
                    </div>
                </a>
            @endif
        </section>

        <section class="grid gap-6 xl:grid-cols-3">
            <div class="rounded-[2rem] border border-slate-200 bg-white p-7 shadow-sm">
                <p class="text-[10px] font-black uppercase tracking-[0.24em] text-slate-400">How To Use</p>
                <h3 class="mt-3 text-xl font-black text-slate-950">จังหวะการทำงานที่แนะนำ</h3>
                <ol class="mt-6 space-y-4 text-sm font-bold leading-relaxed text-slate-600">
                    <li>1. เริ่มจาก workspace ที่ตรงกับงานในช่วงนั้นก่อนทุกครั้ง</li>
                    <li>2. ใช้ sidebar ของ workspace นั้นเพื่อคุม flow หลักแทนการเปิดหลายหน้าปนกัน</li>
                    <li>3. กลับมาที่ Platform Home เมื่อต้องสลับไปอีกระบบหรือเช็กภาพรวม</li>
                </ol>
            </div>

            <div class="rounded-[2rem] border border-slate-200 bg-white p-7 shadow-sm">
                <p class="text-[10px] font-black uppercase tracking-[0.24em] text-slate-400">Shared Admin</p>
                <h3 class="mt-3 text-xl font-black text-slate-950">ส่วนกลางที่ยังใช้ร่วมกัน</h3>
                <div class="mt-6 space-y-3">
                    <a href="{{ route('admin.manage_staff') }}" class="flex items-center justify-between rounded-2xl bg-slate-50 px-5 py-4 text-sm font-bold text-slate-700 transition-all hover:bg-slate-100">
                        <span>ทีมเจ้าหน้าที่</span>
                        <i class="fa-solid fa-arrow-right"></i>
                    </a>
                    <a href="{{ route('admin.activity_logs') }}" class="flex items-center justify-between rounded-2xl bg-slate-50 px-5 py-4 text-sm font-bold text-slate-700 transition-all hover:bg-slate-100">
                        <span>Activity Logs</span>
                        <i class="fa-solid fa-arrow-right"></i>
                    </a>
                    @if ($adminUser?->hasFullPlatformAccess())
                        <a href="{{ route('admin.system_settings') }}" class="flex items-center justify-between rounded-2xl bg-slate-50 px-5 py-4 text-sm font-bold text-slate-700 transition-all hover:bg-slate-100">
                            <span>Integration Settings</span>
                            <i class="fa-solid fa-arrow-right"></i>
                        </a>
                    @endif
                </div>
            </div>

            <div class="overflow-hidden rounded-[2rem] border border-slate-900 bg-slate-950 text-white shadow-[0_24px_80px_rgba(15,23,42,0.22)]">
                <div class="bg-[radial-gradient(circle_at_top_left,rgba(16,185,129,0.24),transparent_34%)] p-7">
                    <p class="text-[10px] font-black uppercase tracking-[0.24em] text-white/45">Scalable Foundation</p>
                    <h3 class="mt-3 text-xl font-black">พร้อมสำหรับโมดูลถัดไป</h3>
                    <p class="mt-4 text-sm font-bold leading-relaxed text-white/72">
                        โครงนี้ออกแบบให้เพิ่มโมดูลใหม่ เช่น e-Insurance หรือ e-Lab ได้ด้วย pattern เดิม ทั้ง workspace, permission, navigation และ shared admin shell
                    </p>
                </div>
            </div>
        </section>
    </div>
</x-admin-layout>
