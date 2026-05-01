<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'RSU Medical Hub')</title>
</head>
<body style="margin:0;padding:0;background-color:#f1f5f9;font-family:'Helvetica Neue',Arial,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f1f5f9;padding:32px 16px;">
    <tr>
        <td align="center">
            <table width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;">

                {{-- Header --}}
                <tr>
                    <td style="background:linear-gradient(135deg,#0c4a6e,#0369a1);border-radius:16px 16px 0 0;padding:32px 40px;text-align:center;">
                        <p style="margin:0 0 4px;font-size:11px;font-weight:800;letter-spacing:0.22em;text-transform:uppercase;color:rgba(186,230,253,0.8);">RSU Medical Hub</p>
                        <h1 style="margin:0;font-size:22px;font-weight:900;color:#ffffff;letter-spacing:-0.5px;">@yield('headline')</h1>
                    </td>
                </tr>

                {{-- Body --}}
                <tr>
                    <td style="background:#ffffff;padding:40px;border-left:1px solid #e2e8f0;border-right:1px solid #e2e8f0;">
                        @yield('content')
                    </td>
                </tr>

                {{-- Footer --}}
                <tr>
                    <td style="background:#f8fafc;border:1px solid #e2e8f0;border-top:none;border-radius:0 0 16px 16px;padding:24px 40px;text-align:center;">
                        <p style="margin:0 0 6px;font-size:12px;font-weight:700;color:#64748b;">RSU Medical Clinic Services</p>
                        <p style="margin:0;font-size:11px;color:#94a3b8;">อีเมลนี้ส่งโดยอัตโนมัติ กรุณาอย่าตอบกลับ</p>
                    </td>
                </tr>

            </table>
        </td>
    </tr>
</table>
</body>
</html>
