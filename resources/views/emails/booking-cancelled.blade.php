@extends('emails.layout')

@section('title', 'แจ้งยกเลิกการจองนัดหมาย')

@section('headline', 'แจ้งยกเลิกการจองนัดหมาย')

@section('content')
<p style="margin:0 0 24px;font-size:15px;color:#475569;">
    เรียน <strong>{{ $booking->user?->name ?? 'ผู้รับบริการ' }}</strong>,
</p>
<p style="margin:0 0 28px;font-size:15px;color:#475569;line-height:1.7;">
    รายการนัดหมายของคุณถูกยกเลิกแล้วตามรายละเอียดด้านล่าง หากคุณไม่ได้ดำเนินการยกเลิกด้วยตัวเอง กรุณาติดต่อเจ้าหน้าที่
</p>

{{-- Cancelled badge --}}
<table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:28px;">
    <tr>
        <td style="background:#fef2f2;border:1px solid #fecaca;border-radius:12px;padding:16px 24px;text-align:center;">
            <p style="margin:0;font-size:13px;font-weight:700;color:#ef4444;letter-spacing:0.12em;text-transform:uppercase;">ยกเลิกการจองแล้ว</p>
        </td>
    </tr>
</table>

{{-- Detail rows --}}
<table width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #e2e8f0;border-radius:12px;overflow:hidden;margin-bottom:28px;">
    <tr>
        <td width="36%" style="background:#f8fafc;padding:14px 20px;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:0.1em;color:#64748b;border-bottom:1px solid #e2e8f0;">รหัสการจอง</td>
        <td style="background:#ffffff;padding:14px 20px;font-size:14px;font-weight:700;color:#0f172a;letter-spacing:0.06em;border-bottom:1px solid #e2e8f0;">{{ $booking->booking_code }}</td>
    </tr>
    <tr>
        <td style="background:#f8fafc;padding:14px 20px;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:0.1em;color:#64748b;border-bottom:1px solid #e2e8f0;">บริการ</td>
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
    หากต้องการนัดหมายใหม่ กรุณาเข้าใช้งานผ่าน RSU Medical Hub
</p>
@endsection
