<x-portal-layout title="Portal Dashboard">
    <div class="py-10">
        <div class="mx-auto max-w-7xl space-y-8 px-6 lg:px-8">
            <section class="overflow-hidden rounded-[2.5rem] border border-sky-100 bg-white shadow-[0_24px_80px_rgba(15,23,42,0.08)]">
                <div class="bg-[radial-gradient(circle_at_top_left,rgba(14,165,233,0.18),transparent_40%)] px-8 py-8">
                    <div class="flex flex-col gap-6 xl:flex-row xl:items-end xl:justify-between">
                        <div class="space-y-3">
                            <p class="text-[10px] font-black uppercase tracking-[0.24em] text-slate-400">Platform Intent</p>
                            <h3 class="text-3xl font-black tracking-tight text-slate-950">เริ่มจากภาพรวม แล้วค่อยพาเข้าหน้างานของแต่ละคลินิก</h3>
                            <p class="max-w-3xl text-sm font-bold leading-relaxed text-slate-500">
                                หน้า portal นี้ออกแบบมาเพื่อให้ superadmin เห็นสถานะทุกคลินิกพร้อมกัน ลดการสลับบริบทระหว่างงานบริการ งานจอง งานยืมอุปกรณ์ และการตั้งค่าระบบกลาง
                            </p>
                        </div>

                        <div class="grid grid-cols-2 gap-3 lg:min-w-[28rem]">
                            <a href="{{ route('portal.clinics') }}" class="rounded-2xl border border-white/70 bg-white/90 px-5 py-4 text-sm font-black text-slate-900 shadow-sm transition-all hover:-translate-y-0.5 hover:shadow-md">
                                Clinics
                            </a>
                            <a href="{{ route('portal.chatbot.faqs') }}" class="rounded-2xl border border-white/70 bg-white/90 px-5 py-4 text-sm font-black text-slate-900 shadow-sm transition-all hover:-translate-y-0.5 hover:shadow-md">
                                Chatbot FAQs
                            </a>
                            <a href="{{ route('portal.chatbot.settings') }}" class="rounded-2xl border border-white/70 bg-white/90 px-5 py-4 text-sm font-black text-slate-900 shadow-sm transition-all hover:-translate-y-0.5 hover:shadow-md">
                                Chatbot Settings
                            </a>
                            <a href="{{ route('portal.settings') }}" class="rounded-2xl border border-white/70 bg-white/90 px-5 py-4 text-sm font-black text-slate-900 shadow-sm transition-all hover:-translate-y-0.5 hover:shadow-md">
                                Site Settings
                            </a>
                        </div>
                    </div>
                </div>
            </section>

            <section class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
                    <p class="text-[10px] font-black uppercase tracking-[0.24em] text-slate-400">Clinics</p>
                    <h3 class="mt-3 text-3xl font-black tracking-tight text-slate-950">{{ number_format($stats['total_clinics']) }}</h3>
                    <p class="mt-2 text-sm font-bold text-slate-500">เปิดใช้งาน {{ number_format($stats['active_clinics']) }} คลินิก</p>
                </div>
                <div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
                    <p class="text-[10px] font-black uppercase tracking-[0.24em] text-slate-400">Members</p>
                    <h3 class="mt-3 text-3xl font-black tracking-tight text-slate-950">{{ number_format($stats['total_users']) }}</h3>
                    <p class="mt-2 text-sm font-bold text-slate-500">ผู้ใช้งานรวมทุกคลินิก</p>
                </div>
                <div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
                    <p class="text-[10px] font-black uppercase tracking-[0.24em] text-slate-400">Campaigns</p>
                    <h3 class="mt-3 text-3xl font-black tracking-tight text-slate-950">{{ number_format($stats['active_campaigns']) }}</h3>
                    <p class="mt-2 text-sm font-bold text-slate-500">แคมเปญที่ยังเปิดรับอยู่ข้ามคลินิก</p>
                </div>
                <div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
                    <p class="text-[10px] font-black uppercase tracking-[0.24em] text-slate-400">System Load</p>
                    <h3 class="mt-3 text-3xl font-black tracking-tight text-slate-950">{{ number_format($stats['pending_bookings']) }}</h3>
                    <p class="mt-2 text-sm font-bold text-slate-500">รายการจองที่รอตรวจสอบ</p>
                </div>
            </section>

            <section class="grid grid-cols-1 gap-6 xl:grid-cols-[minmax(0,1.7fr),minmax(20rem,0.9fr)]">
                <div class="rounded-[2.5rem] border border-slate-200 bg-white p-6 shadow-sm lg:p-8">
                    <div class="flex items-end justify-between gap-4">
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-[0.24em] text-slate-400">Clinic Overview</p>
                            <h3 class="mt-3 text-2xl font-black tracking-tight text-slate-950">ภาพรวมแต่ละคลินิก</h3>
                            <p class="mt-2 text-sm font-bold text-slate-500">ดูผู้ใช้ ทีมเจ้าหน้าที่ แคมเปญ และรายการจองค้างแยกตามคลินิก</p>
                        </div>
                        <a href="{{ route('portal.clinics') }}" class="text-sm font-black text-sky-600 transition-colors hover:text-sky-700">ดูทั้งหมด</a>
                    </div>

                    <div class="mt-6 overflow-x-auto">
                        <table class="min-w-full whitespace-nowrap text-left">
                            <thead>
                                <tr class="border-b border-slate-100 bg-slate-50 text-[10px] font-black uppercase tracking-[0.22em] text-slate-400">
                                    <th class="px-5 py-4">Clinic</th>
                                    <th class="px-5 py-4 text-center">Users</th>
                                    <th class="px-5 py-4 text-center">Staff</th>
                                    <th class="px-5 py-4 text-center">Active Campaigns</th>
                                    <th class="px-5 py-4 text-center">Pending Bookings</th>
                                    <th class="px-5 py-4 text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($clinicSnapshots as $clinic)
                                    <tr class="hover:bg-slate-50/70">
                                        <td class="px-5 py-5">
                                            <div class="font-black text-slate-900">{{ $clinic['name'] }}</div>
                                            <div class="text-sm font-bold text-slate-500">{{ $clinic['slug'] }}</div>
                                        </td>
                                        <td class="px-5 py-5 text-center font-black text-slate-900">{{ number_format($clinic['users_count']) }}</td>
                                        <td class="px-5 py-5 text-center font-black text-slate-900">{{ number_format($clinic['staff_count']) }}</td>
                                        <td class="px-5 py-5 text-center font-black text-slate-900">{{ number_format($clinic['campaigns_count']) }}</td>
                                        <td class="px-5 py-5 text-center font-black text-slate-900">{{ number_format($clinic['pending_bookings_count']) }}</td>
                                        <td class="px-5 py-5 text-center">
                                            <span class="rounded-full px-4 py-2 text-[10px] font-black uppercase tracking-[0.22em] {{ $clinic['status'] === 'active' ? 'border border-emerald-200 bg-emerald-50 text-emerald-700' : 'border border-slate-200 bg-slate-100 text-slate-600' }}">
                                                {{ $clinic['status'] }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-5 py-16 text-center text-sm font-bold text-slate-400">ยังไม่มีข้อมูลคลินิกในระบบ</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="space-y-6">
                    <section class="rounded-[2.5rem] border border-slate-200 bg-white p-6 shadow-sm">
                        <p class="text-[10px] font-black uppercase tracking-[0.24em] text-slate-400">Global Controls</p>
                        <h3 class="mt-3 text-2xl font-black tracking-tight text-slate-950">ศูนย์ควบคุมระบบกลาง</h3>
                        <div class="mt-5 space-y-3">
                            <a href="{{ route('portal.settings') }}" class="flex items-center justify-between rounded-2xl bg-slate-50 px-5 py-4 transition-all hover:bg-slate-100">
                                <div>
                                    <div class="font-black text-slate-900">Site Settings</div>
                                    <div class="text-sm font-bold text-slate-500">Global keys {{ number_format($stats['global_settings']) }} รายการ</div>
                                </div>
                                <i class="fa-solid fa-chevron-right text-slate-400"></i>
                            </a>
                            <a href="{{ route('portal.chatbot.settings') }}" class="flex items-center justify-between rounded-2xl bg-slate-50 px-5 py-4 transition-all hover:bg-slate-100">
                                <div>
                                    <div class="font-black text-slate-900">Chatbot Settings</div>
                                    <div class="text-sm font-bold text-slate-500">ตั้งค่าโมเดลและ daily quota แยกตามคลินิก</div>
                                </div>
                                <i class="fa-solid fa-chevron-right text-slate-400"></i>
                            </a>
                            <a href="{{ route('portal.chatbot.faqs') }}" class="flex items-center justify-between rounded-2xl bg-slate-50 px-5 py-4 transition-all hover:bg-slate-100">
                                <div>
                                    <div class="font-black text-slate-900">FAQ Manager</div>
                                    <div class="text-sm font-bold text-slate-500">ดูแลคำถามประจำของ LINE Chatbot ให้แต่ละคลินิก</div>
                                </div>
                                <i class="fa-solid fa-chevron-right text-slate-400"></i>
                            </a>
                        </div>
                    </section>

                    <section class="rounded-[2.5rem] border border-slate-200 bg-slate-950 p-6 text-white shadow-[0_24px_80px_rgba(15,23,42,0.18)]">
                        <p class="text-[10px] font-black uppercase tracking-[0.24em] text-white/45">Operations Pulse</p>
                        <div class="mt-5 space-y-4">
                            <div class="rounded-2xl bg-white/5 px-4 py-4">
                                <div class="text-[10px] font-black uppercase tracking-[0.22em] text-white/45">Staff Accounts</div>
                                <div class="mt-2 text-2xl font-black tracking-tight">{{ number_format($stats['total_staff']) }}</div>
                            </div>
                            <div class="rounded-2xl bg-white/5 px-4 py-4">
                                <div class="text-[10px] font-black uppercase tracking-[0.22em] text-white/45">Borrow Active</div>
                                <div class="mt-2 text-2xl font-black tracking-tight">{{ number_format($stats['active_borrow_records']) }}</div>
                            </div>
                        </div>
                    </section>
                </div>
            </section>
        </div>
    </div>
</x-portal-layout>
