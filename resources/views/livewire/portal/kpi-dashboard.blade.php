<div class="space-y-10" wire:poll.60s>

    {{-- ══ SATISFACTION SURVEY ════════════════════════════════════ --}}
    <section>
        <div class="flex items-center gap-2 mb-5">
            <div class="w-2.5 h-2.5 rounded-full bg-amber-400"></div>
            <span class="text-[10px] font-black uppercase tracking-[.18em] text-slate-400">ความพึงพอใจ (Satisfaction Survey)</span>
            <span class="ml-auto text-[10px] text-slate-400">อัปเดต {{ $updatedAt }} น.</span>
        </div>

        {{-- 4 stat cards --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-5">

            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 relative overflow-hidden hover:-translate-y-0.5 transition-transform">
                <div class="absolute right-0 top-0 w-1 h-full rounded-r-2xl" style="background:linear-gradient(180deg,#F59E0B,#D97706)"></div>
                <p class="text-[10px] font-black uppercase tracking-wider text-gray-400 mb-2">คะแนนเฉลี่ย</p>
                <div class="text-3xl font-black text-gray-900 mb-1.5">
                    {{ $survey['avg'] !== null ? number_format($survey['avg'], 1) : '—' }}
                    <span class="text-sm font-semibold text-gray-400">/ 5</span>
                </div>
                <div class="flex gap-0.5">
                    @if ($survey['avg'] !== null)
                        @for ($i = 1; $i <= 5; $i++)
                            @if ($survey['avg'] >= $i)
                                <i class="fa-solid fa-star text-amber-400 text-[11px]"></i>
                            @elseif ($survey['avg'] >= $i - 0.5)
                                <i class="fa-solid fa-star-half-stroke text-amber-400 text-[11px]"></i>
                            @else
                                <i class="fa-regular fa-star text-gray-200 text-[11px]"></i>
                            @endif
                        @endfor
                    @else
                        <span class="text-xs text-gray-300">ยังไม่มีข้อมูล</span>
                    @endif
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 relative overflow-hidden hover:-translate-y-0.5 transition-transform">
                <div class="absolute right-0 top-0 w-1 h-full rounded-r-2xl" style="background:linear-gradient(180deg,#8B5CF6,#6D28D9)"></div>
                <p class="text-[10px] font-black uppercase tracking-wider text-gray-400 mb-2">ผลประเมินทั้งหมด</p>
                <div class="text-3xl font-black text-gray-900 mb-1.5">{{ number_format($survey['total']) }}</div>
                <span class="text-[10px] text-gray-400">
                    <i class="fa-solid fa-comment-dots mr-1 text-violet-400"></i>
                    มีความคิดเห็น {{ number_format($comments['total']) }} รายการ
                </span>
            </div>

            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 relative overflow-hidden hover:-translate-y-0.5 transition-transform">
                <div class="absolute right-0 top-0 w-1 h-full rounded-r-2xl" style="background:linear-gradient(180deg,#2563EB,#1D4ED8)"></div>
                <p class="text-[10px] font-black uppercase tracking-wider text-gray-400 mb-2">สัปดาห์นี้</p>
                <div class="text-3xl font-black text-gray-900 mb-1.5">{{ number_format($survey['thisWeek']) }}</div>
                @if ($survey['lastWeek'] > 0)
                    @php $pct = round(($survey['thisWeek'] - $survey['lastWeek']) / $survey['lastWeek'] * 100); @endphp
                    <span class="text-[10px] font-black px-2 py-0.5 rounded-full {{ $pct >= 0 ? 'text-emerald-700 bg-emerald-50' : 'text-red-600 bg-red-50' }}">
                        <i class="fa-solid {{ $pct >= 0 ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down' }} mr-0.5"></i>
                        {{ ($pct >= 0 ? '+' : '') . $pct }}% vs สัปดาห์ก่อน
                    </span>
                @else
                    <span class="text-[10px] text-gray-300">ไม่มีข้อมูลสัปดาห์ก่อน</span>
                @endif
            </div>

            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 relative overflow-hidden hover:-translate-y-0.5 transition-transform">
                <div class="absolute right-0 top-0 w-1 h-full rounded-r-2xl" style="background:linear-gradient(180deg,#10b981,#059669)"></div>
                <p class="text-[10px] font-black uppercase tracking-wider text-gray-400 mb-2">Satisfaction Rate</p>
                <div class="text-3xl font-black mb-1.5 text-emerald-600">{{ $survey['satRate'] }}%</div>
                <span class="text-[10px] text-gray-400">
                    <i class="fa-solid fa-face-smile mr-1 text-amber-400"></i>
                    ให้คะแนน 4–5 ดาว
                </span>
            </div>

        </div>

        {{-- Distribution + Comments --}}
        <div class="grid grid-cols-1 lg:grid-cols-5 gap-4">

            {{-- Star distribution --}}
            <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                <p class="text-xs font-black uppercase tracking-wider text-gray-400 mb-4">การกระจายคะแนน</p>
                @php $distMax = max(1, max($survey['dist'])); @endphp
                @for ($s = 5; $s >= 1; $s--)
                    @php
                        $cnt  = $survey['dist'][$s];
                        $pct  = $survey['total'] > 0 ? round($cnt / $survey['total'] * 100) : 0;
                        $barW = round($cnt / $distMax * 100);
                        $clr  = $s >= 4 ? '#F59E0B' : ($s === 3 ? '#fb923c' : '#ef4444');
                    @endphp
                    <div class="flex items-center gap-3 mb-3 last:mb-0">
                        <div class="flex items-center gap-1 w-12 shrink-0">
                            <i class="fa-solid fa-star text-[9px]" style="color:{{ $clr }}"></i>
                            <span class="text-xs font-black text-gray-600">{{ $s }}</span>
                        </div>
                        <div class="flex-1 bg-gray-100 rounded-full h-2 overflow-hidden">
                            <div class="h-full rounded-full transition-all duration-700" style="width:{{ $barW }}%;background:{{ $clr }}"></div>
                        </div>
                        <div class="w-16 text-right shrink-0 text-xs font-bold text-gray-700">
                            {{ number_format($cnt) }}
                            <span class="text-gray-400 font-normal">({{ $pct }}%)</span>
                        </div>
                    </div>
                @endfor
                @if ($survey['total'] === 0)
                    <p class="text-center text-sm text-gray-300 py-4">ยังไม่มีผลประเมิน</p>
                @endif
            </div>

            {{-- Comments --}}
            <div class="lg:col-span-3 bg-white rounded-2xl border border-gray-100 shadow-sm p-5 flex flex-col">
                <div class="flex items-center justify-between mb-4">
                    <p class="text-xs font-black uppercase tracking-wider text-gray-400">ความคิดเห็นล่าสุด</p>
                    @if ($comments['total'] > 0)
                        <span class="text-[10px] text-gray-400">
                            หน้า {{ $comments['page'] }}/{{ $comments['totalPages'] }} · {{ number_format($comments['total']) }} รายการ
                        </span>
                    @endif
                </div>

                @if ($comments['items']->isEmpty())
                    <div class="flex-1 flex flex-col items-center justify-center py-8 text-gray-300">
                        <i class="fa-regular fa-comment-dots text-3xl mb-2"></i>
                        <p class="text-sm">ยังไม่มีความคิดเห็น</p>
                    </div>
                @else
                    <div class="flex-1 overflow-hidden">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-gray-100">
                                    <th class="text-left text-[10px] font-black uppercase tracking-wider text-gray-400 pb-2 w-16">คะแนน</th>
                                    <th class="text-left text-[10px] font-black uppercase tracking-wider text-gray-400 pb-2">ความคิดเห็น</th>
                                    <th class="text-right text-[10px] font-black uppercase tracking-wider text-gray-400 pb-2 w-16">วันที่</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @foreach ($comments['items'] as $c)
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="py-2.5 pr-3">
                                            <div class="flex gap-0.5">
                                                @for ($i = 1; $i <= 5; $i++)
                                                    <i class="fa-{{ $c->score >= $i ? 'solid' : 'regular' }} fa-star text-[9px] {{ $c->score >= $i ? 'text-amber-400' : 'text-gray-200' }}"></i>
                                                @endfor
                                            </div>
                                        </td>
                                        <td class="py-2.5 pr-3 text-xs text-gray-700">
                                            {{ mb_strimwidth($c->comment, 0, 80, '…') }}
                                        </td>
                                        <td class="py-2.5 text-right text-[10px] text-gray-400 whitespace-nowrap">
                                            {{ \Carbon\Carbon::parse($c->created_at)->format('d/m/y') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if ($comments['totalPages'] > 1)
                        <div class="mt-4 pt-3 border-t border-gray-100 flex items-center justify-center gap-1 flex-wrap">
                            <button wire:click="prevCommentPage"
                                class="px-2 py-1 text-xs rounded-lg {{ $comments['page'] <= 1 ? 'text-gray-300 cursor-default' : 'text-gray-500 hover:bg-gray-100' }}"
                                {{ $comments['page'] <= 1 ? 'disabled' : '' }}>«</button>
                            @for ($p = max(1, $comments['page'] - 2); $p <= min($comments['totalPages'], $comments['page'] + 2); $p++)
                                <button
                                    wire:click="goToCommentPage({{ $p }})"
                                    class="px-2.5 py-1 text-xs rounded-lg font-semibold {{ $p === $comments['page'] ? 'text-white' : 'text-gray-500 hover:bg-gray-100' }}"
                                    style="{{ $p === $comments['page'] ? 'background:#7c3aed' : '' }}">
                                    {{ $p }}
                                </button>
                            @endfor
                            <button wire:click="nextCommentPage({{ $comments['totalPages'] }})"
                                class="px-2 py-1 text-xs rounded-lg {{ $comments['page'] >= $comments['totalPages'] ? 'text-gray-300 cursor-default' : 'text-gray-500 hover:bg-gray-100' }}"
                                {{ $comments['page'] >= $comments['totalPages'] ? 'disabled' : '' }}>»</button>
                        </div>
                    @endif
                @endif
            </div>

        </div>
    </section>

    {{-- ══ CAMPAIGN ════════════════════════════════════════════════ --}}
    <section>
        <div class="flex items-center gap-2 mb-5">
            <div class="w-2.5 h-2.5 rounded-full bg-emerald-500"></div>
            <span class="text-[10px] font-black uppercase tracking-[.18em] text-slate-400">แคมเปญ & การจอง</span>
        </div>

        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">

            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 relative overflow-hidden hover:-translate-y-0.5 transition-transform">
                <div class="absolute right-0 top-0 w-1 h-full rounded-r-2xl" style="background:linear-gradient(180deg,#10b981,#059669)"></div>
                <p class="text-[10px] font-black uppercase tracking-wider text-gray-400 mb-2">แคมเปญเปิดอยู่</p>
                <div class="text-3xl font-black mb-2 text-emerald-600">{{ number_format($campaign['active']) }}</div>
                <span class="text-[10px] font-black px-2 py-0.5 rounded-full text-emerald-700 bg-emerald-50">
                    <i class="fa-solid fa-circle-check mr-0.5"></i> Active
                </span>
            </div>

            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 relative overflow-hidden hover:-translate-y-0.5 transition-transform">
                <div class="absolute right-0 top-0 w-1 h-full rounded-r-2xl" style="background:linear-gradient(180deg,#3B82F6,#1D4ED8)"></div>
                <p class="text-[10px] font-black uppercase tracking-wider text-gray-400 mb-2">อัตราการจอง</p>
                <div class="text-3xl font-black text-gray-900 mb-2">{{ $campaign['bookingRate'] }}%</div>
                <div class="w-full bg-gray-100 rounded-full h-1.5">
                    <div class="h-full rounded-full" style="width:{{ $campaign['bookingRate'] }}%;background:#3B82F6"></div>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 relative overflow-hidden hover:-translate-y-0.5 transition-transform">
                <div class="absolute right-0 top-0 w-1 h-full rounded-r-2xl" style="background:linear-gradient(180deg,#10B981,#059669)"></div>
                <p class="text-[10px] font-black uppercase tracking-wider text-gray-400 mb-2">Completion Rate</p>
                <div class="text-3xl font-black text-gray-900 mb-2">{{ $campaign['completionRate'] }}%</div>
                <div class="w-full bg-gray-100 rounded-full h-1.5">
                    <div class="h-full rounded-full" style="width:{{ $campaign['completionRate'] }}%;background:#10B981"></div>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 relative overflow-hidden hover:-translate-y-0.5 transition-transform">
                <div class="absolute right-0 top-0 w-1 h-full rounded-r-2xl" style="background:linear-gradient(180deg,#F97316,#EA580C)"></div>
                <p class="text-[10px] font-black uppercase tracking-wider text-gray-400 mb-2">ที่นั่งทั้งหมด</p>
                <div class="text-3xl font-black text-gray-900 mb-2">
                    {{ number_format($campaign['usedQuota']) }}
                    <span class="text-sm font-semibold text-gray-400">/ {{ number_format($campaign['totalQuota']) }}</span>
                </div>
                <span class="text-[10px] text-gray-400">ที่นั่งถูกใช้งาน</span>
            </div>

        </div>
    </section>

    {{-- ══ USERS + e_BORROW ════════════════════════════════════════ --}}
    <section class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Users --}}
        <div>
            <div class="flex items-center gap-2 mb-5">
                <div class="w-2.5 h-2.5 rounded-full bg-violet-500"></div>
                <span class="text-[10px] font-black uppercase tracking-[.18em] text-slate-400">ผู้ใช้งาน</span>
            </div>
            <div class="grid grid-cols-3 gap-3">

                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 relative overflow-hidden">
                    <div class="absolute right-0 top-0 w-1 h-full rounded-r-2xl" style="background:linear-gradient(180deg,#8B5CF6,#6D28D9)"></div>
                    <p class="text-[9px] font-black uppercase tracking-wider text-gray-400 mb-1.5">ทั้งหมด</p>
                    <div class="text-2xl font-black text-gray-900">{{ number_format($users['total']) }}</div>
                </div>

                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 relative overflow-hidden">
                    <div class="absolute right-0 top-0 w-1 h-full rounded-r-2xl" style="background:linear-gradient(180deg,#EC4899,#BE185D)"></div>
                    <p class="text-[9px] font-black uppercase tracking-wider text-gray-400 mb-1.5">เดือนนี้</p>
                    <div class="text-2xl font-black text-gray-900">{{ number_format($users['thisMonth']) }}</div>
                    @if ($users['growth'] !== null)
                        <span class="text-[9px] font-black {{ $users['growth'] >= 0 ? 'text-emerald-600' : 'text-red-500' }}">
                            {{ ($users['growth'] >= 0 ? '+' : '') . $users['growth'] }}%
                        </span>
                    @endif
                </div>

                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 relative overflow-hidden">
                    <div class="absolute right-0 top-0 w-1 h-full rounded-r-2xl" style="background:linear-gradient(180deg,#06B6D4,#0891B2)"></div>
                    <p class="text-[9px] font-black uppercase tracking-wider text-gray-400 mb-1.5">เดือนก่อน</p>
                    <div class="text-2xl font-black text-gray-900">{{ number_format($users['lastMonth']) }}</div>
                </div>

            </div>
        </div>

        {{-- e_Borrow --}}
        <div>
            <div class="flex items-center gap-2 mb-5">
                <div class="w-2.5 h-2.5 rounded-full bg-orange-400"></div>
                <span class="text-[10px] font-black uppercase tracking-[.18em] text-slate-400">ระบบยืมอุปกรณ์ (e-Borrow)</span>
            </div>
            <div class="grid grid-cols-3 gap-3">

                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 relative overflow-hidden">
                    <div class="absolute right-0 top-0 w-1 h-full rounded-r-2xl" style="background:linear-gradient(180deg,#F97316,#EA580C)"></div>
                    <p class="text-[9px] font-black uppercase tracking-wider text-gray-400 mb-1.5">ทั้งหมด</p>
                    <div class="text-2xl font-black text-gray-900">{{ number_format($borrow['total']) }}</div>
                </div>

                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 relative overflow-hidden">
                    <div class="absolute right-0 top-0 w-1 h-full rounded-r-2xl" style="background:linear-gradient(180deg,#10b981,#059669)"></div>
                    <p class="text-[9px] font-black uppercase tracking-wider text-gray-400 mb-1.5">กำลังยืม</p>
                    <div class="text-2xl font-black text-emerald-600">{{ number_format($borrow['active']) }}</div>
                </div>

                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 relative overflow-hidden">
                    <div class="absolute right-0 top-0 w-1 h-full rounded-r-2xl" style="background:linear-gradient(180deg,#EF4444,#B91C1C)"></div>
                    <p class="text-[9px] font-black uppercase tracking-wider text-gray-400 mb-1.5">เกินกำหนด</p>
                    <div class="text-2xl font-black {{ $borrow['overdue'] > 0 ? 'text-red-500' : 'text-gray-900' }}">
                        {{ number_format($borrow['overdue']) }}
                    </div>
                    @if ($borrow['overdue'] > 0)
                        <span class="text-[9px] font-black text-red-500">⚠ ต้องติดตาม</span>
                    @endif
                </div>

            </div>
        </div>

    </section>

</div>
