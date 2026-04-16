const { test, expect } = require('@playwright/test');

test('ทดสอบระบบ Login แอดมิน RSU Healthcare', async ({ page }) => {
  // 1. สั่งให้บอทเปิดหน้าเว็บ Login ของเรา (เปลี่ยน URL ตามเครื่องคุณโฟร์ก)
  await page.goto('http://localhost/rsu_clinic/admin/login.php');

  // 2. สั่งให้บอทพิมพ์ Username และ Password
  await page.locator('input[name="username"]').fill('admin_folk');
  await page.locator('input[name="password"]').fill('P@ssw0rd123');

  // 3. สั่งให้บอทกดปุ่ม "เข้าสู่ระบบ"
  await page.locator('button[type="submit"]').click();

  // 4. ตรวจสอบว่าเข้าหน้า Dashboard สำเร็จไหม (เช่น เช็กว่ามีคำว่า "System Governance" บนหน้าจอไหม)
  await expect(page.locator('text=System Governance')).toBeVisible();
  
  // (ออปชันเสริม) สั่งให้บอทแคปหน้าจอเก็บไว้ดูเป็นหลักฐาน
  await page.screenshot({ path: 'login-success.png' });
});