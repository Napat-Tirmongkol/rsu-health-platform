<?php

namespace App\Livewire\User;

use App\Models\ChatMessage;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class SupportChat extends Component
{
    public $message = '';

    public function sendMessage()
    {
        $this->validate([
            'message' => 'required|string|max:1000',
        ]);

        ChatMessage::create([
            'clinic_id' => currentClinicId(),
            'user_id' => Auth::guard('user')->id(),
            'message' => $this->message,
        ]);

        $this->message = '';
        $this->dispatch('messageSent');
    }

    public function render()
    {
        $messages = ChatMessage::where('user_id', Auth::guard('user')->id())
            ->orderBy('created_at', 'asc')
            ->get();

        // Mark messages from staff as read
        ChatMessage::where('user_id', Auth::guard('user')->id())
            ->whereNotNull('staff_id')
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return view('livewire.user.support-chat', compact('messages'));
    }
}
