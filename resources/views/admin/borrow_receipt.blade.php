<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt {{ $payment->receipt_number ?: '#'.$payment->id }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f8fafc;
            margin: 0;
            padding: 32px;
            color: #0f172a;
        }

        .sheet {
            max-width: 820px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 24px;
            box-shadow: 0 20px 45px rgba(15, 23, 42, 0.08);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #2e9e63, #10b981);
            color: #ffffff;
            padding: 32px;
        }

        .header h1 {
            margin: 0 0 8px;
            font-size: 28px;
        }

        .header p {
            margin: 0;
            opacity: 0.85;
        }

        .content {
            padding: 32px;
        }

        .grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-bottom: 24px;
        }

        .card {
            border: 1px solid #e2e8f0;
            border-radius: 18px;
            padding: 18px;
            background: #f8fafc;
        }

        .label {
            display: block;
            margin-bottom: 6px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: #64748b;
        }

        .value {
            font-size: 18px;
            font-weight: 700;
            color: #0f172a;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
        }

        .table th,
        .table td {
            padding: 14px 10px;
            border-bottom: 1px solid #e2e8f0;
            text-align: left;
        }

        .table th {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            color: #64748b;
        }

        .amount {
            font-size: 28px;
            font-weight: 800;
            color: #059669;
        }

        .print-actions {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            margin-top: 24px;
        }

        .button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 14px 20px;
            border-radius: 14px;
            border: 0;
            background: #0f172a;
            color: #ffffff;
            font-weight: 700;
            cursor: pointer;
        }

        .button.secondary {
            background: #e2e8f0;
            color: #0f172a;
        }

        @media print {
            body {
                background: #ffffff;
                padding: 0;
            }

            .sheet {
                box-shadow: none;
                border-radius: 0;
            }

            .print-actions {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="sheet">
        <div class="header">
            <h1>ใบเสร็จรับเงินค่าปรับ</h1>
            <p>RSU Medical Clinic · e-Borrow Receipt</p>
        </div>

        <div class="content">
            <div class="grid">
                <div class="card">
                    <span class="label">Receipt Number</span>
                    <div class="value">{{ $payment->receipt_number ?: 'N/A' }}</div>
                </div>
                <div class="card">
                    <span class="label">Payment Date</span>
                    <div class="value">{{ optional($payment->payment_date)->format('d M Y H:i') ?: '-' }}</div>
                </div>
                <div class="card">
                    <span class="label">Borrower</span>
                    <div class="value">{{ $borrower?->full_name ?: $borrower?->name ?: 'Unknown user' }}</div>
                </div>
                <div class="card">
                    <span class="label">Identity</span>
                    <div class="value">{{ $borrower?->identity_label ?? 'Identity' }}: {{ $borrower?->identity_value ?? '-' }}</div>
                </div>
            </div>

            <table class="table">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Category</th>
                        <th>Payment Method</th>
                        <th>Borrow Record</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>{{ $item?->name ?? '-' }}</td>
                        <td>{{ $category?->name ?? '-' }}</td>
                        <td>{{ $payment->payment_method }}</td>
                        <td>#{{ $record?->id ?? '-' }}</td>
                    </tr>
                </tbody>
            </table>

            <div class="grid" style="margin-top: 24px;">
                <div class="card">
                    <span class="label">Fine Amount</span>
                    <div class="value">{{ number_format((float) ($fine?->amount ?? 0), 2) }} บาท</div>
                </div>
                <div class="card">
                    <span class="label">Amount Paid</span>
                    <div class="amount">{{ number_format((float) $payment->amount_paid, 2) }} บาท</div>
                </div>
            </div>

            @if($payment->payment_notes || $fine?->notes)
                <div class="card" style="margin-top: 24px;">
                    <span class="label">Notes</span>
                    <div style="white-space: pre-wrap; line-height: 1.7; font-weight: 600; color: #334155;">
{{ trim(implode("\n", array_filter([$payment->payment_notes, $fine?->notes]))) }}
                    </div>
                </div>
            @endif

            <div class="print-actions">
                <button class="button secondary" onclick="window.close()">Close</button>
                <button class="button" onclick="window.print()">Print Receipt</button>
            </div>
        </div>
    </div>
</body>
</html>
