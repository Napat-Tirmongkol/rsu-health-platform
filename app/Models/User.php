<?php

namespace App\Models;

use App\Models\Traits\BelongsToClinic;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Schema;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Jetstream\HasTeams;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use BelongsToClinic;
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use HasTeams;
    use Notifiable;
    use TwoFactorAuthenticatable;

    private static ?bool $identityTableExists = null;

    protected $fillable = [
        'clinic_id',
        'prefix',
        'first_name',
        'last_name',
        'full_name',
        'gender',
        'status',
        'citizen_id',
        'student_personnel_id',
        'department',
        'phone_number',
        'name',
        'email',
        'phone',
        'username',
        'line_user_id',
        'line_avatar_url',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    protected $appends = [
        'profile_photo_url',
        'identity_label',
        'identity_value',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function announcementReads()
    {
        return $this->hasMany(UserAnnouncementRead::class);
    }

    public function identities()
    {
        return $this->hasMany(UserIdentity::class);
    }

    public function primaryIdentity()
    {
        return $this->hasOne(UserIdentity::class)->where('is_primary', true);
    }

    public function resolveIdentity(): array
    {
        $identity = null;

        if (self::hasIdentityTable()) {
            try {
                $identity = $this->relationLoaded('primaryIdentity')
                    ? $this->primaryIdentity
                    : $this->primaryIdentity()->first();
            } catch (QueryException) {
                self::$identityTableExists = false;
            }
        }

        if (! $identity) {
            $identity = $this->fallbackIdentity();
        }

        if (! $identity) {
            return [
                'type' => 'unknown',
                'label' => 'รหัสระบุตัวตน',
                'value' => (string) $this->id,
            ];
        }

        return [
            'type' => $identity->identity_type,
            'label' => $this->identityLabel($identity->identity_type),
            'value' => $identity->identity_value,
        ];
    }

    public static function hasIdentityTable(): bool
    {
        return self::$identityTableExists ??= Schema::hasTable('user_identities');
    }

    public function getIdentityLabelAttribute(): string
    {
        return $this->resolveIdentity()['label'];
    }

    public function getIdentityValueAttribute(): string
    {
        return $this->resolveIdentity()['value'];
    }

    private function fallbackIdentity(): ?object
    {
        $studentPersonnelId = trim((string) ($this->student_personnel_id ?? ''));
        $citizenId = trim((string) ($this->citizen_id ?? ''));

        if ($studentPersonnelId !== '') {
            return (object) [
                'identity_type' => $this->status === 'staff' ? 'staff_id' : 'student_id',
                'identity_value' => $studentPersonnelId,
            ];
        }

        if ($citizenId !== '') {
            return (object) [
                'identity_type' => ctype_digit($citizenId) && strlen($citizenId) === 13 ? 'citizen_id' : 'passport',
                'identity_value' => $citizenId,
            ];
        }

        return null;
    }

    private function identityLabel(string $type): string
    {
        return match ($type) {
            'student_id' => 'รหัสนักศึกษา',
            'staff_id' => 'รหัสบุคลากร',
            'citizen_id' => 'เลขบัตรประชาชน',
            'passport' => 'Passport',
            default => 'รหัสระบุตัวตน',
        };
    }
}
