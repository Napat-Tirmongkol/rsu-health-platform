// tests/portal.spec.js
const { test, expect } = require('@playwright/test');

const BASE_PREVIEW = 'http://171.102.216.219/plesk-site-preview/dev.healthycampus.rsu.ac.th/';
const TEST_TOKEN = 'RSU_TEST_2024';

test.describe('RSU Medical Portal Staging Tests', () => {

  test('should login and display the hub page', async ({ page }) => {
    // 1. เข้าหน้า Hub โดยตรงพร้อมคีย์ลับ (ไม่ต้องพึ่ง test_auth.php)
    await page.goto(`${BASE_PREVIEW}user/hub.php?test_token=${TEST_TOKEN}`);
    
    // 2. ตรวจสอบว่าเห็นหัวข้อ "ศูนย์รวมบริการ"
    const heading = page.locator('h1');
    await expect(heading).toContainText(/ศูนย์รวมบริการ/);
    
    // 3. ถ่ายรูปหน้าจอเก็บไว้ดูผล
    await page.screenshot({ path: 'test-results/hub-screenshot.png' });
  });

  test('profile page should load without 500 error', async ({ page }) => {
    // เข้าหน้า Profile พร้อมคีย์ลับ
    await page.goto(`${BASE_PREVIEW}user/profile.php?test_token=${TEST_TOKEN}`);
    
    // เช็คว่าไม่มีข้อความ Error 500
    const bodyText = await page.innerText('body');
    expect(bodyText).not.toContain('Internal Server Error');
    expect(bodyText).not.toContain('500');
    
    // เช็คว่าเห็นปุ่มบันทึกข้อมูล
    const saveBtn = page.locator('button:has-text("บันทึก")');
    await expect(saveBtn).toBeVisible();
    
    await page.screenshot({ path: 'test-results/profile-screenshot.png' });
  });

});
