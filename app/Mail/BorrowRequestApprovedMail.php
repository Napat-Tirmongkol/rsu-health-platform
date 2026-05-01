<?php

namespace App\Mail;

use App\Models\BorrowRecord;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BorrowRequestApprovedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public BorrowRecord $record)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'แจ้งอนุมัติคำขอยืมอุปกรณ์',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.borrow-request-approved',
        );
    }
}
