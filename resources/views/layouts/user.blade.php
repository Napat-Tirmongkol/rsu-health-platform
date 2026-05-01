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
    </style>
    @livewireStyles
</head>
<body class="pb-32">
    <div class="relative mx-auto min-h-screen max-w-md">
        <header class="sticky top-0 z-[60] flex items-center justify-between border-b border-slate-50 bg-white px-6 py-4 shadow-sm shadow-slate-100">
            <div class="flex items-center gap-4">
                <a href="{{ route('user.booking') }}" class="flex h-12 w-12 items-center justify-center rounded-2xl bg-[#2e9e63] text-white shadow-lg shadow-green-100 transition-all active:scale-90">
                    <i class="fa-solid fa-plus text-xl"></i>
                </a>
                <div class="flex flex-col">
                    <h1 class="mb-1 text-lg font-black leading-none tracking-tight text-slate-900">RSU Medical Clinic</h1>
                    <p class="text-[10px] font-black uppercase leading-none tracking-[0.2em] text-slate-400">User Hub</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <button onclick="showQR()" class="flex h-10 w-10 items-center justify-center text-slate-600 transition-transform hover:text-green-600 active:scale-90">
                    <i class="fa-solid fa-qrcode text-lg"></i>
                </button>
                <livewire:user.notification-bell />
            </div>
        </header>

        <div id="qr-modal" class="fixed inset-0 z-[100] hidden items-center justify-center p-6">
            <button type="button" aria-label="Close QR modal" class="absolute inset-0 bg-slate-900/60" onclick="hideQR()"></button>
            <div class="relative w-full max-w-[340px] rounded-[3rem] bg-white p-10 text-center shadow-2xl">
                <div class="mx-auto mb-8 h-1.5 w-12 rounded-full bg-slate-100"></div>
                <div class="mb-8 flex justify-center rounded-[2.5rem] bg-slate-50 p-8 shadow-inner">
                    <div class="flex h-48 w-48 items-center justify-center rounded-3xl border border-slate-100 bg-white p-4 shadow-sm [&_svg]:h-full [&_svg]:w-full">
                        @if($identityQrSvg)
                            {!! $identityQrSvg !!}
                        @else
                            <i class="fa-solid fa-qrcode text-6xl text-slate-800"></i>
                        @endif
                    </div>
                </div>
                <h3 class="mb-1.5 text-xl font-black text-slate-900">Identity QR Code</h3>
                <p class="mb-2 font-mono text-sm font-black tracking-[0.2em] text-[#2e9e63]">{{ Auth::guard('user')->user()->resolveIdentity()['value'] }}</p>
                <p class="mb-2 text-[10px] font-bold uppercase tracking-widest text-slate-500">{{ Auth::guard('user')->user()->resolveIdentity()['label'] }}</p>
                <p class="mb-8 text-[10px] font-bold uppercase tracking-widest text-slate-400">RSU Medical Identity</p>
                <button onclick="hideQR()" class="h-16 w-full rounded-2xl bg-slate-900 font-black text-white shadow-xl transition-all active:scale-95">ปิดหน้าต่าง</button>
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

        <nav class="fixed bottom-0 left-0 right-0 z-[70] mx-auto flex max-w-md items-center justify-between border-t border-slate-50 bg-white px-8 py-4 pb-10 shadow-[0_-20px_40px_rgba(0,0,0,0.04)]">
            <a href="{{ route('user.hub') }}" class="flex flex-col items-center gap-1.5 transition-all {{ request()->routeIs('user.hub') ? 'scale-110 text-green-600' : 'text-slate-300' }}">
                <i class="fa-solid fa-house-chimney text-xl"></i>
                <span class="text-[8px] font-black uppercase tracking-[0.1em]">Home</span>
            </a>
            <a href="{{ route('user.history') }}" class="flex flex-col items-center gap-1.5 transition-all {{ request()->routeIs('user.history') ? 'text-green-600' : 'text-slate-300' }}">
                <i class="fa-solid fa-calendar-day text-xl"></i>
                <span class="text-[8px] font-black uppercase tracking-[0.1em]">History</span>
            </a>

            <div class="relative -mt-14">
                <a href="{{ route('user.booking') }}" class="group flex h-16 w-16 items-center justify-center rounded-[1.8rem] border-[6px] border-[#F8FAFF] bg-[#2e9e63] text-white shadow-[0_15px_30px_rgba(46,158,99,0.4)] transition-all active:scale-90 {{ request()->routeIs('user.booking') ? 'ring-4 ring-emerald-100' : '' }}">
                    <i class="fa-solid fa-plus text-2xl transition-transform group-hover:scale-125"></i>
                </a>
            </div>

            <a href="{{ route('user.chat') }}" class="flex flex-col items-center gap-1.5 transition-all {{ request()->routeIs('user.chat') ? 'text-green-600' : 'text-slate-300' }}">
                <i class="fa-solid fa-comment-dots text-xl"></i>
                <span class="text-[8px] font-black uppercase tracking-[0.1em]">Chat</span>
            </a>
            <a href="{{ route('user.profile') }}" class="flex flex-col items-center gap-1.5 transition-all {{ request()->routeIs('user.profile') ? 'text-green-600' : 'text-slate-300' }}">
                <i class="fa-solid fa-user text-xl"></i>
                <span class="text-[8px] font-black uppercase tracking-[0.1em]">Account</span>
            </a>
        </nav>
    </div>

    @livewireScripts
</body>
</html>
