@extends('emails.layout')

@section('title', 'SMTP Test')

@section('headline', 'SMTP Connection Test')

@section('content')
<p style="margin:0 0 24px;font-size:15px;color:#475569;line-height:1.7;">
    นี่คืออีเมลทดสอบจาก <strong>RSU Health Platform</strong>
</p>

<table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:28px;">
    <tr>
        <td style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:12px;padding:20px 24px;text-align:center;">
            <p style="margin:0 0 6px;font-size:13px;font-weight:700;color:#16a34a;letter-spacing:0.12em;text-transform:uppercase;">✓ SMTP ทำงานปกติ</p>
            <p style="margin:0;font-size:12px;color:#4ade80;">การตั้งค่าอีเมลสามารถใช้งานได้แล้ว</p>
        </td>
    </tr>
</table>

@if (!empty($details))
<table width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #e2e8f0;border-radius:12px;overflow:hidden;margin-bottom:28px;">
    @foreach ($details as $label => $value)
    <tr>
        <td width="40%" style="background:#f8fafc;padding:12px 20px;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:0.1em;color:#64748b;border-bottom:1px solid #e2e8f0;">{{ $label }}</td>
        <td style="background:#ffffff;padding:12px 20px;font-size:13px;color:#0f172a;border-bottom:1px solid #e2e8f0;">{{ $value }}</td>
    </tr>
    @endforeach
</table>
@endif

<p style="margin:0;font-size:13px;color:#94a3b8;line-height:1.7;">
    หากคุณได้รับข้อความนี้ แสดงว่าการตั้งค่า SMTP สามารถใช้งานได้แล้ว
</p>
@endsection
