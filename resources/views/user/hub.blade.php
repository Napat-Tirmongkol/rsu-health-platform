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

    <main class="space-y-8 px-6 pt-8">
        <div class="px-1">
            <p class="mb-2 text-[10px] font-black uppercase tracking-[0.3em] text-slate-400 opacity-70">
                {{ $thaiDate }}
            </p>
            <div class="flex items-end justify-between">
                <h2 class="text-3xl font-black tracking-tight text-slate-900">Health Hub</h2>
            </div>
        </div>

        <div onclick="window.location.href='{{ route('user.profile') }}'"
            class="group relative cursor-pointer overflow-hidden rounded-[3rem] bg-gradient-to-br from-[#2e9e63] via-[#10b981] to-[#2e9e63] p-8 shadow-[0_25px_50px_-12px_rgba(46,158,99,0.3)] transition-all active:scale-[0.97]">
            <div class="absolute -right-6 -top-6 h-48 w-48 rounded-full bg-white/10 blur-3xl transition-transform duration-1000 group-hover:scale-150"></div>
            <div class="absolute -bottom-12 -left-12 h-56 w-56 rounded-full bg-emerald-400/20 blur-3xl"></div>

            <div class="relative z-10">
                <div class="mb-10 flex items-center gap-5">
                    <div class="relative">
                        <div class="flex h-20 w-20 items-center justify-center overflow-hidden rounded-[2rem] border-2 border-white/20 bg-white/20 shadow-2xl">
                            @if($user->profile_photo_path)
                                <img src="{{ Storage::url($user->profile_photo_path) }}" class="h-full w-full object-cover">
                            @else
                                <i class="fa-solid fa-user text-3xl text-white/50"></i>
                            @endif
                        </div>
                        <div class="absolute -bottom-1 -right-1 h-6 w-6 animate-pulse rounded-full border-4 border-[#237a4c] bg-emerald-400"></div>
                    </div>
                    <div class="min-w-0 flex-1 text-left">
                        <p class="mb-1 text-sm font-bold text-emerald-50">สวัสดีครับ</p>
                        <h3 class="mb-1 truncate text-2xl font-black leading-tight tracking-tight text-white">
                            {{ $user->name }}
                        </h3>
                        <p class="text-[11px] font-black uppercase tracking-[0.1em] text-emerald-100/80">
                            {{ $user->identity_label }}: {{ $user->identity_value }}
                        </p>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl border border-white/20 bg-white/15 shadow-xl backdrop-blur-xl transition-transform group-hover:translate-x-1">
                        <i class="fa-solid fa-chevron-right text-sm text-white"></i>
                    </div>
                </div>

                <div class="relative flex items-center justify-between border-t border-white/10 pt-6">
                    <div class="flex items-center gap-3 text-white">
                        <i class="fa-solid fa-hospital text-sm text-emerald-200"></i>
                        <p class="max-w-[200px] truncate text-[11px] font-bold tracking-wide text-emerald-50">
                            RSU Medical Clinic · Verified
                        </p>
                    </div>
                    <div class="flex items-center gap-2 rounded-full border border-emerald-400/30 bg-emerald-400/20 px-4 py-1.5 backdrop-blur-md">
                        <i class="fa-solid fa-circle-check text-[10px] text-emerald-300"></i>
                        <span class="text-[9px] font-black uppercase tracking-[0.15em] text-emerald-200">Active</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <button onclick="window.location.href='{{ route('user.history') }}'"
                class="group flex flex-col items-center rounded-[2.5rem] border border-slate-100 bg-white p-6 text-center shadow-[0_15px_30px_rgba(0,0,0,0.03)] transition-all active:scale-95">
                <div class="mb-3 flex h-12 w-12 items-center justify-center rounded-2xl bg-green-50 transition-transform group-hover:scale-110">
                    <i class="fa-solid fa-calendar-check text-lg text-green-600"></i>
                </div>
                <p class="mb-0.5 text-xl font-black text-slate-900">{{ $bookingList->count() }}</p>
                <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">รายการนัดหมาย</p>
            </button>
            <button onclick="window.location.href='{{ route('user.borrow.history') }}'"
                class="group flex flex-col items-center rounded-[2.5rem] border border-slate-100 bg-white p-6 text-center shadow-[0_15px_30px_rgba(0,0,0,0.03)] transition-all active:scale-95">
                <div class="mb-3 flex h-12 w-12 items-center justify-center rounded-2xl bg-orange-50 transition-transform group-hover:scale-110">
                    <i class="fa-solid fa-boxes-stacked text-lg text-orange-600"></i>
                </div>
                <p class="mb-0.5 text-xl font-black text-slate-900">{{ $borrowCount }}</p>
                <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">รายการยืมของ</p>
            </button>
        </div>

        <div class="space-y-4">
            <div class="flex items-center justify-between px-1">
                <h3 class="text-sm font-black uppercase tracking-widest text-slate-900">Main Menu</h3>
                <button class="rounded-full bg-green-50 px-3 py-1.5 text-[10px] font-black uppercase tracking-widest text-green-600">All Services</button>
            </div>

            <div class="rounded-[3rem] border border-slate-100 bg-white p-6 pt-8 shadow-[0_20px_50px_rgba(0,0,0,0.04)]">
                <div class="mb-8 grid grid-cols-2 gap-4">
                    <a href="{{ route('user.booking') }}"
                        class="group relative flex flex-col items-start overflow-hidden rounded-[2.2rem] bg-[#2e9e63] p-6 text-left text-white shadow-[0_15px_30px_rgba(46,158,99,0.25)] transition-all active:scale-95">
                        <div class="absolute -right-4 -top-4 h-16 w-16 rounded-full bg-white/10 blur-xl transition-transform group-hover:scale-150"></div>
                        <div class="mb-4 flex h-11 w-11 items-center justify-center rounded-2xl border border-white/20 bg-white/20">
                            <i class="fa-solid fa-calendar-plus text-base text-white"></i>
                        </div>
                        <p class="text-[13px] font-black leading-tight tracking-wide">จองคิว /<br>แคมเปญ</p>
                    </a>

                    <a href="{{ route('user.borrow.index') }}"
                        class="group relative flex flex-col items-start rounded-[2.2rem] border border-indigo-100 bg-indigo-50 p-6 text-indigo-600 shadow-sm transition-all active:scale-95">
                        @if($borrowCount > 0)
                            <span class="absolute right-4 top-4 flex h-6 w-6 items-center justify-center rounded-full border-2 border-white bg-red-500 text-[10px] font-black text-white shadow-lg animate-bounce">{{ $borrowCount }}</span>
                        @endif
                        <div class="mb-4 flex h-11 w-11 items-center justify-center rounded-2xl border border-indigo-50 bg-white shadow-sm transition-transform group-hover:scale-110">
                            <i class="fa-solid fa-box-open text-base text-indigo-500"></i>
                        </div>
                        <p class="text-[13px] font-black leading-tight tracking-wide text-slate-800">ยืมอุปกรณ์<br>และติดตามรายการ</p>
                    </a>
                </div>

                <div class="border-t border-slate-50 pt-6">
                    <p class="mb-5 text-center text-[9px] font-black uppercase tracking-[0.3em] text-slate-400">External Services</p>
                    <div class="grid grid-cols-4 gap-4">
                        <a href="https://page.line.me/641mrzwm?oat_content=url&openQrModal=true" target="_blank" rel="noopener noreferrer" class="flex flex-col items-center gap-2 transition-all active:scale-90">
                            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-purple-50 text-purple-600 shadow-sm">
                                <i class="fa-solid fa-comment-dots text-lg"></i>
                            </div>
                            <span class="text-center text-[8px] font-black uppercase leading-tight tracking-widest text-slate-500">Counseling</span>
                        </a>
                        <a href="{{ route('user.services.ncd-clinic') }}" class="flex flex-col items-center gap-2 transition-all active:scale-90">
                            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-cyan-50 text-cyan-600 shadow-sm">
                                <i class="fa-solid fa-heart-pulse text-lg"></i>
                            </div>
                            <span class="text-center text-[8px] font-black uppercase leading-tight tracking-widest text-slate-500">NCD Clinic</span>
                        </a>
                        <a href="{{ route('user.services.contact') }}" class="flex flex-col items-center gap-2 transition-all active:scale-90">
                            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600 shadow-sm">
                                <i class="fa-solid fa-phone-flip text-base"></i>
                            </div>
                            <span class="text-center text-[8px] font-black uppercase leading-tight tracking-widest text-slate-500">Contact</span>
                        </a>
                        <a href="{{ route('user.services.help') }}" class="flex flex-col items-center gap-2 transition-all active:scale-90">
                            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-orange-50 text-orange-600 shadow-sm">
                                <i class="fa-solid fa-circle-question text-lg"></i>
                            </div>
                            <span class="text-center text-[8px] font-black uppercase leading-tight tracking-widest text-slate-500">Help</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="space-y-4">
            <p class="px-1 text-left text-[10px] font-black uppercase tracking-[0.3em] text-slate-500">Upcoming Appointments</p>
            <div class="overflow-hidden rounded-[3rem] border border-slate-100 bg-white shadow-[0_20px_50px_rgba(0,0,0,0.04)]">
                <div class="flex items-center justify-between border-b border-slate-50 px-7 pb-4 pt-7">
                    <h3 class="text-xs font-black uppercase tracking-widest text-slate-900">Latest Queue</h3>
                    <span class="rounded-full bg-green-50 px-3 py-1 text-[9px] font-black uppercase text-green-600">{{ $upcomingCount }} Active</span>
                </div>
                <div class="space-y-4 p-6">
                    @forelse($bookingList->whereIn('status', ['pending', 'confirmed'])->take(2) as $booking)
                        <div class="group relative rounded-[2.2rem] border border-slate-100 bg-slate-50/50 p-6 text-left transition-all active:scale-[0.98]">
                            <div class="mb-5 flex items-start justify-between">
                                <div class="flex items-center gap-4">
                                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl border border-slate-100 bg-white text-base text-green-600 shadow-sm">
                                        <i class="fa-solid fa-calendar-check"></i>
                                    </div>
                                    <div>
                                        <h4 class="mb-1.5 text-sm font-black leading-tight text-slate-900">{{ $booking->campaign->title }}</h4>
                                        <div class="flex items-center gap-2">
                                            <span class="h-1.5 w-1.5 rounded-full {{ $booking->status === 'confirmed' ? 'bg-green-500' : 'bg-amber-500' }}"></span>
                                            <p class="text-[9px] font-black uppercase tracking-[0.1em] text-slate-400">{{ $booking->status }} slot</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="flex items-center gap-3 rounded-2xl border border-slate-100/50 bg-white p-4 shadow-sm">
                                    <i class="fa-regular fa-calendar text-xs text-green-500"></i>
                                    <div>
                                        <p class="mb-1 text-[8px] font-black uppercase leading-none tracking-widest text-slate-400">Date</p>
                                        <p class="text-xs font-black leading-none text-slate-800">{{ $booking->slot->date->format('d M Y') }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3 rounded-2xl border border-slate-100/50 bg-white p-4 shadow-sm">
                                    <i class="fa-regular fa-clock text-xs text-green-500"></i>
                                    <div>
                                        <p class="mb-1 text-[8px] font-black uppercase leading-none tracking-widest text-slate-400">Time</p>
                                        <p class="text-xs font-black leading-none text-slate-800">{{ \Carbon\Carbon::parse($booking->slot->start_time)->format('H:i') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="py-12 text-center text-sm font-bold italic text-slate-300">ยังไม่มีนัดหมายที่กำลังจะมาถึง</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="space-y-4">
            <p class="px-1 text-left text-[10px] font-black uppercase tracking-[0.3em] text-slate-500">Latest Announcements</p>
            <div class="rounded-[3rem] border border-slate-100 bg-white p-6 shadow-[0_20px_50px_rgba(0,0,0,0.04)]">
                <div class="space-y-4">
                    @forelse($announcements->take(3) as $announcement)
                        <div class="rounded-[2rem] border border-slate-100 bg-slate-50/80 p-5">
                            <p class="mb-1 text-[10px] font-black uppercase tracking-widest text-slate-400">{{ $announcement->created_at->diffForHumans() }}</p>
                            <h4 class="text-sm font-black text-slate-900">{{ $announcement->title }}</h4>
                            <p class="mt-1 text-[11px] leading-relaxed text-slate-500">{{ $announcement->content }}</p>
                        </div>
                    @empty
                        <div class="py-8 text-center text-sm font-bold text-slate-300">ยังไม่มีประกาศใหม่ในขณะนี้</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="space-y-4">
            <p class="px-1 text-left text-[10px] font-black uppercase tracking-[0.3em] text-slate-500">Medical Coverage</p>

            @if($insurance)
                <div class="premium-shadow relative overflow-hidden rounded-[3rem] bg-slate-900 p-8 text-left shadow-2xl">
                    <div class="absolute -bottom-8 -right-8 h-40 w-40 rounded-full bg-white/5 blur-3xl"></div>
                    <div class="mb-8 flex items-start justify-between">
                        <div>
                            <h4 class="text-sm font-black tracking-tight text-white">Health Insurance</h4>
                            <p class="mt-1.5 text-[9px] font-black uppercase leading-none tracking-[0.2em] text-white/30">{{ $insurance->member_status }}</p>
                        </div>
                        <span class="rounded-full px-3 py-1 text-[9px] font-black uppercase tracking-widest {{ $insurance->insurance_status === 'Active' ? 'bg-emerald-500/20 text-emerald-400' : 'bg-rose-500/20 text-rose-400' }}">
                            {{ $insurance->insurance_status }}
                        </span>
                    </div>
                    <div class="mb-6">
                        <p class="mb-1 text-[9px] font-black uppercase tracking-[0.2em] text-white/30">Policy No.</p>
                        <p class="text-xs font-black tracking-widest text-white/70">{{ $insurance->policy_number ?? 'RSU-INS-7782' }}</p>
                    </div>
                    <div class="relative z-10 flex items-end justify-between border-t border-white/10 pt-6">
                        <div>
                            <p class="mb-1.5 text-[8px] font-black uppercase tracking-[0.2em] text-white/30">Primary Holder</p>
                            <p class="max-w-[180px] truncate text-[11px] font-black uppercase tracking-wider text-white">{{ $user->name }}</p>
                        </div>
                        <div class="text-right">
                            <p class="mb-1.5 text-[8px] font-black uppercase tracking-[0.2em] text-white/30">Coverage</p>
                            <p class="text-[11px] font-black uppercase tracking-widest text-white">Active</p>
                        </div>
                    </div>
                </div>
            @else
                <div class="premium-shadow relative overflow-hidden rounded-[3rem] bg-slate-900 p-8 text-left shadow-2xl">
                    <div class="absolute -bottom-8 -right-8 h-40 w-40 rounded-full bg-white/5 blur-3xl"></div>
                    <div class="flex flex-col items-center justify-center gap-3 py-6 text-center">
                        <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-white/10 text-2xl text-white/30">
                            <i class="fa-solid fa-shield-xmark"></i>
                        </div>
                        <p class="text-xs font-black uppercase tracking-widest text-white/50">ไม่พบข้อมูลประกันในระบบ</p>
                    </div>
                </div>
            @endif

            <button onclick="showInsurance()" class="group relative w-full cursor-pointer overflow-hidden rounded-[2.2rem] bg-[#2e9e63] p-6 text-left shadow-xl shadow-green-100">
                <div class="absolute -right-4 -top-4 h-20 w-20 rounded-full bg-white/10 blur-xl transition-transform group-hover:scale-150"></div>
                <div class="relative z-10 flex items-center gap-4">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white/20 text-white">
                        <i class="fa-solid fa-shield-heart"></i>
                    </div>
                    <div class="flex-1">
                        <h5 class="text-xs font-black uppercase tracking-widest text-white">ความคุ้มครองและวิธีใช้สิทธิ์</h5>
                        <p class="mt-0.5 text-[11px] text-white/70">กดเพื่อดูรายละเอียดประกันและช่องทางติดต่อ</p>
                    </div>
                    <i class="fa-solid fa-chevron-right shrink-0 text-xs text-white/50"></i>
                </div>
            </button>
        </div>

        <footer class="space-y-2 pb-16 pt-10 text-center opacity-30">
            <p class="text-[10px] font-black uppercase tracking-[0.3em] text-slate-400">© 2569 RSU Medical Services</p>
            <div class="flex items-center justify-center gap-3">
                <span class="h-1 w-1 rounded-full bg-slate-400"></span>
                <p class="text-[9px] font-bold uppercase tracking-widest text-slate-400">Hospital OS v3.2</p>
                <span class="h-1 w-1 rounded-full bg-slate-400"></span>
            </div>
        </footer>
    </main>

    <div id="insDetailModal" class="fixed inset-0 z-[100] hidden items-center justify-center p-6">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-md" onclick="hideInsurance()"></div>
        <div class="relative max-h-[80vh] w-full max-w-[340px] overflow-y-auto rounded-[3rem] bg-white p-8 shadow-2xl">
            <h3 class="mb-4 text-xl font-black">รายละเอียดความคุ้มครอง</h3>
            <div class="space-y-4 text-left text-sm text-slate-600">
                <p>- คุ้มครองอุบัติเหตุ 24 ชั่วโมง</p>
                <p>- วงเงินค่ารักษาพยาบาลตามสิทธิ์</p>
                <p>- แสดงบัตรนี้พร้อมบัตรประชาชนเพื่อใช้สิทธิ์</p>
            </div>
            <button onclick="hideInsurance()" class="mt-8 w-full rounded-2xl bg-slate-900 py-4 font-black text-white">รับทราบ</button>
        </div>
    </div>
</x-user-layout>
