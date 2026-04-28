<?php

namespace Tests\Feature;

use App\Models\BorrowCategory;
use App\Models\BorrowItem;
use App\Models\BorrowRecord;
use App\Models\Clinic;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class BorrowFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function createUserAndClinic(): array
    {
        $clinic = Clinic::create([
            'name' => 'RSU Medical Clinic',
            'slug' => 'medical',
            'code' => 'RSU-MED',
            'status' => 'active',
        ]);

        $user = User::create([
            'clinic_id' => $clinic->id,
            'name' => 'Borrow User',
            'email' => 'borrow-user@example.com',
            'line_user_id' => 'line-borrow-user',
            'password' => Hash::make('password'),
        ]);

        return [$clinic, $user];
    }

    public function test_borrow_catalog_and_history_pages_render(): void
    {
        [$clinic, $user] = $this->createUserAndClinic();

        $category = BorrowCategory::create([
            'clinic_id' => $clinic->id,
            'name' => 'Wheelchair',
            'description' => 'Borrow support equipment',
            'total_quantity' => 2,
            'available_quantity' => 1,
            'is_active' => true,
        ]);

        BorrowItem::create([
            'clinic_id' => $clinic->id,
            'category_id' => $category->id,
            'name' => 'Wheelchair A',
            'serial_number' => 'WC-001',
            'status' => 'available',
        ]);

        foreach (['user.borrow.index', 'user.borrow.history'] as $route) {
            $this->actingAs($user, 'user')
                ->withSession(['clinic_id' => $clinic->id])
                ->get(route($route))
                ->assertOk();
        }

        $this->actingAs($user, 'user')
            ->withSession(['clinic_id' => $clinic->id])
            ->get(route('user.borrow.create', $category))
            ->assertOk()
            ->assertSee('Wheelchair');
    }

    public function test_user_can_create_borrow_request(): void
    {
        [$clinic, $user] = $this->createUserAndClinic();

        $category = BorrowCategory::create([
            'clinic_id' => $clinic->id,
            'name' => 'Wheelchair',
            'description' => 'Borrow support equipment',
            'total_quantity' => 2,
            'available_quantity' => 1,
            'is_active' => true,
        ]);

        BorrowItem::create([
            'clinic_id' => $clinic->id,
            'category_id' => $category->id,
            'name' => 'Wheelchair A',
            'serial_number' => 'WC-001',
            'status' => 'available',
        ]);

        $response = $this->actingAs($user, 'user')
            ->withSession(['clinic_id' => $clinic->id])
            ->post(route('user.borrow.store', $category), [
                'reason' => 'Need mobility support during clinic visit',
                'quantity' => 1,
                'due_date' => now()->addDay()->toDateString(),
            ]);

        $response->assertRedirect(route('user.borrow.history'));

        $this->assertDatabaseHas('borrow_records', [
            'clinic_id' => $clinic->id,
            'category_id' => $category->id,
            'borrower_user_id' => $user->id,
            'approval_status' => 'pending',
        ]);
    }

    public function test_borrow_history_shows_created_request(): void
    {
        [$clinic, $user] = $this->createUserAndClinic();

        $category = BorrowCategory::create([
            'clinic_id' => $clinic->id,
            'name' => 'Wheelchair',
            'description' => 'Borrow support equipment',
            'total_quantity' => 2,
            'available_quantity' => 1,
            'is_active' => true,
        ]);

        BorrowRecord::create([
            'clinic_id' => $clinic->id,
            'category_id' => $category->id,
            'borrower_user_id' => $user->id,
            'quantity' => 1,
            'reason' => 'Need mobility support during clinic visit',
            'borrowed_at' => now(),
            'due_date' => now()->addDay()->toDateString(),
            'approval_status' => 'pending',
            'status' => 'borrowed',
            'fine_status' => 'none',
        ]);

        $this->actingAs($user, 'user')
            ->withSession(['clinic_id' => $clinic->id])
            ->get(route('user.borrow.history'))
            ->assertOk()
            ->assertSee('Wheelchair')
            ->assertSee('Need mobility support during clinic visit')
            ->assertSee('รอตรวจสอบ');
    }
}
