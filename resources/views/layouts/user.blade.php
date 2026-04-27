<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>{{ $title ?? 'RSU Medical Hub' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            -webkit-tap-highlight-color: transparent;
            background-color: #F8FAFF;
            color: #0f172a;
        }

        .premium-shadow {
            box-shadow: 0 20px 40px -15px rgba(46, 158, 99, 0.15);
        }

        .custom-scrollbar::-webkit-scrollbar {
            display: none;
        }

        .nav-active {
            color: #2e9e63 !important;
            transform: scale(1.1);
        }
    </style>
    @livewireStyles
</head>
<body class="pb-32">
    <div class="max-w-md mx-auto relative min-h-screen">
        <header class="bg-white sticky top-0 z-[60] px-6 py-4 flex items-center justify-between border-b border-slate-50 shadow-sm shadow-slate-100">
            <div class="flex items-center gap-4">
                <a href="{{ route('user.booking') }}" class="w-12 h-12 bg-[#2e9e63] rounded-2xl flex items-center justify-center text-white shadow-lg shadow-green-100 active:scale-90 transition-all">
                    <i class="fa-solid fa-plus text-xl"></i>
                </a>
                <div class="flex flex-col">
                    <h1 class="text-slate-900 font-black text-lg leading-none mb-1 tracking-tight">RSU Medical Clinic</h1>
                    <p class="text-slate-400 text-[10px] font-black uppercase tracking-[0.2em] leading-none">User Hub</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <button onclick="showQR()" class="w-10 h-10 flex items-center justify-center text-slate-600 hover:text-green-600 transition-colors active:scale-90 transition-all">
                    <i class="fa-solid fa-qrcode text-lg"></i>
                </button>
                <livewire:user.notification-bell />
            </div>
        </header>

        <div id="qr-modal" class="fixed inset-0 z-[100] hidden items-center justify-center p-6">
            <button type="button" aria-label="Close QR modal" class="absolute inset-0 bg-slate-900/60" onclick="hideQR()"></button>
            <div class="relative bg-white w-full max-w-[340px] rounded-[3rem] p-10 text-center shadow-2xl">
                <div class="w-12 h-1.5 bg-slate-100 rounded-full mx-auto mb-8"></div>
                <div class="bg-slate-50 rounded-[2.5rem] p-8 mb-8 shadow-inner flex justify-center">
                    <div class="w-48 h-48 bg-white p-4 rounded-3xl shadow-sm flex items-center justify-center border border-slate-100 [&_svg]:w-full [&_svg]:h-full">
                        @if($identityQrSvg)
                            {!! $identityQrSvg !!}
                        @else
                            <i class="fa-solid fa-qrcode text-6xl text-slate-800"></i>
                        @endif
                    </div>
                </div>
                <h3 class="text-slate-900 font-black text-xl mb-1.5">Identity QR Code</h3>
                <p class="text-[#2e9e63] font-mono font-black text-sm tracking-[0.2em] mb-2">{{ Auth::guard('user')->user()->student_personnel_id ?? Auth::guard('user')->user()->username ?? 'USER' }}</p>
                <p class="text-slate-400 text-[10px] font-bold uppercase tracking-widest mb-8">RSU Medical Identity</p>
                <button onclick="hideQR()" class="w-full h-16 bg-slate-900 text-white font-black rounded-2xl active:scale-95 transition-all shadow-xl">ปิดหน้าต่าง</button>
            </div>
        </div>

        <script>
            function showQR() {
                const modal = document.getElementById('qr-modal');
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            }

            function hideQR() {
                const modal = document.getElementById('qr-modal');
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }
        </script>

        <main>
            {{ $slot }}
        </main>

        <nav class="fixed bottom-0 left-0 right-0 z-[70] bg-white border-t border-slate-50 px-8 py-4 pb-10 flex justify-between items-center max-w-md mx-auto shadow-[0_-20px_40px_rgba(0,0,0,0.04)]">
            <a href="{{ route('user.hub') }}" class="flex flex-col items-center gap-1.5 {{ request()->routeIs('user.hub') ? 'text-green-600 scale-110' : 'text-slate-300' }} transition-all">
                <i class="fa-solid fa-house-chimney text-xl"></i>
                <span class="text-[8px] font-black uppercase tracking-[0.1em]">Home</span>
            </a>
            <a href="{{ route('user.history') }}" class="flex flex-col items-center gap-1.5 {{ request()->routeIs('user.history') ? 'text-green-600' : 'text-slate-300' }} transition-all">
                <i class="fa-solid fa-calendar-day text-xl"></i>
                <span class="text-[8px] font-black uppercase tracking-[0.1em]">Booking</span>
            </a>

            <div class="relative -mt-14">
                <a href="{{ route('user.booking') }}" class="w-16 h-16 bg-[#2e9e63] rounded-[1.8rem] rotate-45 flex items-center justify-center text-white shadow-[0_15px_30px_rgba(46,158,99,0.4)] border-[6px] border-[#F8FAFF] active:scale-90 transition-all group">
                    <i class="fa-solid fa-plus text-2xl -rotate-45 group-hover:scale-125 transition-transform"></i>
                </a>
            </div>

            <a href="{{ route('user.chat') }}" class="flex flex-col items-center gap-1.5 {{ request()->routeIs('user.chat') ? 'text-green-600' : 'text-slate-300' }} transition-all">
                <i class="fa-solid fa-comment-dots text-xl"></i>
                <span class="text-[8px] font-black uppercase tracking-[0.1em]">Chat</span>
            </a>
            <a href="{{ route('user.profile') }}" class="flex flex-col items-center gap-1.5 {{ request()->routeIs('user.profile') ? 'text-green-600' : 'text-slate-300' }} transition-all">
                <i class="fa-solid fa-user-ninja text-xl"></i>
                <span class="text-[8px] font-black uppercase tracking-[0.1em]">Account</span>
            </a>
        </nav>
    </div>

    @livewireScripts
</body>
</html>
