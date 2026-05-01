@extends('emails.layout')

@section('title', 'รับคำขอจองนัดหมาย')

@section('headline', 'รับคำขอจองนัดหมายแล้ว')

@section('content')
<p style="margin:0 0 24px;font-size:15px;color:#475569;">
    เรียน <strong>{{ $booking->user?->name ?? 'ผู้รับบริการ' }}</strong>,
</p>
<p style="margin:0 0 28px;font-size:15px;color:#475569;line-height:1.7;">
    เราได้รับคำขอจองนัดหมายของคุณแล้ว กรุณารอการยืนยันจากเจ้าหน้าที่คลินิก
</p>

{{-- Booking code badge --}}
<table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:28px;">
    <tr>
        <td style="background:linear-gradient(135deg,#0c4a6e,#0369a1);border-radius:12px;padding:20px 24px;text-align:center;">
            <p style="margin:0 0 4px;font-size:11px;font-weight:800;letter-spacing:0.2em;text-transform:uppercase;color:rgba(186,230,253,0.8);">รหัสการจอง</p>
            <p style="margin:0;font-size:28px;font-weight:900;color:#ffffff;letter-spacing:0.12em;">{{ $booking->booking_code }}</p>
        </td>
    </tr>
</table>

{{-- Status badge --}}
<table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:28px;">
    <tr>
        <td style="background:#fffbeb;border:1px solid #fde68a;border-radius:12px;padding:16px 24px;text-align:center;">
            <p style="margin:0;font-size:13px;font-weight:700;color:#d97706;letter-spacing:0.12em;text-transform:uppercase;">⏳ รอการยืนยัน</p>
        </td>
    </tr>
</table>

{{-- Detail rows --}}
<table width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #e2e8f0;border-radius:12px;overflow:hidden;margin-bottom:28px;">
    <tr>
        <td width="36%" style="background:#f8fafc;padding:14px 20px;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:0.1em;color:#64748b;border-bottom:1px solid #e2e8f0;">บริการ</td>
        <td style="background:#ffffff;padding:14px 20px;font-size:14px;font-weight:600;color:#0f172a;border-bottom:1px solid #e2e8f0;">{{ $booking->campaign?->title ?? '-' }}</td>
    </tr>
    <tr>
        <td style="background:#f8fafc;padding:14px 20px;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:0.1em;color:#64748b;border-bottom:1px solid #e2e8f0;">วันที่</td>
        <td style="background:#ffffff;padding:14px 20px;font-size:14px;font-weight:600;color:#0f172a;border-bottom:1px solid #e2e8f0;">{{ $booking->slot?->date?->format('d/m/Y') ?? '-' }}</td>
    </tr>
    <tr>
        <td style="background:#f8fafc;padding:14px 20px;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:0.1em;color:#64748b;">เวลา</td>
        <td style="background:#ffffff;padding:14px 20px;font-size:14px;font-weight:600;color:#0f172a;">{{ $booking->slot ? substr((string) $booking->slot->start_time, 0, 5) : '-' }} น.</td>
    </tr>
</table>

<p style="margin:0;font-size:13px;color:#94a3b8;line-height:1.7;">
    คุณจะได้รับการแจ้งเตือนอีกครั้งเมื่อการจองได้รับการยืนยัน
</p>
@endsection
