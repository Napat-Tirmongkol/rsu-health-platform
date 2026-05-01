<x-admin-layout>
    <x-slot name="title">Clinic Services Workspace</x-slot>
    @php($adminUser = Auth::guard('admin')->user())

    <div class="space-y-8">
        <section class="overflow-hidden rounded-[2.5rem] border border-emerald-100 bg-white shadow-[0_24px_80px_rgba(16,185,129,0.08)]">
            <div class="grid gap-0 xl:grid-cols-[1.25fr,0.95fr]">
                <div class="bg-[radial-gradient(circle_at_top_left,rgba(16,185,129,0.16),transparent_36%),linear-gradient(180deg,#ffffff_0%,#f8fafc_100%)] px-8 py-9 xl:px-10 xl:py-10">
                    <p class="text-[10px] font-black uppercase tracking-[0.3em] text-emerald-600">e-Campaign</p>
                    <h2 class="mt-4 text-3xl font-black tracking-tight text-slate-950">Clinic Services Workspace</h2>
                    <p class="mt-4 max-w-2xl text-sm font-bold leading-relaxed text-slate-600">
                        ดูแคมเปญ การจอง และตารางรอบเวลาในบริบทเดียวกัน เพื่อให้ทีมคลินิกโฟกัสกับงานบริการผู้ป่วยได้ง่ายและต่อเนื่อง
                    </p>
                </div>

                <div class="border-t border-emerald-100 bg-slate-50 px-8 py-9 xl:border-l xl:border-t-0 xl:px-10 xl:py-10">
                    <p class="text-[10px] font-black uppercase tracking-[0.3em] text-slate-400">Quick Access</p>
                    <div class="mt-5 grid grid-cols-2 gap-3">
                        @if (! $adminUser || $adminUser->hasActionAccess('campaign.manage'))
                            <a href="{{ route('admin.campaigns') }}" class="rounded-2xl border border-emerald-100 bg-white px-5 py-4 text-sm font-black text-slate-800 shadow-sm transition-all hover:-translate-y-0.5 hover:border-emerald-200 hover:bg-emerald-50">Campaigns</a>
                        @endif
                        @if (! $adminUser || $adminUser->hasActionAccess('campaign.booking.manage'))
                            <a href="{{ route('admin.bookings') }}" class="rounded-2xl border border-emerald-100 bg-white px-5 py-4 text-sm font-black text-slate-800 shadow-sm transition-all hover:-translate-y-0.5 hover:border-emerald-200 hover:bg-emerald-50">Bookings</a>
                        @endif
                        @if (! $adminUser || $adminUser->hasActionAccess('campaign.manage'))
                            <a href="{{ route('admin.time_slots') }}" class="rounded-2xl border border-emerald-100 bg-white px-5 py-4 text-sm font-black text-slate-800 shadow-sm transition-all hover:-translate-y-0.5 hover:border-emerald-200 hover:bg-emerald-50">Time Slots</a>
                            <a href="{{ route('admin.reports') }}" class="rounded-2xl border border-emerald-100 bg-white px-5 py-4 text-sm font-black text-slate-800 shadow-sm transition-all hover:-translate-y-0.5 hover:border-emerald-200 hover:bg-emerald-50">Reports</a>
                        @endif
                    </div>
                </div>
            </div>
        </section>

        <div class="grid gap-6 xl:grid-cols-3">
            <div class="rounded-[2rem] border border-slate-200 bg-white p-7 shadow-sm">
                <p class="text-[10px] font-black uppercase tracking-[0.24em] text-slate-400">Core Flow</p>
                <h3 class="mt-3 text-xl font-black text-slate-950">งานหลักของทีมคลินิก</h3>
                <div class="mt-6 space-y-3">
                    @if (! $adminUser || $adminUser->hasActionAccess('campaign.manage'))
                        <a href="{{ route('admin.campaigns') }}" class="flex items-center justify-between rounded-2xl bg-emerald-50 px-5 py-4 text-sm font-bold text-emerald-700 transition-all hover:bg-emerald-100">
                            <span>จัดการแคมเปญและบริการ</span>
                            <i class="fa-solid fa-arrow-right"></i>
                        </a>
                    @endif
                    @if (! $adminUser || $adminUser->hasActionAccess('campaign.booking.manage'))
                        <a href="{{ route('admin.bookings') }}" class="flex items-center justify-between rounded-2xl bg-slate-50 px-5 py-4 text-sm font-bold text-slate-700 transition-all hover:bg-slate-100">
                            <span>ดูคิวจองและเปิด scanner</span>
                            <i class="fa-solid fa-arrow-right"></i>
                        </a>
                    @endif
                    @if (! $adminUser || $adminUser->hasActionAccess('campaign.manage'))
                        <a href="{{ route('admin.time_slots') }}" class="flex items-center justify-between rounded-2xl bg-slate-50 px-5 py-4 text-sm font-bold text-slate-700 transition-all hover:bg-slate-100">
                            <span>วางตารางรอบเวลา</span>
                            <i class="fa-solid fa-arrow-right"></i>
                        </a>
                    @endif
                </div>
            </div>

            <div class="rounded-[2rem] border border-slate-200 bg-white p-7 shadow-sm">
                <p class="text-[10px] font-black uppercase tracking-[0.24em] text-slate-400">History & Insight</p>
                <h3 class="mt-3 text-xl font-black text-slate-950">ติดตามผู้ใช้และผลลัพธ์</h3>
                <div class="mt-6 space-y-3">
                    @if (! $adminUser || $adminUser->hasActionAccess('campaign.manage'))
                        <a href="{{ route('admin.users') }}" class="flex items-center justify-between rounded-2xl bg-slate-50 px-5 py-4 text-sm font-bold text-slate-700 transition-all hover:bg-slate-100">
                            <span>ดูประวัติผู้รับบริการ</span>
                            <i class="fa-solid fa-arrow-right"></i>
                        </a>
                        <a href="{{ route('admin.reports') }}" class="flex items-center justify-between rounded-2xl bg-slate-50 px-5 py-4 text-sm font-bold text-slate-700 transition-all hover:bg-slate-100">
                            <span>รายงานและสรุปผล</span>
                            <i class="fa-solid fa-arrow-right"></i>
                        </a>
                    @endif
                    @if (! $adminUser || $adminUser->hasActionAccess('campaign.booking.manage'))
                        <a href="{{ route('staff.scan') }}" target="_blank" class="flex items-center justify-between rounded-2xl bg-emerald-50 px-5 py-4 text-sm font-bold text-emerald-700 transition-all hover:bg-emerald-100">
                            <span>เปิด scanner หน้างาน</span>
                            <i class="fa-solid fa-arrow-up-right-from-square"></i>
                        </a>
                    @endif
                </div>
            </div>

            <div class="overflow-hidden rounded-[2rem] border border-slate-900 bg-slate-950 text-white shadow-[0_24px_80px_rgba(15,23,42,0.22)]">
                <div class="bg-[radial-gradient(circle_at_top_left,rgba(16,185,129,0.24),transparent_34%)] p-7">
                    <p class="text-[10px] font-black uppercase tracking-[0.24em] text-white/45">Working Context</p>
                    <h3 class="mt-3 text-xl font-black">เหมาะกับใคร</h3>
                    <ul class="mt-6 space-y-4 text-sm font-bold leading-relaxed text-white/78">
                        <li class="flex gap-3"><i class="fa-solid fa-check mt-1 text-emerald-300"></i><span>ผู้ดูแลแคมเปญ วัคซีน และบริการสุขภาพของคลินิก</span></li>
                        <li class="flex gap-3"><i class="fa-solid fa-check mt-1 text-emerald-300"></i><span>ทีมที่ต้องตามจำนวนคิว การยืนยันการจอง และรอบเวลาในแต่ละวัน</span></li>
                        <li class="flex gap-3"><i class="fa-solid fa-check mt-1 text-emerald-300"></i><span>เจ้าหน้าที่ที่ต้องสลับไปใช้ scanner ระหว่างช่วงบริการ</span></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
