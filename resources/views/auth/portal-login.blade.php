<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Login - {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body { font-family: 'Outfit', sans-serif; }
    </style>
</head>
<body class="min-h-screen bg-[radial-gradient(circle_at_top,rgba(14,165,233,0.14),transparent_35%),linear-gradient(180deg,#f7fbff_0%,#eef6ff_100%)] text-slate-900 antialiased">
    <div class="mx-auto flex min-h-screen max-w-7xl items-center px-6 py-10 lg:px-8">
        <div class="grid w-full overflow-hidden rounded-[2rem] border border-white/80 bg-white shadow-[0_32px_120px_rgba(15,23,42,0.12)] lg:grid-cols-[minmax(22rem,0.95fr),minmax(0,1.05fr)]">
            <section class="relative overflow-hidden bg-[linear-gradient(145deg,#0ea5e9_0%,#0284c7_50%,#0f172a_100%)] px-8 py-10 text-white lg:px-10 lg:py-12">
                <div class="absolute -right-10 top-10 h-40 w-40 rounded-full border-[28px] border-white/10"></div>
                <div class="absolute -bottom-12 -left-12 h-48 w-48 rounded-full border-[30px] border-white/10"></div>

                <div class="relative flex h-full flex-col justify-between gap-10">
                    <div class="space-y-6">
                        <div class="inline-flex items-center gap-3 rounded-full bg-white/10 px-4 py-2 backdrop-blur">
                            <span class="flex h-10 w-10 items-center justify-center rounded-full bg-white text-sky-600">
                                <i class="fa-solid fa-hospital-user text-sm"></i>
                            </span>
                            <div>
                                <p class="text-[11px] font-black uppercase tracking-[0.24em] text-white/70">RSU Medical Hub</p>
                                <p class="text-sm font-bold text-white">Portal Console</p>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <p class="text-[11px] font-black uppercase tracking-[0.24em] text-sky-100/80">Superadmin Access</p>
                            <h1 class="max-w-sm text-4xl font-black leading-tight tracking-tight">ควบคุมหลายคลินิกจากจุดเดียว</h1>
                            <p class="max-w-md text-sm font-bold leading-relaxed text-white/75">
                                ใช้บัญชี portal เพื่อดูภาพรวมทุกคลินิก จัดการ chatbot, site settings และงานควบคุมระดับระบบกลางโดยไม่ต้องสลับบริบทไปมาระหว่างโมดูล
                            </p>
                        </div>
                    </div>

                    <div class="relative grid grid-cols-3 gap-3">
                        <div class="rounded-[1.5rem] bg-white/10 px-4 py-4 backdrop-blur">
                            <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-white/15 text-white">
                                <i class="fa-solid fa-building-circle-check text-lg"></i>
                            </div>
                            <div class="mt-4 text-xs font-black uppercase tracking-[0.18em] text-white/65">Clinics</div>
                        </div>
                        <div class="rounded-[1.5rem] bg-white/10 px-4 py-4 backdrop-blur">
                            <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-white/15 text-white">
                                <i class="fa-solid fa-robot text-lg"></i>
                            </div>
                            <div class="mt-4 text-xs font-black uppercase tracking-[0.18em] text-white/65">Chatbot</div>
                        </div>
                        <div class="rounded-[1.5rem] bg-white/10 px-4 py-4 backdrop-blur">
                            <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-white/15 text-white">
                                <i class="fa-solid fa-sliders text-lg"></i>
                            </div>
                            <div class="mt-4 text-xs font-black uppercase tracking-[0.18em] text-white/65">Settings</div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="px-8 py-10 lg:px-10 lg:py-12">
                <div class="mx-auto max-w-xl space-y-8">
                    <div class="space-y-3">
                        <p class="inline-flex items-center gap-3 rounded-full border border-sky-100 bg-sky-50 px-4 py-2 text-[11px] font-black uppercase tracking-[0.22em] text-sky-700">
                            <span class="h-2 w-2 rounded-full bg-sky-500"></span>
                            Portal Sign In
                        </p>
                        <div>
                            <h2 class="text-4xl font-black tracking-tight text-slate-950">เข้าระบบ Portal</h2>
                            <p class="mt-2 text-sm font-bold text-slate-500">ลงชื่อเข้าใช้ด้วยบัญชี superadmin เพื่อเข้าหน้า Portal Console</p>
                        </div>
                    </div>

                    @if (session('error') || $errors->any())
                        <div class="rounded-[1.5rem] border border-rose-200 bg-rose-50 px-5 py-4 text-sm font-bold text-rose-700">
                            <i class="fa-solid fa-circle-exclamation mr-2"></i>
                            {{ session('error') ?: $errors->first() }}
                        </div>
                    @endif

                    <form action="{{ route('portal.login.store') }}" method="POST" class="space-y-5">
                        @csrf

                        <div>
                            <label class="mb-2 block text-xs font-black uppercase tracking-[0.22em] text-slate-500">Email</label>
                            <div class="relative">
                                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-5 text-slate-400">
                                    <i class="fa-regular fa-envelope"></i>
                                </span>
                                <input
                                    type="email"
                                    name="email"
                                    value="{{ old('email') }}"
                                    placeholder="portal@example.com"
                                    required
                                    class="w-full rounded-[1.5rem] border border-slate-200 bg-slate-50 py-4 pl-12 pr-4 text-sm font-bold text-slate-700 outline-none transition focus:border-sky-400 focus:bg-white focus:ring-4 focus:ring-sky-100"
                                >
                            </div>
                        </div>

                        <div>
                            <label class="mb-2 block text-xs font-black uppercase tracking-[0.22em] text-slate-500">Password</label>
                            <div class="relative">
                                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-5 text-slate-400">
                                    <i class="fa-solid fa-lock"></i>
                                </span>
                                <input
                                    type="password"
                                    name="password"
                                    id="portalPassword"
                                    placeholder="Enter your password"
                                    required
                                    class="w-full rounded-[1.5rem] border border-slate-200 bg-slate-50 py-4 pl-12 pr-12 text-sm font-bold text-slate-700 outline-none transition focus:border-sky-400 focus:bg-white focus:ring-4 focus:ring-sky-100"
                                >
                                <button type="button" id="togglePortalPassword" class="absolute inset-y-0 right-0 flex items-center pr-5 text-slate-400 transition hover:text-sky-600">
                                    <i class="fa-regular fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <button type="submit" class="inline-flex w-full items-center justify-center gap-3 rounded-[1.5rem] bg-sky-600 px-6 py-4 text-sm font-black uppercase tracking-[0.22em] text-white shadow-[0_18px_40px_rgba(14,165,233,0.24)] transition hover:-translate-y-0.5 hover:bg-sky-700">
                            เข้าสู่ Portal
                            <i class="fa-solid fa-arrow-right"></i>
                        </button>
                    </form>

                    <div class="grid gap-4 rounded-[1.75rem] border border-slate-200 bg-slate-50 px-5 py-5 sm:grid-cols-2">
                        <a href="{{ route('staff.login') }}" class="rounded-2xl bg-white px-4 py-4 text-sm font-black text-slate-700 shadow-sm ring-1 ring-slate-200 transition hover:-translate-y-0.5 hover:shadow-md">
                            <div class="text-[11px] uppercase tracking-[0.18em] text-slate-400">Need staff access?</div>
                            <div class="mt-2 flex items-center justify-between">
                                <span>Go to Staff Login</span>
                                <i class="fa-solid fa-arrow-up-right-from-square text-slate-400"></i>
                            </div>
                        </a>
                        <a href="{{ route('portal') }}" class="rounded-2xl bg-white px-4 py-4 text-sm font-black text-slate-700 shadow-sm ring-1 ring-slate-200 transition hover:-translate-y-0.5 hover:shadow-md">
                            <div class="text-[11px] uppercase tracking-[0.18em] text-slate-400">Portal entry</div>
                            <div class="mt-2 flex items-center justify-between">
                                <span>Refresh current entry flow</span>
                                <i class="fa-solid fa-rotate-right text-slate-400"></i>
                            </div>
                        </a>
                    </div>

                    <p class="text-center text-xs font-bold text-slate-400">Powered by RSU Medical Hub Platform</p>
                </div>
            </section>
        </div>
    </div>

    <script>
        const portalPasswordInput = document.getElementById('portalPassword');
        const togglePortalPasswordButton = document.getElementById('togglePortalPassword');
        const togglePortalPasswordIcon = togglePortalPasswordButton?.querySelector('i');

        togglePortalPasswordButton?.addEventListener('click', () => {
            const isPassword = portalPasswordInput.type === 'password';
            portalPasswordInput.type = isPassword ? 'text' : 'password';
            togglePortalPasswordIcon.className = isPassword ? 'fa-regular fa-eye-slash' : 'fa-regular fa-eye';
        });
    </script>
</body>
</html>
