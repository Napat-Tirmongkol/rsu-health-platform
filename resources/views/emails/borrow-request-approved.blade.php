<p>คำขอยืมอุปกรณ์ของคุณได้รับการอนุมัติแล้ว</p>
<p>อุปกรณ์: {{ $record->item?->name ?? $record->category?->name ?? 'อุปกรณ์' }}</p>
<p>กำหนดคืน: {{ $record->due_date?->format('d/m/Y') ?? '-' }}</p>
<p>เหตุผล: {{ $record->reason }}</p>
