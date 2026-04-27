<div class="flex h-[70vh] flex-col overflow-hidden rounded-3xl border border-slate-100 bg-white shadow-sm" wire:poll.10s x-data="{ scrollToBottom() { $nextTick(() => { $refs.chatbox.scrollTop = $refs.chatbox.scrollHeight }) } }" x-init="scrollToBottom()" @messageSent.window="scrollToBottom()">
    <div class="flex items-center gap-3 border-b border-slate-50 bg-emerald-50/50 px-6 py-4">
        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-600 text-white shadow-md">
            <i class="fa-solid fa-headset"></i>
        </div>
        <div>
            <h3 class="text-sm font-bold text-slate-800">ฝ่ายสนับสนุน RSU Health</h3>
            <p class="text-[10px] font-bold uppercase tracking-widest text-emerald-600">เจ้าหน้าที่พร้อมให้บริการ</p>
        </div>
    </div>

    <div class="flex-1 space-y-4 overflow-y-auto p-6 scroll-smooth" x-ref="chatbox">
        @forelse($messages as $msg)
            @php $isUser = is_null($msg->staff_id); @endphp
            <div class="flex {{ $isUser ? 'justify-end' : 'justify-start' }}">
                <div class="max-w-[80%]">
                    <div class="{{ $isUser ? 'rounded-tr-none bg-emerald-600 text-white shadow-md shadow-emerald-100' : 'rounded-tl-none bg-slate-100 text-slate-700' }} rounded-2xl px-4 py-3 text-xs">
                        {{ $msg->message }}
                    </div>
                    <p class="{{ $isUser ? 'text-right' : 'text-left' }} mt-1 text-[9px] text-slate-400">
                        {{ $msg->created_at->format('H:i') }}
                    </p>
                </div>
            </div>
        @empty
            <div class="flex h-full items-center justify-center text-center">
                <p class="text-xs text-slate-400">เริ่มส่งข้อความถึงเจ้าหน้าที่ได้เลย</p>
            </div>
        @endforelse
    </div>

    <div class="border-t border-slate-50 bg-white p-4">
        <form wire:submit.prevent="sendMessage" class="flex gap-2">
            <input
                type="text"
                wire:model="message"
                placeholder="พิมพ์ข้อความที่นี่..."
                class="flex-1 rounded-xl border-none bg-slate-50 px-4 py-3 text-xs outline-none transition-all focus:ring-2 focus:ring-emerald-500"
            >
            <button
                type="submit"
                class="flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-600 text-white shadow-lg shadow-emerald-100 transition-transform active:scale-95"
            >
                <i class="fa-solid fa-paper-plane text-sm"></i>
            </button>
        </form>
        @error('message')
            <p class="mt-2 text-[10px] font-bold text-rose-500">{{ $message }}</p>
        @enderror
    </div>
</div>
