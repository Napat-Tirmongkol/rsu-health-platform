<?php

namespace App\Services\Chatbot;

use App\Models\ChatbotConversation;
use App\Models\ChatbotMessage;
use Illuminate\Support\Collection;

class ConversationManager
{
    public function findOrCreateConversation(int $clinicId, string $lineUserId): ChatbotConversation
    {
        $conversation = ChatbotConversation::withoutGlobalScopes()->firstOrCreate(
            [
                'clinic_id' => $clinicId,
                'line_user_id' => $lineUserId,
            ],
            [
                'last_active_at' => now(),
            ]
        );

        $conversation->forceFill([
            'last_active_at' => now(),
        ])->save();

        return $conversation;
    }

    public function storeUserMessage(ChatbotConversation $conversation, string $content, ?string $intent = null): ChatbotMessage
    {
        return $this->storeMessage($conversation, 'user', $content, $intent);
    }

    public function storeAssistantMessage(ChatbotConversation $conversation, string $content, ?string $intent = null, ?int $tokensUsed = null): ChatbotMessage
    {
        return $this->storeMessage($conversation, 'assistant', $content, $intent, $tokensUsed);
    }

    public function recentMessages(ChatbotConversation $conversation, int $limit = 5): Collection
    {
        return ChatbotMessage::withoutGlobalScopes()
            ->where('conversation_id', $conversation->id)
            ->latest('id')
            ->limit($limit)
            ->get()
            ->reverse()
            ->values();
    }

    public function countUserMessagesToday(int $clinicId, string $lineUserId): int
    {
        return ChatbotMessage::withoutGlobalScopes()
            ->where('clinic_id', $clinicId)
            ->where('role', 'user')
            ->whereHas('conversation', function ($query) use ($lineUserId) {
                $query->withoutGlobalScopes()->where('line_user_id', $lineUserId);
            })
            ->whereDate('created_at', today())
            ->count();
    }

    private function storeMessage(ChatbotConversation $conversation, string $role, string $content, ?string $intent = null, ?int $tokensUsed = null): ChatbotMessage
    {
        return ChatbotMessage::create([
            'clinic_id' => $conversation->clinic_id,
            'conversation_id' => $conversation->id,
            'role' => $role,
            'content' => $content,
            'intent' => $intent,
            'tokens_used' => $tokensUsed,
        ]);
    }
}
