@extends('emails.layout')

@section('title', 'อนุมัติคำขอยืมอุปกรณ์')

@section('headline', 'อนุมัติคำขอยืมอุปกรณ์')

@section('content')
<p style="margin:0 0 24px;font-size:15px;color:#475569;">
    เรียน <strong>{{ $record->user?->name ?? 'ผู้ขอยืม' }}</strong>,
</p>
<p style="margin:0 0 28px;font-size:15px;color:#475569;line-height:1.7;">
    คำขอยืมอุปกรณ์ของคุณได้รับการอนุมัติแล้ว กรุณารับอุปกรณ์ได้ที่คลินิกตามกำหนดการด้านล่าง
</p>

{{-- Approved badge --}}
<table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:28px;">
    <tr>
        <td style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:12px;padding:16px 24px;text-align:center;">
            <p style="margin:0;font-size:13px;font-weight:700;color:#16a34a;letter-spacing:0.12em;text-transform:uppercase;">✓ อนุมัติแล้ว</p>
        </td>
    </tr>
</table>

{{-- Detail rows --}}
<table width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #e2e8f0;border-radius:12px;overflow:hidden;margin-bottom:28px;">
    <tr>
        <td width="36%" style="background:#f8fafc;padding:14px 20px;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:0.1em;color:#64748b;border-bottom:1px solid #e2e8f0;">อุปกรณ์</td>
        <td style="background:#ffffff;padding:14px 20px;font-size:14px;font-weight:600;color:#0f172a;border-bottom:1px solid #e2e8f0;">{{ $record->item?->name ?? $record->category?->name ?? 'อุปกรณ์' }}</td>
    </tr>
    <tr>
        <td style="background:#f8fafc;padding:14px 20px;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:0.1em;color:#64748b;border-bottom:1px solid #e2e8f0;">กำหนดคืน</td>
        <td style="background:#ffffff;padding:14px 20px;font-size:14px;font-weight:700;color:#dc2626;border-bottom:1px solid #e2e8f0;">{{ $record->due_date?->format('d/m/Y') ?? '-' }}</td>
    </tr>
    @if ($record->reason)
    <tr>
        <td style="background:#f8fafc;padding:14px 20px;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:0.1em;color:#64748b;">เหตุผล</td>
        <td style="background:#ffffff;padding:14px 20px;font-size:14px;color:#475569;">{{ $record->reason }}</td>
    </tr>
    @endif
</table>

<p style="margin:0;font-size:13px;color:#94a3b8;line-height:1.7;">
    กรุณาคืนอุปกรณ์ภายในวันกำหนด หากมีข้อสงสัยกรุณาติดต่อเจ้าหน้าที่คลินิก
</p>
@endsection
