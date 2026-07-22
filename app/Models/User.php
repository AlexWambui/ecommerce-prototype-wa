<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Laravel\Fortify\Contracts\PasskeyUser;
use Laravel\Fortify\PasskeyAuthenticatable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Illuminate\Support\Facades\Storage;
use App\Concerns\HasUuid;
use App\Enums\UserRoles;
use App\Enums\UserStatuses;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property Carbon|null $two_factor_confirmed_at
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
// #[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable implements PasskeyUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, PasskeyAuthenticatable, TwoFactorAuthenticatable;
    use HasUuid;

    protected $guarded = [];

    protected $appends = [
        'role_label',
        'status_label',
        'image_url',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
            'role' => UserRoles::class,
            'status' => UserStatuses::class
        ];
    }

    public function hasRole(string $role_name): bool
    {
        // Convert string role name to enum value
        foreach (UserRoles::cases() as $role) {
            if (strtolower($role->name) === strtolower($role_name)) {
                return $this->role->value === $role->value;
            }
        }
        return false;
    }

    public function hasAnyRole(array $role_names): bool
    {
        foreach ($role_names as $role_name) {
            if ($this->hasRole($role_name)) {
                return true;
            }
        }

        return false;
    }

    public function isActive(): bool
    {
        return $this->status === UserStatuses::ACTIVE;
    }

    public function getIsActiveAttribute(): bool
    {
        return $this->isActive();
    }

    public function getRoleLabelAttribute(): string
    {
        return $this->role->label();
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->status->label();
    }

    public function getImageUrlAttribute(): ?string
    {
        if ($this->image && Storage::disk('public')->exists('users/' . $this->image)) {
            return asset('storage/users/' . $this->image);
        }
        
        return asset('assets/images/default-image.png');
    }
}
