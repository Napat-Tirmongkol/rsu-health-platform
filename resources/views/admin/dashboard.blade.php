<x-admin-layout>
    <x-slot name="title">หน้าหลักแพลตฟอร์ม</x-slot>

    @php($adminUser = Auth::guard('admin')->user())

    <div class="space-y-6">
        <section class="rounded-[24px] border border-[#e8eef7] bg-white p-6 shadow-sm">
            <div class="grid gap-6 xl:grid-cols-[1.35fr,0.92fr]">
                <div>
                    <p class="text-[10px] font-black uppercase tracking-[0.16em] text-slate-400">Platform Overview</p>
                    <h2 class="mt-3 text-3xl font-black tracking-tight text-slate-900">RSU Operations Platform</h2>
                    <p class="mt-4 max-w-3xl text-sm font-semibold leading-relaxed text-slate-600">
                        หน้านี้เป็นจุดเริ่มต้นสำหรับเลือก workspace ให้ตรงกับงานที่กำลังทำอยู่ ช่วยลดการสลับความคิดระหว่างงานคลินิก งานยืมอุปกรณ์ และงานควบคุมระบบกลาง
                    </p>

                    <div class="mt-6 grid gap-4 lg:grid-cols-3">
                        <div class="rounded-[20px] border border-[#e8eef7] bg-[#f8fafc] p-5">
                            <p class="text-[10px] font-black uppercase tracking-[0.14em] text-slate-400">Context First</p>
                            <p class="mt-3 text-sm font-semibold leading-relaxed text-slate-700">เริ่มจาก workspace ที่ตรงกับงานปัจจุบัน แล้วค่อยเข้าเมนูย่อยที่เกี่ยวข้องจริง</p>
                        </div>
                        <div class="rounded-[20px] border border-[#e8eef7] bg-[#f8fafc] p-5">
                            <p class="text-[10px] font-black uppercase tracking-[0.14em] text-slate-400">Shared Control</p>
                            <p class="mt-3 text-sm font-semibold leading-relaxed text-slate-700">สิทธิ์ ระบบตั้งค่า และทีมเจ้าหน้าที่ยังถูกรวมไว้ในจุดเดียวเพื่อบริหารง่าย</p>
                        </div>
                        <div class="rounded-[20px] border border-[#e8eef7] bg-[#f8fafc] p-5">
                            <p class="text-[10px] font-black uppercase tracking-[0.14em] text-slate-400">Scalable Shell</p>
                            <p class="mt-3 text-sm font-semibold leading-relaxed text-slate-700">พร้อมรองรับโมดูลใหม่ในอนาคตโดยไม่ต้องรื้อ shell หรือ navigation ใหม่ทั้งก้อน</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-[24px] border border-[#e8eef7] bg-[#f8fafc] p-6">
                    <p class="text-[10px] font-black uppercase tracking-[0.16em] text-slate-400">Design Intent</p>
                    <div class="mt-5 space-y-4">
                        <div class="rounded-[18px] border border-[#eef2f7] bg-white p-5">
                            <h3 class="text-base font-black text-slate-900">คลินิกแต่ไม่ชวนตึง</h3>
                            <p class="mt-2 text-sm font-semibold leading-relaxed text-slate-600">เน้นโทนสะอาด น่าเชื่อถือ และมองข้อมูลได้เร็วแบบเครื่องมือหน้างานจริง</p>
                        </div>
                        <div class="rounded-[18px] border border-[#eef2f7] bg-white p-5">
                            <h3 class="text-base font-black text-slate-900">คุ้นกับของเดิม</h3>
                            <p class="mt-2 text-sm font-semibold leading-relaxed text-slate-600">ใช้ภาษาดีไซน์ของโปรเจกต์เก่า แต่เขียนใหม่บน Tailwind เพื่อให้ระบบดูแลระยะยาวง่ายขึ้น</p>
                        </div>
                        <div class="rounded-[18px] border border-[#eef2f7] bg-white p-5">
                            <h3 class="text-base font-black text-slate-900">รองรับหลายระบบ</h3>
                            <p class="mt-2 text-sm font-semibold leading-relaxed text-slate-600">e-Campaign, e-Borrow, Portal และ Chatbot สามารถอยู่ร่วมกันได้โดยไม่ทำให้เมนูรกหรือหลงบริบท</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="grid gap-6 {{ $adminUser?->hasModuleAccess('campaign') && $adminUser?->hasModuleAccess('borrow') ? 'xl:grid-cols-2' : 'xl:grid-cols-1' }}">
            @if ($adminUser?->hasModuleAccess('campaign'))
                <a href="{{ route('admin.workspace.campaign') }}" class="group rounded-[24px] border border-[#e8eef7] bg-white p-6 shadow-sm transition-all hover:-translate-y-1 hover:border-[#c7e8d5] hover:shadow-md">
                    <div class="flex h-full flex-col justify-between gap-6">
                        <div>
                            <div class="flex h-14 w-14 items-center justify-center rounded-[18px] bg-[#e8f8f0] text-[#2e9e63]">
                                <i class="fa-solid fa-syringe text-xl"></i>
                            </div>
                            <p class="mt-5 text-[10px] font-black uppercase tracking-[0.14em] text-[#2e9e63]">e-Campaign</p>
                            <h3 class="mt-3 text-3xl font-black tracking-tight text-slate-900">Clinic Services Workspace</h3>
                            <p class="mt-4 max-w-2xl text-sm font-semibold leading-relaxed text-slate-600">
                                ดูแคมเปญ การจอง การเช็กอิน และตารางรอบเวลาในบริบทเดียว เพื่อให้ทีมคลินิกโฟกัสกับงานบริการผู้ป่วยได้ง่ายขึ้น
                            </p>
                        </div>
                        <div class="flex items-center justify-between gap-4">
                            <div class="flex flex-wrap gap-2">
                                <span class="rounded-full border border-[#e2e8f0] bg-white px-3 py-2 text-[10px] font-black uppercase tracking-[0.14em] text-slate-500">Campaigns</span>
                                <span class="rounded-full border border-[#e2e8f0] bg-white px-3 py-2 text-[10px] font-black uppercase tracking-[0.14em] text-slate-500">Bookings</span>
                                <span class="rounded-full border border-[#e2e8f0] bg-white px-3 py-2 text-[10px] font-black uppercase tracking-[0.14em] text-slate-500">Scanner</span>
                            </div>
                            <span class="inline-flex items-center gap-2 text-sm font-black text-slate-800">Open Workspace <i class="fa-solid fa-arrow-right transition-transform group-hover:translate-x-1"></i></span>
                        </div>
                    </div>
                </a>
            @endif

            @if ($adminUser?->hasModuleAccess('borrow'))
                <a href="{{ route('admin.workspace.borrow') }}" class="group rounded-[24px] border border-[#e8eef7] bg-white p-6 shadow-sm transition-all hover:-translate-y-1 hover:border-[#c7e8d5] hover:shadow-md">
                    <div class="flex h-full flex-col justify-between gap-6">
                        <div>
                            <div class="flex h-14 w-14 items-center justify-center rounded-[18px] bg-[#e8f8f0] text-[#2e9e63]">
                                <i class="fa-solid fa-box-open text-xl"></i>
                            </div>
                            <p class="mt-5 text-[10px] font-black uppercase tracking-[0.14em] text-[#2e9e63]">e-Borrow</p>
                            <h3 class="mt-3 text-3xl font-black tracking-tight text-slate-900">Borrow &amp; Inventory Workspace</h3>
                            <p class="mt-4 max-w-2xl text-sm font-semibold leading-relaxed text-slate-600">
                                รวมคำขอยืม สต็อก การคืน ค่าปรับ และงานหน้าจุดบริการไว้ใน workspace เดียว เพื่อให้ทีมเดิน flow ได้ต่อเนื่องตั้งแต่รับคำขอจนปิดรายการ
                            </p>
                        </div>
                        <div class="flex items-center justify-between gap-4">
                            <div class="flex flex-wrap gap-2">
                                <span class="rounded-full border border-[#e2e8f0] bg-white px-3 py-2 text-[10px] font-black uppercase tracking-[0.14em] text-slate-500">Requests</span>
                                <span class="rounded-full border border-[#e2e8f0] bg-white px-3 py-2 text-[10px] font-black uppercase tracking-[0.14em] text-slate-500">Inventory</span>
                                <span class="rounded-full border border-[#e2e8f0] bg-white px-3 py-2 text-[10px] font-black uppercase tracking-[0.14em] text-slate-500">Returns</span>
                            </div>
                            <span class="inline-flex items-center gap-2 text-sm font-black text-slate-800">Open Workspace <i class="fa-solid fa-arrow-right transition-transform group-hover:translate-x-1"></i></span>
                        </div>
                    </div>
                </a>
            @endif
        </section>
    </div>
</x-admin-layout>
