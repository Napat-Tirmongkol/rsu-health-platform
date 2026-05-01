<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BookingCancelledMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(public Booking $booking)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'แจ้งยกเลิกการจองนัดหมาย '.$this->booking->booking_code,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.booking-cancelled',
        );
    }
}
