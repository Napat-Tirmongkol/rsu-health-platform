<x-admin-layout>
    <x-slot name="title">Clinic Services Workspace</x-slot>
    @php($adminUser = Auth::guard('admin')->user())

    <div class="space-y-6">
        <section class="rounded-[24px] border border-[#e8eef7] bg-white p-6 shadow-sm">
            <div class="grid gap-6 xl:grid-cols-[1.2fr,0.9fr]">
                <div>
                    <p class="text-[10px] font-black uppercase tracking-[0.16em] text-[#2e9e63]">e-Campaign</p>
                    <h2 class="mt-3 text-3xl font-black tracking-tight text-slate-900">Clinic Services Workspace</h2>
                    <p class="mt-4 max-w-2xl text-sm font-semibold leading-relaxed text-slate-600">
                        ดูแคมเปญ การจอง และตารางรอบเวลาในบริบทเดียวกัน เพื่อให้ทีมคลินิกโฟกัสกับงานบริการผู้ป่วยได้ง่ายขึ้น
                    </p>
                </div>

                <div class="rounded-[20px] border border-[#e8eef7] bg-[#f8fafc] p-5">
                    <p class="text-[10px] font-black uppercase tracking-[0.14em] text-slate-400">Quick Access</p>
                    <div class="mt-4 grid grid-cols-2 gap-3">
                        @if (! $adminUser || $adminUser->hasActionAccess('campaign.manage'))
                            <a href="{{ route('admin.campaigns') }}" class="rounded-[16px] border border-[#e2e8f0] bg-white px-4 py-4 text-sm font-black text-slate-800 transition-all hover:border-[#c7e8d5] hover:bg-[#f0faf4]">Campaigns</a>
                        @endif
                        @if (! $adminUser || $adminUser->hasActionAccess('campaign.booking.manage'))
                            <a href="{{ route('admin.bookings') }}" class="rounded-[16px] border border-[#e2e8f0] bg-white px-4 py-4 text-sm font-black text-slate-800 transition-all hover:border-[#c7e8d5] hover:bg-[#f0faf4]">Bookings</a>
                        @endif
                        @if (! $adminUser || $adminUser->hasActionAccess('campaign.manage'))
                            <a href="{{ route('admin.time_slots') }}" class="rounded-[16px] border border-[#e2e8f0] bg-white px-4 py-4 text-sm font-black text-slate-800 transition-all hover:border-[#c7e8d5] hover:bg-[#f0faf4]">Time Slots</a>
                            <a href="{{ route('admin.reports') }}" class="rounded-[16px] border border-[#e2e8f0] bg-white px-4 py-4 text-sm font-black text-slate-800 transition-all hover:border-[#c7e8d5] hover:bg-[#f0faf4]">Reports</a>
                        @endif
                    </div>
                </div>
            </div>
        </section>

        <div class="grid gap-6 xl:grid-cols-3">
            <div class="rounded-[24px] border border-[#e8eef7] bg-white p-6 shadow-sm">
                <p class="text-[10px] font-black uppercase tracking-[0.14em] text-slate-400">Core Flow</p>
                <h3 class="mt-3 text-xl font-black text-slate-900">งานหลักของทีมคลินิก</h3>
                <div class="mt-5 space-y-3">
                    @if (! $adminUser || $adminUser->hasActionAccess('campaign.manage'))
                        <a href="{{ route('admin.campaigns') }}" class="flex items-center justify-between rounded-[16px] bg-[#f0faf4] px-5 py-4 text-sm font-bold text-[#2e9e63] transition-all hover:bg-[#e8f8f0]">
                            <span>จัดการแคมเปญและบริการ</span>
                            <i class="fa-solid fa-arrow-right"></i>
                        </a>
                    @endif
                    @if (! $adminUser || $adminUser->hasActionAccess('campaign.booking.manage'))
                        <a href="{{ route('admin.bookings') }}" class="flex items-center justify-between rounded-[16px] bg-[#f8fafc] px-5 py-4 text-sm font-bold text-slate-700 transition-all hover:bg-slate-100">
                            <span>ดูคิวจองและเปิด scanner</span>
                            <i class="fa-solid fa-arrow-right"></i>
                        </a>
                    @endif
                    @if (! $adminUser || $adminUser->hasActionAccess('campaign.manage'))
                        <a href="{{ route('admin.time_slots') }}" class="flex items-center justify-between rounded-[16px] bg-[#f8fafc] px-5 py-4 text-sm font-bold text-slate-700 transition-all hover:bg-slate-100">
                            <span>วางตารางรอบเวลา</span>
                            <i class="fa-solid fa-arrow-right"></i>
                        </a>
                    @endif
                </div>
            </div>

            <div class="rounded-[24px] border border-[#e8eef7] bg-white p-6 shadow-sm">
                <p class="text-[10px] font-black uppercase tracking-[0.14em] text-slate-400">History & Insight</p>
                <h3 class="mt-3 text-xl font-black text-slate-900">ติดตามผู้ใช้และผลลัพธ์</h3>
                <div class="mt-5 space-y-3">
                    @if (! $adminUser || $adminUser->hasActionAccess('campaign.manage'))
                        <a href="{{ route('admin.users') }}" class="flex items-center justify-between rounded-[16px] bg-[#f8fafc] px-5 py-4 text-sm font-bold text-slate-700 transition-all hover:bg-slate-100">
                            <span>ดูประวัติผู้รับบริการ</span>
                            <i class="fa-solid fa-arrow-right"></i>
                        </a>
                        <a href="{{ route('admin.reports') }}" class="flex items-center justify-between rounded-[16px] bg-[#f8fafc] px-5 py-4 text-sm font-bold text-slate-700 transition-all hover:bg-slate-100">
                            <span>รายงานและสรุปผล</span>
                            <i class="fa-solid fa-arrow-right"></i>
                        </a>
                    @endif
                    @if (! $adminUser || $adminUser->hasActionAccess('campaign.booking.manage'))
                        <a href="{{ route('staff.scan') }}" target="_blank" class="flex items-center justify-between rounded-[16px] bg-[#f0faf4] px-5 py-4 text-sm font-bold text-[#2e9e63] transition-all hover:bg-[#e8f8f0]">
                            <span>เปิด scanner หน้างาน</span>
                            <i class="fa-solid fa-arrow-up-right-from-square"></i>
                        </a>
                    @endif
                </div>
            </div>

            <div class="rounded-[24px] border border-[#e8eef7] bg-white p-6 shadow-sm">
                <p class="text-[10px] font-black uppercase tracking-[0.14em] text-slate-400">เหมาะกับใคร</p>
                <ul class="mt-5 space-y-4 text-sm font-semibold leading-relaxed text-slate-700">
                    <li class="flex gap-3"><i class="fa-solid fa-check mt-1 text-[#2e9e63]"></i><span>ผู้ดูแลแคมเปญ วัคซีน และบริการสุขภาพของคลินิก</span></li>
                    <li class="flex gap-3"><i class="fa-solid fa-check mt-1 text-[#2e9e63]"></i><span>ทีมที่ต้องติดตามจำนวนคิว การยืนยันการจอง และรอบเวลาในแต่ละวัน</span></li>
                    <li class="flex gap-3"><i class="fa-solid fa-check mt-1 text-[#2e9e63]"></i><span>เจ้าหน้าที่ที่ต้องสลับไปใช้ scanner ระหว่างช่วงบริการ</span></li>
                </ul>
            </div>
        </div>
    </div>
</x-admin-layout>
