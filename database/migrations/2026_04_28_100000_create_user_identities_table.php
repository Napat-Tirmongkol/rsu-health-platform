<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_identities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('identity_type', 50);
            $table->string('identity_value', 100);
            $table->boolean('is_primary')->default(false);
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'identity_type', 'identity_value'], 'user_identity_unique');
        });

        $now = now();

        DB::table('users')
            ->select(['id', 'status', 'student_personnel_id', 'citizen_id', 'created_at', 'updated_at'])
            ->orderBy('id')
            ->chunkById(100, function ($users) use ($now) {
                $rows = [];

                foreach ($users as $user) {
                    $studentPersonnelId = trim((string) ($user->student_personnel_id ?? ''));
                    $citizenId = trim((string) ($user->citizen_id ?? ''));
                    $status = trim((string) ($user->status ?? ''));

                    if ($studentPersonnelId !== '') {
                        $rows[] = [
                            'user_id' => $user->id,
                            'identity_type' => $status === 'staff' ? 'staff_id' : 'student_id',
                            'identity_value' => $studentPersonnelId,
                            'is_primary' => in_array($status, ['student', 'staff'], true),
                            'verified_at' => null,
                            'created_at' => $user->created_at ?? $now,
                            'updated_at' => $user->updated_at ?? $now,
                        ];
                    }

                    if ($citizenId !== '') {
                        $isCitizenId = ctype_digit($citizenId) && strlen($citizenId) === 13;

                        $rows[] = [
                            'user_id' => $user->id,
                            'identity_type' => $isCitizenId ? 'citizen_id' : 'passport',
                            'identity_value' => $citizenId,
                            'is_primary' => ! in_array($status, ['student', 'staff'], true),
                            'verified_at' => null,
                            'created_at' => $user->created_at ?? $now,
                            'updated_at' => $user->updated_at ?? $now,
                        ];
                    }
                }

                if ($rows !== []) {
                    DB::table('user_identities')->insert($rows);
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_identities');
    }
};
