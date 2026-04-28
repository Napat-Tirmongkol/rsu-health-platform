<x-admin-layout>
    <x-slot name="title">Clinic Services Workspace</x-slot>

    <div class="space-y-8">
        <div class="rounded-[2.5rem] border border-emerald-100 bg-gradient-to-br from-emerald-50 via-white to-green-50 p-8 text-slate-900 shadow-xl shadow-emerald-100">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                <div class="max-w-2xl">
                    <p class="text-[10px] font-black uppercase tracking-[0.3em] text-emerald-500">e-Campaign</p>
                    <h2 class="mt-3 text-3xl font-black tracking-tight">Clinic Services Workspace</h2>
                    <p class="mt-3 text-sm font-bold leading-relaxed text-slate-600">
                        ดูแคมเปญ การจอง และตารางรอบเวลาในบริบทเดียวกัน เพื่อให้ทีมคลินิกโฟกัสกับงานบริการผู้ป่วยได้ง่ายขึ้น
                    </p>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <a href="{{ route('admin.campaigns') }}" class="rounded-2xl border border-emerald-100 bg-white px-5 py-4 text-sm font-black text-slate-800 shadow-sm transition-all hover:-translate-y-0.5 hover:border-emerald-200 hover:bg-emerald-50">Campaigns</a>
                    <a href="{{ route('admin.bookings') }}" class="rounded-2xl border border-emerald-100 bg-white px-5 py-4 text-sm font-black text-slate-800 shadow-sm transition-all hover:-translate-y-0.5 hover:border-emerald-200 hover:bg-emerald-50">Bookings</a>
                    <a href="{{ route('admin.time_slots') }}" class="rounded-2xl border border-emerald-100 bg-white px-5 py-4 text-sm font-black text-slate-800 shadow-sm transition-all hover:-translate-y-0.5 hover:border-emerald-200 hover:bg-emerald-50">Time Slots</a>
                    <a href="{{ route('admin.reports') }}" class="rounded-2xl border border-emerald-100 bg-white px-5 py-4 text-sm font-black text-slate-800 shadow-sm transition-all hover:-translate-y-0.5 hover:border-emerald-200 hover:bg-emerald-50">Reports</a>
                </div>
            </div>
        </div>

        <div class="grid gap-6 xl:grid-cols-3">
            <div class="rounded-[2rem] bg-white p-7 shadow-sm shadow-slate-100">
                <p class="text-[10px] font-black uppercase tracking-[0.24em] text-slate-400">Core Flow</p>
                <h3 class="mt-3 text-xl font-black text-slate-900">งานหลักของทีมคลินิก</h3>
                <div class="mt-6 space-y-3">
                    <a href="{{ route('admin.campaigns') }}" class="flex items-center justify-between rounded-2xl bg-emerald-50 px-5 py-4 text-sm font-bold text-emerald-700 transition-all hover:bg-emerald-100">
                        <span>จัดการแคมเปญและบริการ</span>
                        <i class="fa-solid fa-arrow-right"></i>
                    </a>
                    <a href="{{ route('admin.bookings') }}" class="flex items-center justify-between rounded-2xl bg-slate-50 px-5 py-4 text-sm font-bold text-slate-700 transition-all hover:bg-slate-100">
                        <span>ดูคิวจองและเปิด scanner</span>
                        <i class="fa-solid fa-arrow-right"></i>
                    </a>
                    <a href="{{ route('admin.time_slots') }}" class="flex items-center justify-between rounded-2xl bg-slate-50 px-5 py-4 text-sm font-bold text-slate-700 transition-all hover:bg-slate-100">
                        <span>วางตารางรอบเวลา</span>
                        <i class="fa-solid fa-arrow-right"></i>
                    </a>
                </div>
            </div>

            <div class="rounded-[2rem] bg-white p-7 shadow-sm shadow-slate-100">
                <p class="text-[10px] font-black uppercase tracking-[0.24em] text-slate-400">History & Insight</p>
                <h3 class="mt-3 text-xl font-black text-slate-900">ติดตามผู้ใช้และผลลัพธ์</h3>
                <div class="mt-6 space-y-3">
                    <a href="{{ route('admin.users') }}" class="flex items-center justify-between rounded-2xl bg-slate-50 px-5 py-4 text-sm font-bold text-slate-700 transition-all hover:bg-slate-100">
                        <span>ดูประวัติผู้รับบริการ</span>
                        <i class="fa-solid fa-arrow-right"></i>
                    </a>
                    <a href="{{ route('admin.reports') }}" class="flex items-center justify-between rounded-2xl bg-slate-50 px-5 py-4 text-sm font-bold text-slate-700 transition-all hover:bg-slate-100">
                        <span>รายงานและสรุปผล</span>
                        <i class="fa-solid fa-arrow-right"></i>
                    </a>
                    <a href="{{ route('staff.scan') }}" target="_blank" class="flex items-center justify-between rounded-2xl bg-emerald-50 px-5 py-4 text-sm font-bold text-emerald-700 transition-all hover:bg-emerald-100">
                        <span>เปิด scanner หน้างาน</span>
                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                    </a>
                </div>
            </div>

            <div class="rounded-[2rem] bg-slate-900 p-7 text-white shadow-xl shadow-slate-200">
                <p class="text-[10px] font-black uppercase tracking-[0.24em] text-white/50">Working Context</p>
                <h3 class="mt-3 text-xl font-black">โหมดนี้เหมาะกับใคร</h3>
                <ul class="mt-6 space-y-4 text-sm font-bold leading-relaxed text-white/80">
                    <li class="flex gap-3"><i class="fa-solid fa-check mt-1 text-emerald-300"></i><span>ผู้ดูแลแคมเปญและวัคซีน</span></li>
                    <li class="flex gap-3"><i class="fa-solid fa-check mt-1 text-emerald-300"></i><span>ทีมที่ต้องตามจำนวนคิวและรอบเวลา</span></li>
                    <li class="flex gap-3"><i class="fa-solid fa-check mt-1 text-emerald-300"></i><span>เจ้าหน้าที่ที่ต้องสลับไปใช้ scanner ระหว่างวัน</span></li>
                </ul>
            </div>
        </div>
    </div>
</x-admin-layout>
