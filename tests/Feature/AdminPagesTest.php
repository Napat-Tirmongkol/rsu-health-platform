<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Booking;
use App\Models\BorrowCategory;
use App\Models\BorrowItem;
use App\Models\BorrowPayment;
use App\Models\BorrowRecord;
use App\Models\Campaign;
use App\Models\Clinic;
use App\Models\Slot;
use App\Models\User;
use App\Mail\BookingCancelledMail;
use App\Mail\BookingConfirmedMail;
use App\Mail\BorrowRequestApprovedMail;
use App\Services\IntegrationSettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_pages_render_with_campaign_booking_data(): void
    {
        $clinic = Clinic::create([
            'name' => 'RSU Medical Clinic',
            'slug' => 'medical',
            'code' => 'RSU-MED',
            'status' => 'active',
        ]);

        $admin = Admin::create([
            'clinic_id' => $clinic->id,
            'name' => 'Clinic Admin',
            'email' => 'admin@example.com',
            'google_id' => 'google-admin-1',
        ]);

        $user = User::create([
            'clinic_id' => $clinic->id,
            'name' => 'Test Patient',
            'full_name' => 'Test Patient',
            'student_personnel_id' => '6600001',
            'phone_number' => '0812345678',
            'department' => 'Medical',
            'email' => 'patient@example.com',
            'password' => 'password',
        ]);

        $campaign = Campaign::create([
            'clinic_id' => $clinic->id,
            'title' => 'Flu Vaccine 2026',
            'description' => 'Seasonal campaign',
            'total_capacity' => 20,
            'status' => 'active',
        ]);

        $slot = Slot::create([
            'camp_id' => $campaign->id,
            'date' => now()->toDateString(),
            'start_time' => '09:00',
            'end_time' => '10:00',
            'max_slots' => 20,
            'status' => 'available',
        ]);

        Booking::create([
            'clinic_id' => $clinic->id,
            'user_id' => $user->id,
            'camp_id' => $campaign->id,
            'slot_id' => $slot->id,
            'status' => 'attended',
        ]);

        foreach ([
            'admin.dashboard',
            'admin.workspace.campaign',
            'admin.workspace.borrow',
            'admin.system_admins',
            'admin.campaigns',
            'admin.bookings',
            'admin.borrow_requests',
            'admin.inventory',
            'admin.borrow_returns',
            'admin.borrow_fines',
            'admin.walk_in_borrow',
            'admin.time_slots',
            'admin.manage_staff',
            'admin.activity_logs',
            'admin.reports',
            'admin.users',
        ] as $route) {
            $this->actingAs($admin, 'admin')
                ->withSession(['clinic_id' => $clinic->id])
                ->get(route($route))
                ->assertOk();
        }
    }

    public function test_admin_pages_show_identity_label_and_value_for_general_user(): void
    {
        $clinic = Clinic::create([
            'name' => 'RSU Medical Clinic',
            'slug' => 'medical',
            'code' => 'RSU-MED',
            'status' => 'active',
        ]);

        $admin = Admin::create([
            'clinic_id' => $clinic->id,
            'name' => 'Clinic Admin',
            'email' => 'passport-admin@example.com',
            'google_id' => 'google-admin-passport',
        ]);

        $user = User::create([
            'clinic_id' => $clinic->id,
            'name' => 'Foreign Visitor',
            'full_name' => 'Foreign Visitor',
            'status' => 'other',
            'citizen_id' => 'AB1234567',
            'phone_number' => '0812345678',
            'department' => 'External',
            'email' => 'passport-visitor@example.com',
            'password' => 'password',
        ]);

        $campaign = Campaign::create([
            'clinic_id' => $clinic->id,
            'title' => 'General Checkup',
            'description' => 'Walk-in campaign',
            'total_capacity' => 10,
            'status' => 'active',
        ]);

        $slot = Slot::create([
            'camp_id' => $campaign->id,
            'date' => now()->toDateString(),
            'start_time' => '13:00',
            'end_time' => '14:00',
            'max_slots' => 10,
            'status' => 'available',
        ]);

        Booking::create([
            'clinic_id' => $clinic->id,
            'user_id' => $user->id,
            'camp_id' => $campaign->id,
            'slot_id' => $slot->id,
            'status' => 'confirmed',
        ]);

        $this->actingAs($admin, 'admin')
            ->withSession(['clinic_id' => $clinic->id])
            ->get(route('admin.users'))
            ->assertOk();

        $this->actingAs($admin, 'admin')
            ->withSession(['clinic_id' => $clinic->id])
            ->get(route('admin.bookings'))
            ->assertOk()
            ->assertSee('Passport');

        $this->actingAs($admin, 'admin')
            ->withSession(['clinic_id' => $clinic->id])
            ->get(route('admin.reports'))
            ->assertOk();
    }

    public function test_admin_campaign_page_includes_scanner_entrypoint(): void
    {
        $clinic = Clinic::create([
            'name' => 'RSU Medical Clinic',
            'slug' => 'medical',
            'code' => 'RSU-MED',
            'status' => 'active',
        ]);

        $admin = Admin::create([
            'clinic_id' => $clinic->id,
            'name' => 'Clinic Admin',
            'email' => 'scanner-admin@example.com',
            'google_id' => 'google-admin-scanner',
        ]);

        $this->actingAs($admin, 'admin')
            ->withSession(['clinic_id' => $clinic->id])
            ->get(route('admin.campaigns'))
            ->assertOk()
            ->assertSee('Open Scanner');
    }

    public function test_admin_dashboard_shows_platform_workspace_switcher(): void
    {
        $clinic = Clinic::create([
            'name' => 'RSU Medical Clinic',
            'slug' => 'medical',
            'code' => 'RSU-MED',
            'status' => 'active',
        ]);

        $admin = Admin::create([
            'clinic_id' => $clinic->id,
            'name' => 'Dashboard Admin',
            'email' => 'dashboard-admin@example.com',
            'google_id' => 'google-admin-dashboard',
        ]);

        $this->actingAs($admin, 'admin')
            ->withSession(['clinic_id' => $clinic->id])
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('RSU Operations Platform');
    }

    public function test_admin_borrow_workspace_shows_command_center(): void
    {
        $clinic = Clinic::create([
            'name' => 'RSU Medical Clinic',
            'slug' => 'medical',
            'code' => 'RSU-MED',
            'status' => 'active',
        ]);

        $admin = Admin::create([
            'clinic_id' => $clinic->id,
            'name' => 'Borrow Workspace Admin',
            'email' => 'borrow-workspace-admin@example.com',
            'google_id' => 'google-admin-borrow-workspace',
        ]);

        $this->actingAs($admin, 'admin')
            ->withSession(['clinic_id' => $clinic->id])
            ->get(route('admin.workspace.borrow'))
            ->assertOk()
            ->assertSee('Borrow Operations Command Center');
    }

    public function test_admin_campaign_workspace_shows_clinic_services_context(): void
    {
        $clinic = Clinic::create([
            'name' => 'RSU Medical Clinic',
            'slug' => 'medical',
            'code' => 'RSU-MED',
            'status' => 'active',
        ]);

        $admin = Admin::create([
            'clinic_id' => $clinic->id,
            'name' => 'Campaign Workspace Admin',
            'email' => 'campaign-workspace-admin@example.com',
            'google_id' => 'google-admin-campaign-workspace',
        ]);

        $this->actingAs($admin, 'admin')
            ->withSession(['clinic_id' => $clinic->id])
            ->get(route('admin.workspace.campaign'))
            ->assertOk()
            ->assertSee('Clinic Services Workspace');
    }

    public function test_admin_can_approve_and_reject_borrow_requests(): void
    {
        $clinic = Clinic::create([
            'name' => 'RSU Medical Clinic',
            'slug' => 'medical',
            'code' => 'RSU-MED',
            'status' => 'active',
        ]);

        $admin = Admin::create([
            'clinic_id' => $clinic->id,
            'name' => 'Borrow Admin',
            'email' => 'borrow-admin@example.com',
            'google_id' => 'google-admin-borrow',
        ]);

        $user = User::create([
            'clinic_id' => $clinic->id,
            'name' => 'Borrow User',
            'full_name' => 'Borrow User',
            'student_personnel_id' => '6700001',
            'phone_number' => '0811111111',
            'department' => 'Engineering',
            'email' => 'borrow-user@example.com',
            'password' => 'password',
        ]);

        $category = BorrowCategory::create([
            'clinic_id' => $clinic->id,
            'name' => 'Laptop',
            'description' => 'Portable device',
            'is_active' => true,
        ]);

        $item = BorrowItem::create([
            'clinic_id' => $clinic->id,
            'category_id' => $category->id,
            'name' => 'Dell Latitude',
            'serial_number' => 'DL-1001',
            'status' => 'available',
        ]);

        $record = BorrowRecord::create([
            'clinic_id' => $clinic->id,
            'category_id' => $category->id,
            'borrower_user_id' => $user->id,
            'quantity' => 1,
            'reason' => 'Need for class',
            'approval_status' => 'pending',
            'status' => 'borrowed',
            'fine_status' => 'none',
        ]);

        $this->actingAs($admin, 'admin')
            ->withSession(['clinic_id' => $clinic->id])
            ->get(route('admin.borrow_requests'))
            ->assertOk()
            ->assertSee('Borrow Requests')
            ->assertSee('Borrow User');

        Livewire::actingAs($admin, 'admin');

        Livewire::test(\App\Livewire\Admin\BorrowRequestManager::class)
            ->call('approve', $record->id);

        $this->assertDatabaseHas('borrow_records', [
            'id' => $record->id,
            'approval_status' => 'approved',
            'item_id' => $item->id,
        ]);

        $this->assertDatabaseHas('borrow_items', [
            'id' => $item->id,
            'status' => 'borrowed',
        ]);

        $secondRecord = BorrowRecord::create([
            'clinic_id' => $clinic->id,
            'category_id' => $category->id,
            'borrower_user_id' => $user->id,
            'quantity' => 1,
            'reason' => 'Backup request',
            'approval_status' => 'pending',
            'status' => 'borrowed',
            'fine_status' => 'none',
        ]);

        Livewire::test(\App\Livewire\Admin\BorrowRequestManager::class)
            ->call('reject', $secondRecord->id);

        $this->assertDatabaseHas('borrow_records', [
            'id' => $secondRecord->id,
            'approval_status' => 'rejected',
        ]);

        $this->assertDatabaseHas('sys_activity_logs', [
            'clinic_id' => $clinic->id,
            'action' => 'borrow.approved',
        ]);

        $this->assertDatabaseHas('sys_activity_logs', [
            'clinic_id' => $clinic->id,
            'action' => 'borrow.rejected',
        ]);
    }

    public function test_admin_can_manage_inventory_categories_and_items(): void
    {
        Storage::fake('public');

        $clinic = Clinic::create([
            'name' => 'RSU Medical Clinic',
            'slug' => 'medical',
            'code' => 'RSU-MED',
            'status' => 'active',
        ]);

        $admin = Admin::create([
            'clinic_id' => $clinic->id,
            'name' => 'Inventory Admin',
            'email' => 'inventory-admin@example.com',
            'google_id' => 'google-admin-inventory',
        ]);

        $this->actingAs($admin, 'admin')
            ->withSession(['clinic_id' => $clinic->id])
            ->get(route('admin.inventory'))
            ->assertOk()
            ->assertSee('Inventory');

        Livewire::actingAs($admin, 'admin');

        Livewire::test(\App\Livewire\Admin\InventoryManager::class)
            ->call('openCreateCategory')
            ->set('categoryName', 'Tablet')
            ->set('categoryDescription', 'Devices for patient education')
            ->set('categoryIsActive', true)
            ->set('categoryImage', UploadedFile::fake()->image('tablet.png', 600, 600))
            ->call('saveCategory');

        $category = BorrowCategory::firstWhere('name', 'Tablet');

        $this->assertNotNull($category);
        $this->assertNotNull($category->image_path);
        Storage::disk('public')->assertExists($category->image_path);

        Livewire::test(\App\Livewire\Admin\InventoryManager::class)
            ->call('openCreateItem')
            ->set('itemCategoryId', $category->id)
            ->set('itemName', 'iPad Air')
            ->set('itemDescription', 'Borrowable tablet')
            ->set('itemSerialNumber', 'IPAD-001')
            ->set('itemStatus', 'available')
            ->call('saveItem');

        $this->assertDatabaseHas('borrow_items', [
            'category_id' => $category->id,
            'name' => 'iPad Air',
            'serial_number' => 'IPAD-001',
            'status' => 'available',
        ]);

        $this->assertDatabaseHas('borrow_categories', [
            'id' => $category->id,
            'total_quantity' => 1,
            'available_quantity' => 1,
        ]);

        $viewer = User::create([
            'clinic_id' => $clinic->id,
            'name' => 'Borrow Viewer',
            'email' => 'borrow-viewer@example.com',
            'line_user_id' => 'line-borrow-viewer',
            'password' => Hash::make('password'),
        ]);

        $this->actingAs($viewer, 'user')
            ->withSession(['clinic_id' => $clinic->id])
            ->get(route('user.borrow.index'))
            ->assertOk()
            ->assertSee(Storage::disk('public')->url($category->image_path), false);
    }

    public function test_admin_can_process_borrow_return_with_fine_payment(): void
    {
        $clinic = Clinic::create([
            'name' => 'RSU Medical Clinic',
            'slug' => 'medical',
            'code' => 'RSU-MED',
            'status' => 'active',
        ]);

        $admin = Admin::create([
            'clinic_id' => $clinic->id,
            'name' => 'Return Admin',
            'email' => 'return-admin@example.com',
            'google_id' => 'google-admin-return',
        ]);

        $user = User::create([
            'clinic_id' => $clinic->id,
            'name' => 'Late Borrower',
            'full_name' => 'Late Borrower',
            'student_personnel_id' => '6700002',
            'phone_number' => '0822222222',
            'department' => 'Science',
            'email' => 'late-borrower@example.com',
            'password' => 'password',
        ]);

        $category = BorrowCategory::create([
            'clinic_id' => $clinic->id,
            'name' => 'Camera',
            'description' => 'Recording tools',
            'is_active' => true,
            'total_quantity' => 1,
            'available_quantity' => 0,
        ]);

        $item = BorrowItem::create([
            'clinic_id' => $clinic->id,
            'category_id' => $category->id,
            'name' => 'Sony ZV-E10',
            'serial_number' => 'CAM-001',
            'status' => 'borrowed',
        ]);

        $record = BorrowRecord::create([
            'clinic_id' => $clinic->id,
            'category_id' => $category->id,
            'item_id' => $item->id,
            'borrower_user_id' => $user->id,
            'quantity' => 1,
            'reason' => 'Media class',
            'borrowed_at' => now()->subDays(7),
            'due_date' => now()->subDays(3)->toDateString(),
            'approval_status' => 'approved',
            'status' => 'borrowed',
            'fine_status' => 'none',
        ]);

        $this->actingAs($admin, 'admin')
            ->withSession(['clinic_id' => $clinic->id])
            ->get(route('admin.borrow_returns'))
            ->assertOk();

        Livewire::actingAs($admin, 'admin');

        Livewire::test(\App\Livewire\Admin\BorrowReturnManager::class)
            ->call('openReturnModal', $record->id)
            ->set('collectFineNow', true)
            ->set('amountPaid', '30.00')
            ->set('paymentMethod', 'cash')
            ->set('paymentNotes', 'Collected at desk')
            ->set('returnNotes', 'Returned in good condition')
            ->call('processReturn');

        $this->assertDatabaseHas('borrow_records', [
            'id' => $record->id,
            'status' => 'returned',
            'fine_status' => 'paid',
        ]);

        $this->assertDatabaseHas('borrow_items', [
            'id' => $item->id,
            'status' => 'available',
        ]);

        $fine = \App\Models\BorrowFine::first();

        $this->assertNotNull($fine);

        $this->assertDatabaseHas('borrow_fines', [
            'borrow_record_id' => $record->id,
            'status' => 'paid',
            'amount' => 30,
        ]);

        $this->assertDatabaseHas('borrow_payments', [
            'fine_id' => $fine->id,
            'amount_paid' => 30,
            'payment_method' => 'cash',
        ]);

        $this->assertDatabaseHas('sys_activity_logs', [
            'clinic_id' => $clinic->id,
            'action' => 'borrow.returned',
        ]);
    }

    public function test_admin_can_record_pending_fine_payment_from_fine_manager(): void
    {
        $clinic = Clinic::create([
            'name' => 'RSU Medical Clinic',
            'slug' => 'medical',
            'code' => 'RSU-MED',
            'status' => 'active',
        ]);

        $admin = Admin::create([
            'clinic_id' => $clinic->id,
            'name' => 'Fine Admin',
            'email' => 'fine-admin@example.com',
            'google_id' => 'google-admin-fine',
        ]);

        $user = User::create([
            'clinic_id' => $clinic->id,
            'name' => 'Fine Borrower',
            'full_name' => 'Fine Borrower',
            'student_personnel_id' => '6700003',
            'phone_number' => '0833333333',
            'department' => 'Architecture',
            'email' => 'fine-borrower@example.com',
            'password' => 'password',
        ]);

        $category = BorrowCategory::create([
            'clinic_id' => $clinic->id,
            'name' => 'Projector',
            'description' => 'Presentation equipment',
            'is_active' => true,
        ]);

        $item = BorrowItem::create([
            'clinic_id' => $clinic->id,
            'category_id' => $category->id,
            'name' => 'Epson Projector',
            'serial_number' => 'PJ-100',
            'status' => 'available',
        ]);

        $record = BorrowRecord::create([
            'clinic_id' => $clinic->id,
            'category_id' => $category->id,
            'item_id' => $item->id,
            'borrower_user_id' => $user->id,
            'quantity' => 1,
            'reason' => 'Seminar',
            'borrowed_at' => now()->subDays(4),
            'due_date' => now()->subDays(2)->toDateString(),
            'returned_at' => now()->subDay(),
            'approval_status' => 'approved',
            'status' => 'returned',
            'fine_status' => 'pending',
        ]);

        $fine = \App\Models\BorrowFine::create([
            'clinic_id' => $clinic->id,
            'borrow_record_id' => $record->id,
            'user_id' => $user->id,
            'amount' => 20,
            'status' => 'pending',
            'notes' => 'Late return fine',
        ]);

        $this->actingAs($admin, 'admin')
            ->withSession(['clinic_id' => $clinic->id])
            ->get(route('admin.borrow_fines'))
            ->assertOk();

        Livewire::actingAs($admin, 'admin');

        Livewire::test(\App\Livewire\Admin\BorrowFineManager::class)
            ->call('openPaymentModal', $fine->id)
            ->set('amountPaid', '20.00')
            ->set('paymentMethod', 'bank_transfer')
            ->set('paymentNotes', 'Transferred by student')
            ->call('recordPayment');

        $this->assertDatabaseHas('borrow_fines', [
            'id' => $fine->id,
            'status' => 'paid',
        ]);

        $this->assertDatabaseHas('borrow_records', [
            'id' => $record->id,
            'fine_status' => 'paid',
        ]);

        $this->assertDatabaseHas('borrow_payments', [
            'fine_id' => $fine->id,
            'amount_paid' => 20,
            'payment_method' => 'bank_transfer',
        ]);

        $this->assertDatabaseHas('sys_activity_logs', [
            'clinic_id' => $clinic->id,
            'action' => 'borrow.fine_paid',
        ]);

        $payment = BorrowPayment::first();

        $this->actingAs($admin, 'admin')
            ->withSession(['clinic_id' => $clinic->id])
            ->get(route('admin.borrow_payments.receipt', $payment))
            ->assertOk()
            ->assertSee($payment->receipt_number);
    }

    public function test_admin_can_create_walk_in_borrow_for_multiple_items(): void
    {
        $clinic = Clinic::create([
            'name' => 'RSU Medical Clinic',
            'slug' => 'medical',
            'code' => 'RSU-MED',
            'status' => 'active',
        ]);

        $admin = Admin::create([
            'clinic_id' => $clinic->id,
            'name' => 'Walk-In Admin',
            'email' => 'walkin-admin@example.com',
            'google_id' => 'google-admin-walkin',
        ]);

        $user = User::create([
            'clinic_id' => $clinic->id,
            'name' => 'Walk In User',
            'full_name' => 'Walk In User',
            'student_personnel_id' => '6700004',
            'phone_number' => '0844444444',
            'department' => 'Nursing',
            'email' => 'walkin-user@example.com',
            'password' => 'password',
        ]);

        $category = BorrowCategory::create([
            'clinic_id' => $clinic->id,
            'name' => 'Microscope',
            'description' => 'Lab equipment',
            'is_active' => true,
            'total_quantity' => 2,
            'available_quantity' => 2,
        ]);

        $itemOne = BorrowItem::create([
            'clinic_id' => $clinic->id,
            'category_id' => $category->id,
            'name' => 'Microscope A',
            'serial_number' => 'MIC-001',
            'status' => 'available',
        ]);

        $itemTwo = BorrowItem::create([
            'clinic_id' => $clinic->id,
            'category_id' => $category->id,
            'name' => 'Microscope B',
            'serial_number' => 'MIC-002',
            'status' => 'available',
        ]);

        $this->actingAs($admin, 'admin')
            ->withSession(['clinic_id' => $clinic->id])
            ->get(route('admin.walk_in_borrow'))
            ->assertOk()
            ->assertSee('Walk-In Borrow');

        Livewire::actingAs($admin, 'admin');

        Livewire::test(\App\Livewire\Admin\WalkInBorrowManager::class)
            ->call('selectUser', $user->id)
            ->call('addItem', $itemOne->id)
            ->call('addItem', $itemTwo->id)
            ->set('dueDate', now()->addDays(5)->toDateString())
            ->set('reason', 'Issued at counter')
            ->call('submitWalkInBorrow');

        $this->assertDatabaseHas('borrow_records', [
            'borrower_user_id' => $user->id,
            'item_id' => $itemOne->id,
            'approval_status' => 'staff_added',
            'status' => 'borrowed',
        ]);

        $this->assertDatabaseHas('borrow_records', [
            'borrower_user_id' => $user->id,
            'item_id' => $itemTwo->id,
            'approval_status' => 'staff_added',
            'status' => 'borrowed',
        ]);

        $this->assertDatabaseHas('borrow_items', [
            'id' => $itemOne->id,
            'status' => 'borrowed',
        ]);

        $this->assertDatabaseHas('borrow_items', [
            'id' => $itemTwo->id,
            'status' => 'borrowed',
        ]);

        $this->assertDatabaseHas('borrow_categories', [
            'id' => $category->id,
            'available_quantity' => 0,
            'total_quantity' => 2,
        ]);

        $this->assertDatabaseHas('sys_activity_logs', [
            'clinic_id' => $clinic->id,
            'action' => 'borrow.walk_in_created',
        ]);
    }

    public function test_admin_module_permissions_limit_workspace_access(): void
    {
        $clinic = Clinic::create([
            'name' => 'RSU Medical Clinic',
            'slug' => 'medical',
            'code' => 'RSU-MED',
            'status' => 'active',
        ]);

        $campaignAdmin = Admin::create([
            'clinic_id' => $clinic->id,
            'name' => 'Campaign Only Admin',
            'email' => 'campaign-only-admin@example.com',
            'google_id' => 'google-admin-campaign-only',
            'module_permissions' => ['campaign'],
        ]);

        $this->actingAs($campaignAdmin, 'admin')
            ->withSession(['clinic_id' => $clinic->id])
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertDontSee('ยืมอุปกรณ์และคลัง');

        $this->actingAs($campaignAdmin, 'admin')
            ->withSession(['clinic_id' => $clinic->id])
            ->get(route('admin.workspace.campaign'))
            ->assertOk();

        $this->actingAs($campaignAdmin, 'admin')
            ->withSession(['clinic_id' => $clinic->id])
            ->get(route('admin.workspace.borrow'))
            ->assertForbidden();
    }

    public function test_manage_staff_page_is_staff_only_after_cleanup(): void
    {
        $clinic = Clinic::create([
            'name' => 'RSU Medical Clinic',
            'slug' => 'medical',
            'code' => 'RSU-MED',
            'status' => 'active',
        ]);

        $superAdmin = Admin::create([
            'clinic_id' => $clinic->id,
            'name' => 'Super Admin',
            'email' => 'super-admin@example.com',
            'google_id' => 'google-admin-super',
            'module_permissions' => ['*'],
        ]);

        $targetAdmin = Admin::create([
            'clinic_id' => $clinic->id,
            'name' => 'Target Admin',
            'email' => 'target-admin@example.com',
            'google_id' => 'google-admin-target',
            'module_permissions' => ['campaign'],
        ]);

        $this->actingAs($superAdmin, 'admin')
            ->withSession(['clinic_id' => $clinic->id])
            ->get(route('admin.manage_staff'))
            ->assertOk()
            ->assertSee('จัดการทีมเจ้าหน้าที่')
            ->assertDontSee('Admin Module Access');

        return;

        Livewire::actingAs($superAdmin, 'admin');

        Livewire::test(\App\Livewire\Admin\AdminAccessManager::class)
            ->call('openAccessModal', $targetAdmin->id)
            ->set('fullPlatformAccess', false)
            ->set('selectedModules', ['borrow'])
            ->call('saveAccess');

        $this->assertSame(['borrow'], $targetAdmin->fresh()->module_permissions);
    }

    public function test_full_platform_admin_can_access_system_admins_page(): void
    {
        $clinic = Clinic::create([
            'name' => 'RSU Medical Clinic',
            'slug' => 'medical',
            'code' => 'RSU-MED',
            'status' => 'active',
        ]);

        $superAdmin = Admin::create([
            'clinic_id' => $clinic->id,
            'name' => 'Platform Super Admin',
            'email' => 'platform-super-admin@example.com',
            'google_id' => 'google-admin-platform-super',
            'module_permissions' => ['*'],
            'default_workspace' => 'campaign',
        ]);

        $this->actingAs($superAdmin, 'admin')
            ->withSession(['clinic_id' => $clinic->id])
            ->get(route('admin.system_admins'))
            ->assertOk()
            ->assertSee('System Admins');
    }

    public function test_system_admin_manager_can_create_borrow_only_admin(): void
    {
        $clinic = Clinic::create([
            'name' => 'RSU Medical Clinic',
            'slug' => 'medical',
            'code' => 'RSU-MED',
            'status' => 'active',
        ]);

        $superAdmin = Admin::create([
            'clinic_id' => $clinic->id,
            'name' => 'Platform Super Admin',
            'email' => 'platform-super-admin-create@example.com',
            'google_id' => 'google-admin-platform-super-create',
            'module_permissions' => ['*'],
            'default_workspace' => 'campaign',
        ]);

        Livewire::actingAs($superAdmin, 'admin');

        Livewire::test(\App\Livewire\Admin\SystemAdminManager::class)
            ->call('openCreateModal')
            ->set('name', 'Borrow Supervisor')
            ->set('email', 'borrow-supervisor@example.com')
            ->set('google_id', 'google-borrow-supervisor')
            ->set('fullPlatformAccess', false)
            ->set('selectedModules', ['borrow'])
            ->set('selectedActions', ['borrow.request.approve', 'borrow.return.process'])
            ->set('defaultWorkspace', 'borrow')
            ->call('save');

        $this->assertDatabaseHas('sys_admins', [
            'email' => 'borrow-supervisor@example.com',
            'default_workspace' => 'borrow',
        ]);

        $created = Admin::where('email', 'borrow-supervisor@example.com')->firstOrFail();
        $this->assertSame(['borrow'], $created->module_permissions);
        $this->assertSame(['borrow.request.approve', 'borrow.return.process'], $created->action_permissions);
    }

    public function test_non_platform_admin_cannot_access_system_admins_page(): void
    {
        $clinic = Clinic::create([
            'name' => 'RSU Medical Clinic',
            'slug' => 'medical',
            'code' => 'RSU-MED',
            'status' => 'active',
        ]);

        $campaignAdmin = Admin::create([
            'clinic_id' => $clinic->id,
            'name' => 'Campaign Only Platform Test',
            'email' => 'campaign-only-platform-test@example.com',
            'google_id' => 'google-campaign-only-platform-test',
            'module_permissions' => ['campaign'],
            'default_workspace' => 'campaign',
        ]);

        $this->actingAs($campaignAdmin, 'admin')
            ->withSession(['clinic_id' => $clinic->id])
            ->get(route('admin.system_admins'))
            ->assertForbidden();
    }

    public function test_action_level_permissions_limit_sensitive_borrow_routes(): void
    {
        $clinic = Clinic::create([
            'name' => 'RSU Medical Clinic',
            'slug' => 'medical',
            'code' => 'RSU-MED',
            'status' => 'active',
        ]);

        $borrowAdmin = Admin::create([
            'clinic_id' => $clinic->id,
            'name' => 'Borrow Operations Admin',
            'email' => 'borrow-ops-admin@example.com',
            'google_id' => 'google-borrow-ops-admin',
            'module_permissions' => ['borrow'],
            'action_permissions' => ['borrow.request.approve', 'borrow.inventory.manage'],
            'default_workspace' => 'borrow',
        ]);

        $this->actingAs($borrowAdmin, 'admin')
            ->withSession(['clinic_id' => $clinic->id])
            ->get(route('admin.borrow_requests'))
            ->assertOk();

        $this->actingAs($borrowAdmin, 'admin')
            ->withSession(['clinic_id' => $clinic->id])
            ->get(route('admin.inventory'))
            ->assertOk();

        $this->actingAs($borrowAdmin, 'admin')
            ->withSession(['clinic_id' => $clinic->id])
            ->get(route('admin.borrow_returns'))
            ->assertForbidden();

        $this->actingAs($borrowAdmin, 'admin')
            ->withSession(['clinic_id' => $clinic->id])
            ->get(route('admin.borrow_fines'))
            ->assertForbidden();
    }

    public function test_borrow_workspace_hides_quick_links_without_matching_actions(): void
    {
        $clinic = Clinic::create([
            'name' => 'RSU Medical Clinic',
            'slug' => 'medical',
            'code' => 'RSU-MED',
            'status' => 'active',
        ]);

        $borrowAdmin = Admin::create([
            'clinic_id' => $clinic->id,
            'name' => 'Borrow Limited Admin',
            'email' => 'borrow-limited-admin@example.com',
            'google_id' => 'google-borrow-limited-admin',
            'module_permissions' => ['borrow'],
            'action_permissions' => ['borrow.request.approve'],
            'default_workspace' => 'borrow',
        ]);

        $this->actingAs($borrowAdmin, 'admin')
            ->withSession(['clinic_id' => $clinic->id])
            ->get(route('admin.workspace.borrow'))
            ->assertOk()
            ->assertSee('Borrow Requests')
            ->assertDontSee(route('admin.walk_in_borrow'), false)
            ->assertDontSee(route('admin.inventory'), false)
            ->assertDontSee(route('admin.borrow_returns'), false);
    }

    public function test_campaign_workspace_hides_quick_links_without_matching_actions(): void
    {
        $clinic = Clinic::create([
            'name' => 'RSU Medical Clinic',
            'slug' => 'medical',
            'code' => 'RSU-MED',
            'status' => 'active',
        ]);

        $campaignAdmin = Admin::create([
            'clinic_id' => $clinic->id,
            'name' => 'Campaign Limited Admin',
            'email' => 'campaign-limited-admin@example.com',
            'google_id' => 'google-campaign-limited-admin',
            'module_permissions' => ['campaign'],
            'action_permissions' => ['campaign.booking.manage'],
            'default_workspace' => 'campaign',
        ]);

        $this->actingAs($campaignAdmin, 'admin')
            ->withSession(['clinic_id' => $clinic->id])
            ->get(route('admin.workspace.campaign'))
            ->assertOk()
            ->assertSee(route('admin.bookings'), false)
            ->assertDontSee(route('admin.campaigns'), false)
            ->assertDontSee(route('admin.time_slots'), false)
            ->assertDontSee(route('admin.reports'), false);
    }

    public function test_livewire_booking_actions_require_booking_manage_permission(): void
    {
        $clinic = Clinic::create([
            'name' => 'RSU Medical Clinic',
            'slug' => 'medical',
            'code' => 'RSU-MED',
            'status' => 'active',
        ]);

        $admin = Admin::create([
            'clinic_id' => $clinic->id,
            'name' => 'Campaign Viewer',
            'email' => 'campaign-viewer@example.com',
            'google_id' => 'google-campaign-viewer',
            'module_permissions' => ['campaign'],
            'action_permissions' => ['campaign.manage'],
        ]);

        $user = User::create([
            'clinic_id' => $clinic->id,
            'name' => 'Test Patient',
            'full_name' => 'Test Patient',
            'student_personnel_id' => '6600111',
            'phone_number' => '0811111111',
            'department' => 'Medical',
            'email' => 'booking-guard-user@example.com',
            'password' => 'password',
        ]);

        $campaign = Campaign::create([
            'clinic_id' => $clinic->id,
            'title' => 'Guarded Campaign',
            'description' => 'Campaign',
            'total_capacity' => 20,
            'status' => 'active',
        ]);

        $slot = Slot::create([
            'camp_id' => $campaign->id,
            'date' => now()->toDateString(),
            'start_time' => '09:00',
            'end_time' => '10:00',
            'max_slots' => 20,
            'status' => 'available',
        ]);

        $booking = Booking::create([
            'clinic_id' => $clinic->id,
            'user_id' => $user->id,
            'camp_id' => $campaign->id,
            'slot_id' => $slot->id,
            'status' => 'pending',
        ]);

        Livewire::actingAs($admin, 'admin');

        Livewire::test(\App\Livewire\Admin\BookingManager::class)
            ->call('approve', $booking->id)
            ->assertForbidden();
    }

    public function test_livewire_borrow_return_actions_require_return_permission(): void
    {
        $clinic = Clinic::create([
            'name' => 'RSU Medical Clinic',
            'slug' => 'medical',
            'code' => 'RSU-MED',
            'status' => 'active',
        ]);

        $admin = Admin::create([
            'clinic_id' => $clinic->id,
            'name' => 'Borrow Queue Admin',
            'email' => 'borrow-queue-admin@example.com',
            'google_id' => 'google-borrow-queue-admin',
            'module_permissions' => ['borrow'],
            'action_permissions' => ['borrow.request.approve'],
        ]);

        $user = User::create([
            'clinic_id' => $clinic->id,
            'name' => 'Late Borrower',
            'full_name' => 'Late Borrower',
            'student_personnel_id' => '6700999',
            'phone_number' => '0891111111',
            'department' => 'Science',
            'email' => 'late-borrower-guard@example.com',
            'password' => 'password',
        ]);

        $category = BorrowCategory::create([
            'clinic_id' => $clinic->id,
            'name' => 'Camera',
            'description' => 'Recording tools',
            'is_active' => true,
        ]);

        $item = BorrowItem::create([
            'clinic_id' => $clinic->id,
            'category_id' => $category->id,
            'name' => 'Sony ZV-E10',
            'serial_number' => 'CAM-GUARD-001',
            'status' => 'borrowed',
        ]);

        $record = BorrowRecord::create([
            'clinic_id' => $clinic->id,
            'category_id' => $category->id,
            'item_id' => $item->id,
            'borrower_user_id' => $user->id,
            'quantity' => 1,
            'reason' => 'Media class',
            'borrowed_at' => now()->subDays(7),
            'due_date' => now()->subDays(3)->toDateString(),
            'approval_status' => 'approved',
            'status' => 'borrowed',
            'fine_status' => 'none',
        ]);

        Livewire::actingAs($admin, 'admin');

        Livewire::test(\App\Livewire\Admin\BorrowReturnManager::class)
            ->call('openReturnModal', $record->id)
            ->assertForbidden();
    }

    public function test_admin_landing_route_uses_default_workspace_when_available(): void
    {
        $clinic = Clinic::create([
            'name' => 'RSU Medical Clinic',
            'slug' => 'medical',
            'code' => 'RSU-MED',
            'status' => 'active',
        ]);

        $borrowAdmin = Admin::create([
            'clinic_id' => $clinic->id,
            'name' => 'Borrow Landing Admin',
            'email' => 'borrow-landing-admin@example.com',
            'google_id' => 'google-borrow-landing-admin',
            'module_permissions' => ['borrow'],
            'default_workspace' => 'borrow',
        ]);

        $this->assertSame('admin.workspace.borrow', $borrowAdmin->landingRouteName());
    }

    public function test_admin_landing_route_falls_back_to_first_allowed_workspace(): void
    {
        $clinic = Clinic::create([
            'name' => 'RSU Medical Clinic',
            'slug' => 'medical',
            'code' => 'RSU-MED',
            'status' => 'active',
        ]);

        $campaignAdmin = Admin::create([
            'clinic_id' => $clinic->id,
            'name' => 'Fallback Campaign Admin',
            'email' => 'fallback-campaign-admin@example.com',
            'google_id' => 'google-fallback-campaign-admin',
            'module_permissions' => ['campaign'],
            'default_workspace' => 'borrow',
        ]);

        $this->assertSame('admin.workspace.campaign', $campaignAdmin->landingRouteName());
    }

    public function test_dev_login_redirects_to_admin_preferred_workspace(): void
    {
        Clinic::create([
            'id' => 1,
            'name' => 'RSU Medical Clinic',
            'slug' => 'medical',
            'code' => 'RSU-MED',
            'status' => 'active',
        ]);

        Admin::create([
            'clinic_id' => 1,
            'name' => 'Developer Admin',
            'email' => 'admin@test.com',
            'google_id' => 'google-dev-admin',
            'module_permissions' => ['borrow'],
            'default_workspace' => 'borrow',
        ]);

        $this->get(route('dev.login'))
            ->assertRedirect(route('admin.workspace.borrow'));
    }

    public function test_admin_booking_cancel_sends_notifications_based_on_rules(): void
    {
        Mail::fake();
        Http::fake([
            'https://api.line.me/v2/bot/message/push' => Http::response(['ok' => true], 200),
        ]);

        $clinic = Clinic::create([
            'name' => 'RSU Medical Clinic',
            'slug' => 'medical',
            'code' => 'RSU-MED',
            'status' => 'active',
        ]);

        $admin = Admin::create([
            'clinic_id' => $clinic->id,
            'name' => 'Clinic Admin',
            'email' => 'cancel-admin@example.com',
            'google_id' => 'google-admin-cancel',
            'module_permissions' => ['campaign'],
        ]);

        $user = User::create([
            'clinic_id' => $clinic->id,
            'name' => 'Cancel Target',
            'full_name' => 'Cancel Target',
            'student_personnel_id' => '6600999',
            'phone_number' => '0899999999',
            'department' => 'Medicine',
            'email' => 'cancel-target@example.com',
            'line_user_id' => 'U-cancel-target',
            'password' => 'password',
        ]);

        $campaign = Campaign::create([
            'clinic_id' => $clinic->id,
            'title' => 'Flu Vaccine 2026',
            'description' => 'Seasonal campaign',
            'total_capacity' => 20,
            'status' => 'active',
        ]);

        $slot = Slot::create([
            'camp_id' => $campaign->id,
            'date' => now()->addDay()->toDateString(),
            'start_time' => '09:00',
            'end_time' => '10:00',
            'max_slots' => 20,
            'status' => 'available',
        ]);

        $booking = Booking::create([
            'clinic_id' => $clinic->id,
            'user_id' => $user->id,
            'camp_id' => $campaign->id,
            'slot_id' => $slot->id,
            'status' => 'pending',
        ]);

        app(IntegrationSettingsService::class)->save(array_merge(
            app(IntegrationSettingsService::class)->defaults(),
            [
                'mail_mailer' => 'smtp',
                'mail_host' => 'smtp.example.com',
                'mail_port' => 587,
                'mail_from_address' => 'clinic@example.com',
                'mail_from_name' => 'RSU Clinic',
                'line_messaging_enabled' => true,
                'line_channel_access_token' => 'line-token',
                'campaign_booking_cancelled_line_enabled' => true,
                'campaign_booking_cancelled_email_enabled' => true,
            ]
        ));

        Livewire::actingAs($admin, 'admin');

        Livewire::test(\App\Livewire\Admin\BookingManager::class)
            ->call('cancel', $booking->id);

        $this->assertDatabaseHas('camp_bookings', [
            'id' => $booking->id,
            'status' => 'cancelled',
        ]);

        $this->assertDatabaseHas('sys_email_logs', [
            'user_id' => $user->id,
            'recipient' => 'cancel-target@example.com',
            'status' => 'sent',
        ]);

        $this->assertDatabaseHas('sys_activity_logs', [
            'clinic_id' => $clinic->id,
            'action' => 'campaign.booking_cancelled_notification_sent',
        ]);

        Mail::assertSent(BookingCancelledMail::class);
        Http::assertSent(fn ($request) => $request->url() === 'https://api.line.me/v2/bot/message/push');
    }

    public function test_admin_booking_approve_sends_notifications_based_on_rules(): void
    {
        Mail::fake();
        Http::fake([
            'https://api.line.me/v2/bot/message/push' => Http::response(['ok' => true], 200),
        ]);

        $clinic = Clinic::create([
            'name' => 'RSU Medical Clinic',
            'slug' => 'medical',
            'code' => 'RSU-MED',
            'status' => 'active',
        ]);

        $admin = Admin::create([
            'clinic_id' => $clinic->id,
            'name' => 'Approve Admin',
            'email' => 'approve-admin@example.com',
            'google_id' => 'google-admin-approve',
            'module_permissions' => ['campaign'],
        ]);

        $user = User::create([
            'clinic_id' => $clinic->id,
            'name' => 'Approve Target',
            'full_name' => 'Approve Target',
            'student_personnel_id' => '6600888',
            'phone_number' => '0888888888',
            'department' => 'Medicine',
            'email' => 'approve-target@example.com',
            'line_user_id' => 'U-approve-target',
            'password' => 'password',
        ]);

        $campaign = Campaign::create([
            'clinic_id' => $clinic->id,
            'title' => 'Health Check 2026',
            'description' => 'Campaign',
            'total_capacity' => 20,
            'status' => 'active',
        ]);

        $slot = Slot::create([
            'camp_id' => $campaign->id,
            'date' => now()->addDay()->toDateString(),
            'start_time' => '10:00',
            'end_time' => '11:00',
            'max_slots' => 20,
            'status' => 'available',
        ]);

        $booking = Booking::create([
            'clinic_id' => $clinic->id,
            'user_id' => $user->id,
            'camp_id' => $campaign->id,
            'slot_id' => $slot->id,
            'status' => 'pending',
        ]);

        app(IntegrationSettingsService::class)->save(array_merge(
            app(IntegrationSettingsService::class)->defaults(),
            [
                'mail_mailer' => 'smtp',
                'mail_host' => 'smtp.example.com',
                'mail_port' => 587,
                'mail_from_address' => 'clinic@example.com',
                'mail_from_name' => 'RSU Clinic',
                'line_messaging_enabled' => true,
                'line_channel_access_token' => 'line-token',
                'campaign_booking_confirmed_line_enabled' => true,
                'campaign_booking_confirmed_email_enabled' => true,
            ]
        ));

        Livewire::actingAs($admin, 'admin');

        Livewire::test(\App\Livewire\Admin\BookingManager::class)
            ->call('approve', $booking->id);

        $this->assertDatabaseHas('camp_bookings', [
            'id' => $booking->id,
            'status' => 'confirmed',
        ]);

        $this->assertDatabaseHas('sys_email_logs', [
            'user_id' => $user->id,
            'recipient' => 'approve-target@example.com',
            'status' => 'sent',
            'subject' => 'ยืนยันการจองนัดหมาย '.$booking->booking_code,
        ]);

        $this->assertDatabaseHas('sys_activity_logs', [
            'clinic_id' => $clinic->id,
            'action' => 'campaign.booking_confirmed_notification_sent',
        ]);

        Mail::assertSent(BookingConfirmedMail::class);
        Http::assertSent(fn ($request) => $request->url() === 'https://api.line.me/v2/bot/message/push');
    }

    public function test_admin_borrow_approval_sends_notifications_based_on_rules(): void
    {
        Mail::fake();
        Http::fake([
            'https://api.line.me/v2/bot/message/push' => Http::response(['ok' => true], 200),
        ]);

        $clinic = Clinic::create([
            'name' => 'RSU Medical Clinic',
            'slug' => 'medical',
            'code' => 'RSU-MED',
            'status' => 'active',
        ]);

        $admin = Admin::create([
            'clinic_id' => $clinic->id,
            'name' => 'Borrow Notify Admin',
            'email' => 'borrow-notify-admin@example.com',
            'google_id' => 'google-borrow-notify-admin',
            'module_permissions' => ['borrow'],
        ]);

        $user = User::create([
            'clinic_id' => $clinic->id,
            'name' => 'Borrow Notify User',
            'full_name' => 'Borrow Notify User',
            'student_personnel_id' => '6700111',
            'phone_number' => '0866666666',
            'department' => 'Science',
            'email' => 'borrow-notify-user@example.com',
            'line_user_id' => 'U-borrow-notify',
            'password' => 'password',
        ]);

        $category = BorrowCategory::create([
            'clinic_id' => $clinic->id,
            'name' => 'Laptop',
            'description' => 'Portable device',
            'is_active' => true,
        ]);

        $item = BorrowItem::create([
            'clinic_id' => $clinic->id,
            'category_id' => $category->id,
            'name' => 'Dell XPS',
            'serial_number' => 'DX-1001',
            'status' => 'available',
        ]);

        $record = BorrowRecord::create([
            'clinic_id' => $clinic->id,
            'category_id' => $category->id,
            'borrower_user_id' => $user->id,
            'quantity' => 1,
            'reason' => 'Need for lab',
            'borrowed_at' => now(),
            'due_date' => now()->addDays(3)->toDateString(),
            'approval_status' => 'pending',
            'status' => 'borrowed',
            'fine_status' => 'none',
        ]);

        app(IntegrationSettingsService::class)->save(array_merge(
            app(IntegrationSettingsService::class)->defaults(),
            [
                'mail_mailer' => 'smtp',
                'mail_host' => 'smtp.example.com',
                'mail_port' => 587,
                'mail_from_address' => 'clinic@example.com',
                'mail_from_name' => 'RSU Clinic',
                'line_messaging_enabled' => true,
                'line_channel_access_token' => 'line-token',
                'borrow_request_approved_line_enabled' => true,
                'borrow_request_approved_email_enabled' => true,
            ]
        ));

        Livewire::actingAs($admin, 'admin');

        Livewire::test(\App\Livewire\Admin\BorrowRequestManager::class)
            ->call('approve', $record->id);

        $this->assertDatabaseHas('borrow_records', [
            'id' => $record->id,
            'approval_status' => 'approved',
            'item_id' => $item->id,
        ]);

        $this->assertDatabaseHas('sys_email_logs', [
            'user_id' => $user->id,
            'recipient' => 'borrow-notify-user@example.com',
            'status' => 'sent',
            'subject' => 'แจ้งอนุมัติคำขอยืมอุปกรณ์',
        ]);

        $this->assertDatabaseHas('sys_activity_logs', [
            'clinic_id' => $clinic->id,
            'action' => 'borrow.request_approved_notification_sent',
        ]);

        Mail::assertSent(BorrowRequestApprovedMail::class);
        Http::assertSent(fn ($request) => $request->url() === 'https://api.line.me/v2/bot/message/push');
    }

    public function test_campaign_reminder_command_sends_notifications_once_per_day(): void
    {
        Mail::fake();
        Http::fake([
            'https://api.line.me/v2/bot/message/push' => Http::response(['ok' => true], 200),
        ]);

        $clinic = Clinic::create([
            'name' => 'RSU Medical Clinic',
            'slug' => 'medical',
            'code' => 'RSU-MED',
            'status' => 'active',
        ]);

        $user = User::create([
            'clinic_id' => $clinic->id,
            'name' => 'Reminder User',
            'full_name' => 'Reminder User',
            'student_personnel_id' => '6600777',
            'phone_number' => '0877777777',
            'department' => 'Nursing',
            'email' => 'reminder-user@example.com',
            'line_user_id' => 'U-reminder-user',
            'password' => 'password',
        ]);

        $campaign = Campaign::create([
            'clinic_id' => $clinic->id,
            'title' => 'Reminder Campaign',
            'description' => 'Campaign',
            'total_capacity' => 20,
            'status' => 'active',
        ]);

        $slot = Slot::create([
            'camp_id' => $campaign->id,
            'date' => now()->addDay()->toDateString(),
            'start_time' => '14:00',
            'end_time' => '15:00',
            'max_slots' => 20,
            'status' => 'available',
        ]);

        $booking = Booking::create([
            'clinic_id' => $clinic->id,
            'user_id' => $user->id,
            'camp_id' => $campaign->id,
            'slot_id' => $slot->id,
            'status' => 'confirmed',
        ]);

        app(IntegrationSettingsService::class)->save(array_merge(
            app(IntegrationSettingsService::class)->defaults(),
            [
                'mail_mailer' => 'smtp',
                'mail_host' => 'smtp.example.com',
                'mail_port' => 587,
                'mail_from_address' => 'clinic@example.com',
                'mail_from_name' => 'RSU Clinic',
                'line_messaging_enabled' => true,
                'line_channel_access_token' => 'line-token',
                'campaign_booking_reminder_line_enabled' => true,
                'campaign_booking_reminder_email_enabled' => true,
            ]
        ));

        Artisan::call('campaign:send-reminders', [
            '--date' => now()->addDay()->toDateString(),
        ]);

        Artisan::call('campaign:send-reminders', [
            '--date' => now()->addDay()->toDateString(),
        ]);

        $this->assertDatabaseHas('sys_email_logs', [
            'user_id' => $user->id,
            'recipient' => 'reminder-user@example.com',
            'status' => 'sent',
            'subject' => 'เตือนการนัดหมาย '.$booking->booking_code,
        ]);

        $this->assertDatabaseCount('sys_email_logs', 1);
        $this->assertDatabaseHas('sys_activity_logs', [
            'clinic_id' => $clinic->id,
            'action' => 'campaign.booking_reminder_notification_sent',
        ]);

        Mail::assertSent(\App\Mail\BookingReminderMail::class, 1);
        Http::assertSentCount(1);
    }
}

