// tests/portal.spec.js
const { test, expect } = require('@playwright/test');

test.describe('RSU Medical Portal Staging Tests', () => {

  test('should login and display the hub page', async ({ page }) => {
    // 1. ล็อกอินผ่านตัวช่วย
    await page.goto('/user/test_auth.php');
    
    // 2. รอจนกว่าจะถึงหน้า hub.php
    await expect(page).toHaveURL(/.*hub.php/);
    
    // 3. ตรวจสอบว่าเห็นหัวข้อ "ศูนย์รวมบริการ" (หรือชื่อที่ตั้งไว้ใน lang.php)
    const heading = page.locator('h1');
    await expect(heading).toContainText(/ศูนย์รวมบริการ/);
    
    // 4. ถ่ายรูปหน้าจอเก็บไว้ดูผล
    await page.screenshot({ path: 'test-results/hub-screenshot.png' });
  });

  test('profile page should load without 500 error', async ({ page }) => {
    // ล็อกอินก่อน
    await page.goto('/user/test_auth.php');
    
    // ไปที่หน้า profile
    await page.goto('/user/profile.php');
    
    // เช็คว่าไม่มีข้อความ Error 500 หรือ Internal Server Error บนหน้าจอ
    const bodyText = await page.innerText('body');
    expect(bodyText).not.toContain('Internal Server Error');
    expect(bodyText).not.toContain('500');
    
    // เช็คว่าเห็นปุ่มบันทึกข้อมูล
    const saveBtn = page.locator('button:has-text("บันทึก")');
    await expect(saveBtn).toBeVisible();
    
    await page.screenshot({ path: 'test-results/profile-screenshot.png' });
  });

});
