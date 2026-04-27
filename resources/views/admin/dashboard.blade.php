<x-admin-layout>
    <x-slot name="title">ศูนย์ควบคุม (Dashboard)</x-slot>

    <div class="space-y-8 animate-in fade-in duration-700">
        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="bg-white p-6 rounded-[2rem] border border-slate-100 shadow-sm flex items-center gap-5 group hover:shadow-md transition-all">
                <div class="w-14 h-14 bg-emerald-50 rounded-2xl flex items-center justify-center text-emerald-600 group-hover:scale-110 transition-transform">
                    <i class="fa-solid fa-users text-2xl"></i>
                </div>
                <div>
                    <p class="text-[10px] text-slate-400 font-black uppercase tracking-widest mb-1">คนไข้ทั้งหมด</p>
                    <h4 class="text-2xl font-black text-slate-800">1,284</h4>
                </div>
            </div>

            <div class="bg-white p-6 rounded-[2rem] border border-slate-100 shadow-sm flex items-center gap-5 group hover:shadow-md transition-all">
                <div class="w-14 h-14 bg-blue-50 rounded-2xl flex items-center justify-center text-blue-600 group-hover:scale-110 transition-transform">
                    <i class="fa-solid fa-calendar-check text-2xl"></i>
                </div>
                <div>
                    <p class="text-[10px] text-slate-400 font-black uppercase tracking-widest mb-1">จองคิววันนี้</p>
                    <h4 class="text-2xl font-black text-slate-800">42</h4>
                </div>
            </div>

            <div class="bg-white p-6 rounded-[2rem] border border-slate-100 shadow-sm flex items-center gap-5 group hover:shadow-md transition-all">
                <div class="w-14 h-14 bg-purple-50 rounded-2xl flex items-center justify-center text-purple-600 group-hover:scale-110 transition-transform">
                    <i class="fa-solid fa-bullhorn text-2xl"></i>
                </div>
                <div>
                    <p class="text-[10px] text-slate-400 font-black uppercase tracking-widest mb-1">แคมเปญที่เปิดอยู่</p>
                    <h4 class="text-2xl font-black text-slate-800">5</h4>
                </div>
            </div>

            <div class="bg-white p-6 rounded-[2rem] border border-slate-100 shadow-sm flex items-center gap-5 group hover:shadow-md transition-all">
                <div class="w-14 h-14 bg-rose-50 rounded-2xl flex items-center justify-center text-rose-600 group-hover:scale-110 transition-transform">
                    <i class="fa-solid fa-clock-rotate-left text-2xl"></i>
                </div>
                <div>
                    <p class="text-[10px] text-slate-400 font-black uppercase tracking-widest mb-1">รอการอนุมัติ</p>
                    <h4 class="text-2xl font-black text-slate-800">18</h4>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Recent Activity -->
            <div class="lg:col-span-2 bg-white rounded-[2.5rem] border border-slate-100 shadow-sm overflow-hidden">
                <div class="px-8 py-6 border-b border-slate-50 flex items-center justify-between">
                    <h3 class="font-black text-slate-800 tracking-tight">กิจกรรมล่าสุด</h3>
                    <button class="text-[10px] text-[#2e9e63] font-black uppercase tracking-widest">ดูทั้งหมด</button>
                </div>
                <div class="p-8 space-y-6">
                    @for($i = 1; $i <= 5; $i++)
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-xl bg-slate-50 flex items-center justify-center text-slate-400 text-xs">
                                <i class="fa-solid fa-user-edit"></i>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-bold text-slate-800">Admin แก้ไขข้อมูลแคมเปญ "วัคซีนไข้หวัดใหญ่"</p>
                                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">2 นาทีที่แล้ว</p>
                            </div>
                        </div>
                    @endfor
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-[#2e9e63] rounded-[2.5rem] p-8 text-white relative overflow-hidden shadow-xl shadow-green-200">
                <div class="absolute -right-10 -top-10 w-40 h-40 bg-white/10 rounded-full blur-3xl"></div>
                <h3 class="font-black text-xl mb-6 relative z-10">ทางลัดด่วน</h3>
                <div class="space-y-3 relative z-10">
                    <button class="w-full py-4 bg-white/10 hover:bg-white/20 border border-white/10 rounded-2xl text-left px-6 font-bold text-sm transition-all flex items-center justify-between group">
                        <span>สร้างแคมเปญใหม่</span>
                        <i class="fa-solid fa-plus group-hover:rotate-90 transition-transform"></i>
                    </button>
                    <button class="w-full py-4 bg-white/10 hover:bg-white/20 border border-white/10 rounded-2xl text-left px-6 font-bold text-sm transition-all flex items-center justify-between group">
                        <span>อนุมัติการจองคิว</span>
                        <i class="fa-solid fa-check-double group-hover:scale-110 transition-transform"></i>
                    </button>
                    <button class="w-full py-4 bg-white/10 hover:bg-white/20 border border-white/10 rounded-2xl text-left px-6 font-bold text-sm transition-all flex items-center justify-between group">
                        <span>พิมพ์รายงานประจำวัน</span>
                        <i class="fa-solid fa-print group-hover:translate-x-1 transition-transform"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
