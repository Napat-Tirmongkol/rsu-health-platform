<div style="font-family: Arial, sans-serif; color: #0f172a; line-height: 1.6;">
    <h2 style="margin-bottom: 16px;">แจ้งยกเลิกการจองนัดหมาย</h2>
    <p>เรียน {{ $booking->user?->name ?? 'ผู้รับบริการ' }},</p>
    <p>รายการนัดหมายของคุณถูกยกเลิกแล้วตามรายละเอียดด้านล่าง</p>
    <ul>
        <li>รหัสการจอง: {{ $booking->booking_code }}</li>
        <li>บริการ: {{ $booking->campaign?->title }}</li>
        <li>วันที่: {{ $booking->slot?->date?->format('d/m/Y') ?? '-' }}</li>
        <li>เวลา: {{ $booking->slot ? substr((string) $booking->slot->start_time, 0, 5) : '-' }} น.</li>
    </ul>
    <p>หากต้องการนัดหมายใหม่ กรุณาเข้าใช้งานผ่าน RSU Medical Hub</p>
</div>
