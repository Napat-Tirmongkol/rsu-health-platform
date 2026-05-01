<p>การจองของคุณได้รับการยืนยันแล้ว</p>
<p>รหัสการจอง: {{ $booking->booking_code }}</p>
<p>บริการ: {{ $booking->campaign?->title }}</p>
<p>วันที่: {{ $booking->slot?->date?->format('d/m/Y') ?? '-' }}</p>
<p>เวลา: {{ $booking->slot ? substr((string) $booking->slot->start_time, 0, 5) : '-' }} น.</p>
