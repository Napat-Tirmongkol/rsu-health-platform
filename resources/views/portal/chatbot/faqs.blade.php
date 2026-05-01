<x-portal-layout title="Chatbot FAQs">
    <div class="py-10">
        <div class="mx-auto max-w-7xl space-y-6 px-6 lg:px-8">
            @if (session('message'))
                <div class="rounded-[2rem] border border-emerald-200 bg-emerald-50 px-6 py-5 text-sm font-bold text-emerald-700 shadow-sm">
                    {{ session('message') }}
                </div>
            @endif

            <section class="rounded-[2.5rem] border border-slate-200 bg-white p-6 shadow-sm lg:p-8">
                <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-[0.24em] text-sky-500">LINE Chatbot</p>
                        <h2 class="mt-3 text-3xl font-black tracking-tight text-slate-950">FAQ Manager</h2>
                        <p class="mt-2 max-w-3xl text-sm font-bold text-slate-500">
                            จัดการคำถามที่พบบ่อยของแต่ละคลินิก เพื่อให้ LINE chatbot ตอบคำถามพื้นฐานได้เร็วและสม่ำเสมอโดยไม่ต้องเรียก AI ทุกครั้ง
                        </p>
                    </div>

                    <form method="GET" action="{{ route('portal.chatbot.faqs') }}" class="flex flex-col gap-3 sm:flex-row">
                        <select name="clinic_id" class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-700">
                            @foreach($clinics as $clinic)
                                <option value="{{ $clinic->id }}" @selected($selectedClinic->id === $clinic->id)>{{ $clinic->name }}</option>
                            @endforeach
                        </select>
                        <input name="search" value="{{ $search }}" type="text" placeholder="ค้นหา question หรือ answer" class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-700">
                        <button type="submit" class="rounded-2xl bg-slate-950 px-5 py-3 text-[11px] font-black uppercase tracking-[0.22em] text-white">Apply</button>
                    </form>
                </div>
            </section>

            <div class="grid grid-cols-1 gap-6 xl:grid-cols-[minmax(0,1.05fr),minmax(24rem,0.95fr)]">
                <section class="rounded-[2.5rem] border border-slate-200 bg-white p-6 shadow-sm lg:p-8">
                    <p class="text-[10px] font-black uppercase tracking-[0.24em] text-slate-400">{{ $editingFaq ? 'Edit FAQ' : 'Create FAQ' }}</p>
                    <h3 class="mt-3 text-2xl font-black tracking-tight text-slate-950">{{ $editingFaq ? 'แก้ไขคำถามที่พบบ่อย' : 'เพิ่มคำถามที่พบบ่อยใหม่' }}</h3>

                    <form method="POST" action="{{ $editingFaq ? route('portal.chatbot.faqs.update', $editingFaq->id) : route('portal.chatbot.faqs.store') }}" class="mt-6 space-y-5">
                        @csrf
                        @if($editingFaq)
                            @method('PUT')
                        @endif
                        <input type="hidden" name="clinic_id" value="{{ $selectedClinic->id }}">

                        <div>
                            <label class="mb-2 block text-xs font-black uppercase tracking-[0.22em] text-slate-500">Question</label>
                            <input name="question" type="text" value="{{ old('question', $editingFaq?->question) }}" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4 text-sm font-bold text-slate-700">
                            @error('question') <p class="mt-2 text-sm font-bold text-rose-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="mb-2 block text-xs font-black uppercase tracking-[0.22em] text-slate-500">Answer</label>
                            <textarea name="answer" rows="6" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4 text-sm font-bold text-slate-700">{{ old('answer', $editingFaq?->answer) }}</textarea>
                            @error('answer') <p class="mt-2 text-sm font-bold text-rose-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="mb-2 block text-xs font-black uppercase tracking-[0.22em] text-slate-500">Keywords</label>
                            <input name="keywords" type="text" value="{{ old('keywords', $editingFaq ? implode(', ', $editingFaq->keywords ?? []) : '') }}" placeholder="เช่น เวลาเปิด, โทร, จอง, ติดต่อ" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4 text-sm font-bold text-slate-700">
                            <p class="mt-2 text-xs font-bold text-slate-400">คั่นด้วย comma เพื่อช่วยให้ bot จับ intent ได้เร็วขึ้น</p>
                        </div>

                        <label class="inline-flex items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-700">
                            <input name="is_active" type="checkbox" value="1" class="h-5 w-5 rounded border-slate-300 text-emerald-600" @checked(old('is_active', $editingFaq?->is_active ?? true))>
                            <span>เปิดใช้งาน FAQ นี้</span>
                        </label>

                        <div class="flex flex-col gap-3 sm:flex-row">
                            <button type="submit" class="flex-1 rounded-2xl bg-sky-600 px-6 py-4 text-[11px] font-black uppercase tracking-[0.22em] text-white shadow-lg shadow-sky-100">
                                {{ $editingFaq ? 'Update FAQ' : 'Create FAQ' }}
                            </button>
                            @if($editingFaq)
                                <a href="{{ route('portal.chatbot.faqs', ['clinic_id' => $selectedClinic->id]) }}" class="rounded-2xl border border-slate-200 bg-white px-6 py-4 text-center text-[11px] font-black uppercase tracking-[0.22em] text-slate-600">
                                    Cancel Edit
                                </a>
                            @endif
                        </div>
                    </form>
                </section>

                <section class="rounded-[2.5rem] border border-slate-200 bg-white p-6 shadow-sm lg:p-8">
                    <div class="flex items-end justify-between gap-4">
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-[0.24em] text-slate-400">FAQ List</p>
                            <h3 class="mt-3 text-2xl font-black tracking-tight text-slate-950">คำถามของ {{ $selectedClinic->name }}</h3>
                            <p class="mt-2 text-sm font-bold text-slate-500">หน้า {{ $faqs->lastPage() > 0 ? $faqs->currentPage() : 0 }} / {{ max($faqs->lastPage(), 1) }} · รวม {{ number_format($faqs->total()) }} รายการ</p>
                        </div>
                    </div>

                    <div class="mt-6 space-y-4">
                        @forelse($faqs as $faq)
                            <article class="rounded-[2rem] border border-slate-200 bg-slate-50 px-5 py-5">
                                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                                    <div class="space-y-3">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <h4 class="text-base font-black text-slate-950">{{ $faq->question }}</h4>
                                            <span class="rounded-full px-3 py-1 text-[10px] font-black uppercase tracking-[0.22em] {{ $faq->is_active ? 'border border-emerald-200 bg-emerald-50 text-emerald-700' : 'border border-slate-200 bg-slate-100 text-slate-500' }}">
                                                {{ $faq->is_active ? 'active' : 'inactive' }}
                                            </span>
                                        </div>
                                        <p class="text-sm font-bold leading-relaxed text-slate-500">{{ $faq->answer }}</p>
                                        <div class="flex flex-wrap gap-2">
                                            @foreach($faq->keywords ?? [] as $keyword)
                                                <span class="rounded-full bg-white px-3 py-1 text-[10px] font-black uppercase tracking-[0.18em] text-slate-500 ring-1 ring-slate-200">{{ $keyword }}</span>
                                            @endforeach
                                        </div>
                                    </div>

                                    <a href="{{ route('portal.chatbot.faqs', ['clinic_id' => $selectedClinic->id, 'edit' => $faq->id]) }}" class="inline-flex items-center justify-center rounded-2xl bg-white px-4 py-3 text-[11px] font-black uppercase tracking-[0.22em] text-slate-700 ring-1 ring-slate-200">
                                        Edit
                                    </a>
                                </div>
                            </article>
                        @empty
                            <div class="rounded-[2rem] border border-dashed border-slate-200 px-6 py-12 text-center text-sm font-bold text-slate-400">
                                ยังไม่มี FAQ สำหรับคลินิกนี้
                            </div>
                        @endforelse
                    </div>

                    @if($faqs->hasPages())
                        <div class="mt-6 border-t border-slate-100 pt-5">
                            {{ $faqs->onEachSide(1)->links() }}
                        </div>
                    @endif
                </section>
            </div>
        </div>
    </div>
</x-portal-layout>
