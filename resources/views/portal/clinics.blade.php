<x-portal-layout title="Clinic Directory">
    <div class="py-10">
        <div class="mx-auto max-w-7xl space-y-6 px-6 lg:px-8">
            <div class="flex items-center justify-between rounded-[2rem] border border-slate-200 bg-white px-6 py-5 shadow-sm">
                <div>
                    <p class="text-sm font-black text-slate-900">จำนวนคลินิกในระบบ</p>
                    <p class="mt-1 text-sm font-bold text-slate-500">หน้า {{ $clinics->lastPage() > 0 ? $clinics->currentPage() : 0 }} / {{ $clinics->lastPage() }} · รวม {{ number_format($clinics->total()) }} รายการ</p>
                </div>
                <a href="{{ route('portal.dashboard') }}" class="text-sm font-black text-sky-600 transition-colors hover:text-sky-700">กลับหน้า Portal Home</a>
            </div>

            <section class="overflow-hidden rounded-[2.5rem] border border-slate-200 bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-full whitespace-nowrap text-left">
                        <thead>
                            <tr class="border-b border-slate-100 bg-slate-50 text-[10px] font-black uppercase tracking-[0.22em] text-slate-400">
                                <th class="px-6 py-5">Clinic</th>
                                <th class="px-6 py-5">Code</th>
                                <th class="px-6 py-5">Domain</th>
                                <th class="px-6 py-5 text-center">Users</th>
                                <th class="px-6 py-5 text-center">Staff</th>
                                <th class="px-6 py-5 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($clinics as $clinic)
                                <tr class="hover:bg-slate-50/70">
                                    <td class="px-6 py-5">
                                        <div class="font-black text-slate-900">{{ $clinic->name }}</div>
                                        <div class="text-sm font-bold text-slate-500">{{ $clinic->slug }}</div>
                                    </td>
                                    <td class="px-6 py-5 font-bold text-slate-700">{{ $clinic->code ?: '-' }}</td>
                                    <td class="px-6 py-5 font-bold text-slate-700">{{ $clinic->domain ?: '-' }}</td>
                                    <td class="px-6 py-5 text-center font-black text-slate-900">{{ number_format($clinic->users_count) }}</td>
                                    <td class="px-6 py-5 text-center font-black text-slate-900">{{ number_format($clinic->staff_count) }}</td>
                                    <td class="px-6 py-5 text-center">
                                        <span class="rounded-full px-4 py-2 text-[10px] font-black uppercase tracking-[0.22em] {{ $clinic->status === 'active' ? 'border border-emerald-200 bg-emerald-50 text-emerald-700' : 'border border-slate-200 bg-slate-100 text-slate-600' }}">
                                            {{ $clinic->status }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-16 text-center text-sm font-bold text-slate-400">ยังไม่มีคลินิกในระบบ</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($clinics->hasPages())
                    <div class="border-t border-slate-100 px-6 py-5">
                        {{ $clinics->onEachSide(1)->links() }}
                    </div>
                @endif
            </section>
        </div>
    </div>
</x-portal-layout>
