<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Admin Dashboard' }} - RSU Medical Hub</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700;800&display=swap');

        body {
            font-family: 'Prompt', sans-serif;
            background-color: #f8fafc;
        }

        .glass-sidebar {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(20px);
            border-right: 1px solid rgba(226, 232, 240, 0.8);
        }

        .nav-link {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border-radius: 1rem;
        }

        .nav-link:hover {
            background-color: rgba(46, 158, 99, 0.05);
            color: #2e9e63;
        }

        .nav-link.active {
            background-color: #2e9e63;
            color: white;
            box-shadow: 0 10px 15px -3px rgba(46, 158, 99, 0.2);
        }

        .custom-scrollbar::-webkit-scrollbar {
            width: 5px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #e2e8f0;
            border-radius: 10px;
        }
    </style>
    @livewireStyles
</head>
<body class="overflow-hidden">
    <div class="flex h-screen overflow-hidden">
        <aside class="w-72 glass-sidebar flex-shrink-0 flex flex-col z-50">
            <div class="p-8">
                <div class="flex items-center gap-3 mb-10">
                    <div class="w-10 h-10 bg-[#2e9e63] rounded-xl flex items-center justify-center text-white shadow-lg shadow-green-200">
                        <i class="fa-solid fa-clinic-medical"></i>
                    </div>
                    <div>
                        <h2 class="font-black text-slate-800 tracking-tight leading-none mb-1">RSU Medical</h2>
                        <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest leading-none">Admin Console</p>
                    </div>
                </div>

                <nav class="space-y-2">
                    <p class="text-[10px] text-slate-400 font-black uppercase tracking-[0.2em] mb-4 ml-4">Management</p>

                    <a href="{{ route('admin.dashboard') }}" class="nav-link flex items-center gap-3 px-5 py-3.5 text-sm font-bold {{ request()->routeIs('admin.dashboard') ? 'active' : 'text-slate-500' }}">
                        <i class="fa-solid fa-chart-pie w-5"></i>
                        <span>Dashboard (KPI)</span>
                    </a>

                    <a href="{{ route('admin.campaigns') }}" class="nav-link flex items-center gap-3 px-5 py-3.5 text-sm font-bold {{ request()->routeIs('admin.campaigns') ? 'active' : 'text-slate-500' }}">
                        <i class="fa-solid fa-calendar-check w-5"></i>
                        <span>จัดการแคมเปญ</span>
                    </a>

                    <a href="{{ route('admin.bookings') }}" class="nav-link flex items-center gap-3 px-5 py-3.5 text-sm font-bold {{ request()->routeIs('admin.bookings') ? 'active' : 'text-slate-500' }}">
                        <i class="fa-solid fa-users-viewfinder w-5"></i>
                        <span>รายการจองคิว</span>
                    </a>

                    <a href="{{ route('admin.users') }}" class="nav-link flex items-center gap-3 px-5 py-3.5 text-sm font-bold {{ request()->routeIs('admin.users') ? 'active' : 'text-slate-500' }}">
                        <i class="fa-solid fa-id-card w-5"></i>
                        <span>รายชื่อนักศึกษา</span>
                    </a>

                    <a href="{{ route('admin.time_slots') }}" class="nav-link flex items-center gap-3 px-5 py-3.5 text-sm font-bold {{ request()->routeIs('admin.time_slots') ? 'active' : 'text-slate-500' }}">
                        <i class="fa-solid fa-clock-rotate-left w-5"></i>
                        <span>จัดการรอบเวลา</span>
                    </a>

                    <p class="text-[10px] text-slate-400 font-black uppercase tracking-[0.2em] mt-8 mb-4 ml-4">Organization</p>

                    <a href="{{ route('admin.manage_staff') }}" class="nav-link flex items-center gap-3 px-5 py-3.5 text-sm font-bold {{ request()->routeIs('admin.manage_staff') ? 'active' : 'text-slate-500' }}">
                        <i class="fa-solid fa-user-gear w-5"></i>
                        <span>จัดการเจ้าหน้าที่</span>
                    </a>

                    <a href="{{ route('admin.activity_logs') }}" class="nav-link flex items-center gap-3 px-5 py-3.5 text-sm font-bold {{ request()->routeIs('admin.activity_logs') ? 'active' : 'text-slate-500' }}">
                        <i class="fa-solid fa-list-check w-5"></i>
                        <span>บันทึกกิจกรรม</span>
                    </a>

                    <a href="{{ route('admin.reports') }}" class="nav-link flex items-center gap-3 px-5 py-3.5 text-sm font-bold {{ request()->routeIs('admin.reports') ? 'active' : 'text-slate-500' }}">
                        <i class="fa-solid fa-chart-line w-5"></i>
                        <span>รายงานและสถิติ</span>
                    </a>
                </nav>
            </div>

            <div class="mt-auto p-8 border-t border-slate-50">
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button type="submit" class="w-full py-4 bg-rose-50 text-rose-500 rounded-2xl font-bold text-sm flex items-center justify-center gap-2 hover:bg-rose-500 hover:text-white transition-all shadow-sm">
                        <i class="fa-solid fa-power-off"></i>
                        <span>ออกจากระบบ</span>
                    </button>
                </form>
            </div>
        </aside>

        <main class="flex-1 flex flex-col min-w-0 overflow-hidden">
            <header class="h-20 bg-white/50 backdrop-blur-md flex items-center justify-between px-10 flex-shrink-0 border-b border-slate-100/50">
                <div class="flex items-center gap-4">
                    <h3 class="text-slate-800 font-black text-lg tracking-tight">{{ $title ?? 'Dashboard' }}</h3>
                </div>
                <div class="flex items-center gap-6">
                    <div class="flex flex-col text-right">
                        <p class="text-sm font-black text-slate-800 leading-none mb-1">{{ Auth::guard('admin')->user()->name ?? 'Administrator' }}</p>
                        <p class="text-[10px] text-emerald-500 font-bold uppercase tracking-widest leading-none">Admin</p>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-slate-100 overflow-hidden border-2 border-white shadow-sm">
                        <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::guard('admin')->user()->name ?? 'A') }}&background=2e9e63&color=fff" class="w-full h-full object-cover" alt="Admin avatar">
                    </div>
                </div>
            </header>

            <div class="flex-1 overflow-y-auto p-10 custom-scrollbar">
                {{ $slot }}
            </div>
        </main>
    </div>

    @livewireScripts
</body>
</html>
