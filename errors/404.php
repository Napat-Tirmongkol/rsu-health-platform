<?php http_response_code(404); ?>
<!doctype html>
<html lang="th">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>404 — ไม่พบหน้าที่ต้องการ</title>
  <link rel="icon" href="/e-campaignv2/favicon.ico">
  <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      font-family: 'Prompt', sans-serif;
      background: #f4f7f5;
      min-height: 100dvh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 24px;
      color: #0f172a;
    }

    .card {
      background: #fff;
      border: 1.5px solid #e2e8f0;
      border-radius: 24px;
      padding: 48px 44px 40px;
      max-width: 400px;
      width: 100%;
      text-align: center;
      animation: up .5s cubic-bezier(.16,1,.3,1) both;
      position: relative;
      overflow: hidden;
    }

    /* top accent bar */
    .card::before {
      content: '';
      position: absolute;
      top: 0; left: 0; right: 0;
      height: 3px;
      background: linear-gradient(90deg, #2e9e63, #6ee7b7);
      border-radius: 24px 24px 0 0;
    }

    @keyframes up {
      from { opacity: 0; transform: translateY(20px); }
      to   { opacity: 1; transform: translateY(0); }
    }

    .ghost-num {
      font-size: 7rem;
      font-weight: 900;
      color: #f0faf4;
      line-height: 1;
      letter-spacing: -.04em;
      margin-bottom: -8px;
      user-select: none;
    }

    .icon-wrap {
      width: 56px;
      height: 56px;
      background: #f0faf4;
      border-radius: 16px;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 20px;
      color: #2e9e63;
      font-size: 22px;
      border: 1.5px solid #c7e8d5;
    }

    h1 {
      font-size: 20px;
      font-weight: 800;
      color: #0f172a;
      margin-bottom: 8px;
      letter-spacing: -.01em;
    }

    .subtitle {
      font-size: 13px;
      font-weight: 500;
      color: #64748b;
      line-height: 1.7;
      margin-bottom: 32px;
    }

    .badge {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      background: #f0faf4;
      color: #2e7d52;
      font-size: 10px;
      font-weight: 800;
      letter-spacing: .12em;
      text-transform: uppercase;
      padding: 4px 12px;
      border-radius: 99px;
      border: 1px solid #c7e8d5;
      margin-bottom: 24px;
    }

    .btn-primary {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      background: #2e9e63;
      color: #fff;
      font-family: 'Prompt', sans-serif;
      font-weight: 700;
      font-size: 13px;
      padding: 12px 28px;
      border-radius: 12px;
      text-decoration: none;
      border: none;
      cursor: pointer;
      width: 100%;
      transition: background .18s, transform .18s;
      letter-spacing: .01em;
    }
    .btn-primary:hover {
      background: #237a4c;
      transform: translateY(-1px);
    }

    .btn-ghost {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 6px;
      color: #94a3b8;
      font-family: 'Prompt', sans-serif;
      font-size: 12px;
      font-weight: 600;
      text-decoration: none;
      background: none;
      border: none;
      cursor: pointer;
      margin-top: 10px;
      padding: 6px 12px;
      border-radius: 8px;
      transition: color .18s, background .18s;
      width: 100%;
    }
    .btn-ghost:hover {
      color: #374151;
      background: #f8fafc;
    }

    .divider {
      height: 1px;
      background: #f1f5f9;
      margin: 28px 0;
    }

    .help-row {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 5px;
      font-size: 11px;
      color: #94a3b8;
      font-weight: 500;
    }
    .help-row a {
      color: #2e9e63;
      font-weight: 700;
      text-decoration: none;
    }
    .help-row a:hover { text-decoration: underline; }

    @media (max-width: 480px) {
      .card { padding: 36px 24px 32px; }
      .ghost-num { font-size: 5rem; }
    }

    @media (prefers-reduced-motion: reduce) {
      .card { animation: none; }
    }
  </style>
</head>
<body>
  <div class="card">
    <div class="badge">
      <i class="fa-solid fa-magnifying-glass" style="font-size:9px"></i>
      Error 404
    </div>

    <div class="ghost-num">404</div>

    <div class="icon-wrap">
      <i class="fa-solid fa-compass"></i>
    </div>

    <h1>ไม่พบหน้าที่ต้องการ</h1>
    <p class="subtitle">
      ขออภัย ไม่พบหน้าที่คุณกำลังมองหา<br>
      อาจถูกย้าย ลบออก หรือ URL ไม่ถูกต้อง
    </p>

    <a href="javascript:history.back()" class="btn-primary">
      <i class="fa-solid fa-arrow-left" style="font-size:11px"></i>
      กลับหน้าก่อนหน้า
    </a>
    <a href="/" class="btn-ghost">
      <i class="fa-solid fa-house" style="font-size:10px"></i>
      ไปหน้าแรก
    </a>

    <div class="divider"></div>

    <div class="help-row">
      <i class="fa-solid fa-circle-info" style="font-size:10px"></i>
      หากพบปัญหา กรุณาติดต่อ
      <a href="mailto:healthy@rsu.ac.th">healthy@rsu.ac.th</a>
    </div>
  </div>
</body>
</html>
