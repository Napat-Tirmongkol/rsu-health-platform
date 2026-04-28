<x-user-layout>
    <x-slot name="title">RSU Medical Hub</x-slot>

    <style>
        .animate-float { animation: float 6s ease-in-out infinite; }
        @keyframes float { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-10px); } }
        .custom-scrollbar::-webkit-scrollbar { display: none; }
    </style>

    <script>
        function showInsurance() { document.getElementById('insDetailModal').classList.remove('hidden'); }
        function hideInsurance() { document.getElementById('insDetailModal').classList.add('hidden'); }
    </script>

    <main class="px-6 pt-8 space-y-8">
        <div class="px-1">
            <p class="text-slate-400 text-[10px] font-black uppercase tracking-[0.3em] mb-2 opacity-70">
                {{ $thaiDate }}
            </p>
            <div class="flex items-end justify-between">
                <h2 class="text-3xl font-black text-slate-900 tracking-tight">Health Hub</h2>
            </div>
        </div>

        <div onclick="window.location.href='{{ route('user.profile') }}'"
            class="relative overflow-hidden bg-gradient-to-br from-[#2e9e63] via-[#10b981] to-[#2e9e63] rounded-[3rem] p-8 shadow-[0_25px_50px_-12px_rgba(46,158,99,0.3)] group active:scale-[0.97] transition-all cursor-pointer">
            <div class="absolute -right-6 -top-6 w-48 h-48 bg-white/10 rounded-full blur-3xl group-hover:scale-150 transition-transform duration-1000"></div>
            <div class="absolute -left-12 -bottom-12 w-56 h-56 bg-emerald-400/20 rounded-full blur-3xl"></div>

            <div class="relative z-10">
                <div class="flex items-center gap-5 mb-10">
                    <div class="relative">
                        <div class="w-20 h-20 rounded-[2rem] overflow-hidden border-2 border-white/20 shadow-2xl bg-white/20 flex items-center justify-center">
                            @if($user->profile_photo_path)
                                <img src="{{ Storage::url($user->profile_photo_path) }}" class="w-full h-full object-cover">
                            @else
                                <i class="fa-solid fa-user text-3xl text-white/50"></i>
                            @endif
                        </div>
                        <div class="absolute -bottom-1 -right-1 w-6 h-6 bg-emerald-400 rounded-full border-4 border-[#237a4c] animate-pulse"></div>
                    </div>
                    <div class="flex-1 min-w-0 text-left">
                        <p class="text-emerald-50 text-sm font-bold mb-1">สวัสดีครับ</p>
                        <h3 class="text-white text-2xl font-black tracking-tight leading-tight mb-1 truncate">
                            {{ $user->name }}
                        </h3>
                        <p class="text-emerald-100/60 text-[11px] font-black uppercase tracking-[0.1em]">ID: {{ $user->username }}</p>
                    </div>
                    <div class="w-12 h-12 bg-white/15 backdrop-blur-xl rounded-2xl flex items-center justify-center border border-white/20 shadow-xl group-hover:translate-x-1 transition-transform">
                        <i class="fa-solid fa-chevron-right text-white text-sm"></i>
                    </div>
                </div>

                <div class="relative flex items-center justify-between pt-6 border-t border-white/10">
                    <div class="flex items-center gap-3 text-white">
                        <i class="fa-solid fa-graduation-cap text-emerald-200 text-sm"></i>
                        <p class="text-emerald-50 text-[11px] font-bold tracking-wide truncate max-w-[200px]">
                            RSU Medical Clinic · Verified
                        </p>
                    </div>
                    <div class="bg-emerald-400/20 border border-emerald-400/30 rounded-full px-4 py-1.5 backdrop-blur-md flex items-center gap-2">
                        <i class="fa-solid fa-circle-check text-emerald-300 text-[10px]"></i>
                        <span class="text-emerald-200 text-[9px] font-black uppercase tracking-[0.15em]">ACTIVE</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <button onclick="window.location.href='{{ route('user.history') }}'"
                class="bg-white rounded-[2.5rem] p-6 border border-slate-100 shadow-[0_15px_30px_rgba(0,0,0,0.03)] flex flex-col items-center text-center active:scale-95 transition-all group">
                <div class="w-12 h-12 bg-green-50 rounded-2xl flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                    <i class="fa-solid fa-calendar-check text-green-600 text-lg"></i>
                </div>
                <p class="font-black text-xl text-slate-900 mb-0.5">{{ $bookingList->count() }}</p>
                <p class="text-slate-400 text-[10px] font-bold uppercase tracking-widest">การเข้ารับบริการ</p>
            </button>
            <button onclick="window.location.href='{{ route('user.borrow.history') }}'"
                class="bg-white rounded-[2.5rem] p-6 border border-slate-100 shadow-[0_15px_30px_rgba(0,0,0,0.03)] flex flex-col items-center text-center active:scale-95 transition-all group">
                <div class="w-12 h-12 bg-orange-50 rounded-2xl flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                    <i class="fa-solid fa-boxes-stacked text-orange-600 text-lg"></i>
                </div>
                <p class="font-black text-xl text-slate-900 mb-0.5">{{ $borrowCount }}</p>
                <p class="text-slate-400 text-[10px] font-bold uppercase tracking-widest">รายการยืมของ</p>
            </button>
        </div>

        <div class="space-y-4">
            <div class="flex items-center justify-between px-1">
                <h3 class="text-slate-900 font-black text-sm uppercase tracking-widest">Main Menu</h3>
                <button class="text-green-600 text-[10px] font-black uppercase tracking-widest bg-green-50 px-3 py-1.5 rounded-full">All Services</button>
            </div>

            <div class="bg-white rounded-[3rem] border border-slate-100 shadow-[0_20px_50px_rgba(0,0,0,0.04)] p-6 pt-8">
                <div class="grid grid-cols-2 gap-4 mb-8">
                    <a href="{{ route('user.booking') }}"
                        class="relative flex flex-col items-start p-6 rounded-[2.2rem] bg-[#2e9e63] shadow-[0_15px_30px_rgba(46,158,99,0.25)] active:scale-95 transition-all text-white overflow-hidden text-left group">
                        <div class="absolute -right-4 -top-4 w-16 h-16 bg-white/10 rounded-full blur-xl group-hover:scale-150 transition-transform"></div>
                        <div class="w-11 h-11 rounded-2xl bg-white/20 flex items-center justify-center mb-4 border border-white/20">
                            <i class="fa-solid fa-calendar-plus text-white text-base"></i>
                        </div>
                        <p class="text-[13px] font-black leading-tight tracking-wide">จองคิว /<br>แคมเปญ</p>
                    </a>

                    <a href="{{ route('user.borrow.index') }}"
                        class="relative flex flex-col items-start p-6 rounded-[2.2rem] bg-indigo-50 border border-indigo-100 shadow-sm active:scale-95 transition-all text-indigo-600 group">
                        @if($borrowCount > 0)
                            <span class="absolute top-4 right-4 w-6 h-6 bg-red-500 text-white text-[10px] font-black rounded-full flex items-center justify-center border-2 border-white shadow-lg animate-bounce">{{ $borrowCount }}</span>
                        @endif
                        <div class="w-11 h-11 rounded-2xl bg-white flex items-center justify-center mb-4 shadow-sm border border-indigo-50 group-hover:scale-110 transition-transform">
                            <i class="fa-solid fa-box-open text-indigo-500 text-base"></i>
                        </div>
                        <p class="text-[13px] font-black leading-tight tracking-wide text-slate-800">ยืมอุปกรณ์<br>และติดตามรายการ</p>
                    </a>
                </div>

                <div class="pt-6 border-t border-slate-50">
                    <p class="text-slate-400 text-[9px] font-black uppercase tracking-[0.3em] mb-5 text-center">External Services</p>
                    <div class="grid grid-cols-4 gap-4">
                        <a href="https://page.line.me/641mrzwm?oat_content=url&openQrModal=true" target="_blank" rel="noopener noreferrer" class="flex flex-col items-center gap-2 active:scale-90 transition-all">
                            <div class="w-12 h-12 bg-purple-50 rounded-2xl flex items-center justify-center text-purple-600 shadow-sm">
                                <i class="fa-solid fa-comment-dots text-lg"></i>
                            </div>
                            <span class="text-slate-500 text-[8px] font-black text-center leading-tight uppercase tracking-widest">Counseling</span>
                        </a>
                        <a href="{{ route('user.services.ncd-clinic') }}" class="flex flex-col items-center gap-2 active:scale-90 transition-all">
                            <div class="w-12 h-12 bg-cyan-50 rounded-2xl flex items-center justify-center text-cyan-600 shadow-sm">
                                <i class="fa-solid fa-heart-pulse text-lg"></i>
                            </div>
                            <span class="text-slate-500 text-[8px] font-black text-center leading-tight uppercase tracking-widest">NCD Clinic</span>
                        </a>
                        <a href="{{ route('user.services.contact') }}" class="flex flex-col items-center gap-2 active:scale-90 transition-all">
                            <div class="w-12 h-12 bg-emerald-50 rounded-2xl flex items-center justify-center text-emerald-600 shadow-sm">
                                <i class="fa-solid fa-phone-flip text-base"></i>
                            </div>
                            <span class="text-slate-500 text-[8px] font-black text-center leading-tight uppercase tracking-widest">Contact</span>
                        </a>
                        <a href="{{ route('user.services.help') }}" class="flex flex-col items-center gap-2 active:scale-90 transition-all">
                            <div class="w-12 h-12 bg-orange-50 rounded-2xl flex items-center justify-center text-orange-600 shadow-sm">
                                <i class="fa-solid fa-circle-question text-lg"></i>
                            </div>
                            <span class="text-slate-500 text-[8px] font-black text-center leading-tight uppercase tracking-widest">Help</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="space-y-4">
            <p class="text-slate-500 text-[10px] font-black uppercase tracking-[0.3em] px-1 text-left">Upcoming Appointments</p>
            <div class="bg-white rounded-[3rem] border border-slate-100 shadow-[0_20px_50px_rgba(0,0,0,0.04)] overflow-hidden">
                <div class="flex items-center justify-between px-7 pt-7 pb-4 border-b border-slate-50">
                    <h3 class="text-slate-900 font-black text-xs uppercase tracking-widest">Latest Queue</h3>
                    <span class="bg-green-50 text-green-600 text-[9px] font-black px-3 py-1 rounded-full uppercase">{{ $upcomingCount }} Active</span>
                </div>
                <div class="p-6 space-y-4">
                    @forelse($bookingList->whereIn('status', ['pending', 'confirmed'])->take(2) as $booking)
                        <div class="bg-slate-50/50 rounded-[2.2rem] p-6 border border-slate-100 relative group active:scale-[0.98] transition-all text-left">
                            <div class="flex items-start justify-between mb-5">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 bg-white rounded-2xl flex items-center justify-center shadow-sm text-green-600 border border-slate-100">
                                        <i class="fa-solid fa-calendar-check text-base"></i>
                                    </div>
                                    <div>
                                        <h4 class="text-slate-900 font-black text-sm leading-tight mb-1.5">{{ $booking->campaign->title }}</h4>
                                        <div class="flex items-center gap-2">
                                            <span class="w-1.5 h-1.5 {{ $booking->status === 'confirmed' ? 'bg-green-500' : 'bg-amber-500' }} rounded-full"></span>
                                            <p class="text-slate-400 text-[9px] font-black uppercase tracking-[0.1em]">{{ $booking->status }} Slot</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="bg-white p-4 rounded-2xl border border-slate-100/50 shadow-sm flex items-center gap-3">
                                    <i class="fa-regular fa-calendar text-green-500 text-xs"></i>
                                    <div>
                                        <p class="text-slate-400 text-[8px] font-black uppercase tracking-widest leading-none mb-1">Date</p>
                                        <p class="text-slate-800 font-black text-xs leading-none">{{ $booking->slot->date->format('d M Y') }}</p>
                                    </div>
                                </div>
                                <div class="bg-white p-4 rounded-2xl border border-slate-100/50 shadow-sm flex items-center gap-3">
                                    <i class="fa-regular fa-clock text-green-500 text-xs"></i>
                                    <div>
                                        <p class="text-slate-400 text-[8px] font-black uppercase tracking-widest leading-none mb-1">Time</p>
                                        <p class="text-slate-800 font-black text-xs leading-none">{{ \Carbon\Carbon::parse($booking->slot->start_time)->format('H:i') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="py-12 text-center text-slate-300 font-bold text-sm italic">ยังไม่มีนัดหมายที่กำลังจะมาถึง</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="space-y-4">
            <p class="text-slate-500 text-[10px] font-black uppercase tracking-[0.3em] px-1 text-left">Medical Coverage</p>

            @if($insurance)
                <div class="bg-slate-900 rounded-[3rem] p-8 shadow-2xl relative overflow-hidden premium-shadow text-left">
                    <div class="absolute -right-8 -bottom-8 w-40 h-40 bg-white/5 rounded-full blur-3xl"></div>
                    <div class="flex items-start justify-between mb-8">
                        <div>
                            <h4 class="text-white font-black text-sm tracking-tight">Health Insurance</h4>
                            <p class="text-white/30 text-[9px] font-black uppercase tracking-[0.2em] mt-1.5 leading-none">{{ $insurance->member_status }}</p>
                        </div>
                        <span class="px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-widest {{ $insurance->insurance_status === 'Active' ? 'bg-emerald-500/20 text-emerald-400' : 'bg-rose-500/20 text-rose-400' }}">
                            {{ $insurance->insurance_status }}
                        </span>
                    </div>
                    <div class="mb-6">
                        <p class="text-white/30 text-[9px] font-black uppercase tracking-[0.2em] mb-1">Policy No.</p>
                        <p class="text-white/70 text-xs font-black tracking-widest">{{ $insurance->policy_number ?? 'RSU-INS-7782' }}</p>
                    </div>
                    <div class="flex items-end justify-between pt-6 border-t border-white/10 relative z-10">
                        <div>
                            <p class="text-white/30 text-[8px] font-black uppercase tracking-[0.2em] mb-1.5">Primary Holder</p>
                            <p class="text-white text-[11px] font-black uppercase tracking-wider truncate max-w-[180px]">{{ $user->name }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-white/30 text-[8px] font-black uppercase tracking-[0.2em] mb-1.5">Coverage</p>
                            <p class="text-white text-[11px] font-black uppercase tracking-widest">Active</p>
                        </div>
                    </div>
                </div>
            @else
                <div class="bg-slate-900 rounded-[3rem] p-8 shadow-2xl relative overflow-hidden premium-shadow text-left">
                    <div class="absolute -right-8 -bottom-8 w-40 h-40 bg-white/5 rounded-full blur-3xl"></div>
                    <div class="flex flex-col items-center justify-center py-6 text-center gap-3">
                        <div class="w-14 h-14 rounded-2xl bg-white/10 flex items-center justify-center text-white/30 text-2xl">
                            <i class="fa-solid fa-shield-xmark"></i>
                        </div>
                        <p class="text-white/50 text-xs font-black uppercase tracking-widest">ไม่พบข้อมูลประกันในระบบ</p>
                    </div>
                </div>
            @endif

            <button onclick="showInsurance()" class="w-full bg-[#2e9e63] rounded-[2.2rem] p-6 shadow-xl shadow-green-100 relative overflow-hidden group cursor-pointer text-left">
                <div class="absolute -right-4 -top-4 w-20 h-20 bg-white/10 rounded-full blur-xl group-hover:scale-150 transition-transform"></div>
                <div class="flex items-center gap-4 relative z-10">
                    <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center text-white shrink-0">
                        <i class="fa-solid fa-shield-heart"></i>
                    </div>
                    <div class="flex-1">
                        <h5 class="text-white font-black text-xs uppercase tracking-widest">ความคุ้มครองและวิธีใช้สิทธิ์</h5>
                        <p class="text-white/70 text-[11px] mt-0.5">กดเพื่อดูรายละเอียดประกันและช่องทางติดต่อ</p>
                    </div>
                    <i class="fa-solid fa-chevron-right text-white/50 text-xs shrink-0"></i>
                </div>
            </button>
        </div>

        <footer class="pt-10 pb-16 text-center space-y-2 opacity-30">
            <p class="text-slate-400 text-[10px] font-black uppercase tracking-[0.3em]">© 2568 RSU Medical Services</p>
            <div class="flex items-center justify-center gap-3">
                <span class="w-1 h-1 bg-slate-400 rounded-full"></span>
                <p class="text-slate-400 text-[9px] font-bold uppercase tracking-widest">Hospital OS v3.2</p>
                <span class="w-1 h-1 bg-slate-400 rounded-full"></span>
            </div>
        </footer>
    </main>

    <div id="insDetailModal" class="fixed inset-0 z-[100] hidden flex items-center justify-center p-6">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-md" onclick="hideInsurance()"></div>
        <div class="relative bg-white w-full max-w-[340px] rounded-[3rem] p-8 shadow-2xl overflow-y-auto max-h-[80vh]">
            <h3 class="text-xl font-black mb-4">รายละเอียดความคุ้มครอง</h3>
            <div class="space-y-4 text-left text-sm text-slate-600">
                <p>- คุ้มครองอุบัติเหตุ 24 ชั่วโมง</p>
                <p>- วงเงินค่ารักษาพยาบาลตามสิทธิ์</p>
                <p>- แสดงบัตรนี้พร้อมบัตรประชาชนเพื่อใช้สิทธิ์</p>
            </div>
            <button onclick="hideInsurance()" class="w-full mt-8 py-4 bg-slate-900 text-white font-black rounded-2xl">รับทราบ</button>
        </div>
    </div>

    @if($announcements->count() > 0)
    @endif
</x-user-layout>
