<?php

namespace App\Jobs;

use App\Services\Chatbot\ChatbotOrchestrator;
use App\Services\Line\LineMessagingService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessLineMessage implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly int $clinicId,
        public readonly string $lineUserId,
        public readonly string $replyToken,
        public readonly string $messageText,
    ) {
    }

    public function handle(ChatbotOrchestrator $orchestrator, LineMessagingService $line): void
    {
        $result = $orchestrator->handle(
            clinicId: $this->clinicId,
            lineUserId: $this->lineUserId,
            message: $this->messageText,
        );

        $line->replyText($this->replyToken, $result['reply']);
    }
}
