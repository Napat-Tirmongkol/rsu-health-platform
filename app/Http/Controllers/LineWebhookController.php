<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessLineMessage;
use App\Services\Line\LineSignatureVerifier;
use Illuminate\Http\Request;

class LineWebhookController extends Controller
{
    public function __invoke(Request $request, LineSignatureVerifier $verifier)
    {
        $payload = $request->getContent();
        $signature = $request->header('X-Line-Signature');

        if (! $verifier->isValid($payload, $signature)) {
            return response()->json(['message' => 'Invalid LINE signature'], 401);
        }

        $events = $request->input('events', []);
        $clinicId = currentClinicId();

        foreach ($events as $event) {
            if (($event['type'] ?? null) !== 'message') {
                continue;
            }

            if (($event['message']['type'] ?? null) !== 'text') {
                continue;
            }

            $lineUserId = data_get($event, 'source.userId');
            $replyToken = data_get($event, 'replyToken');
            $messageText = trim((string) data_get($event, 'message.text'));

            if (blank($lineUserId) || blank($replyToken) || $messageText === '') {
                continue;
            }

            ProcessLineMessage::dispatch(
                clinicId: $clinicId,
                lineUserId: $lineUserId,
                replyToken: $replyToken,
                messageText: $messageText,
            );
        }

        return response()->json(['ok' => true]);
    }
}
