<?php

namespace App\Models;

use App\Casts\RoleCast;
use App\Enums\Role;
use App\Enums\SubscriptionStatus;
use App\Enums\UserStatus;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable // implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'status',
        'last_login_at',
        'phone',
        'address',
        'settings',
        'avatar_path',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
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
            'role' => RoleCast::class,
            'status' => UserStatus::class,
            'last_login_at' => 'datetime',
            'settings' => 'array',
        ];
    }

    /**
     * Preferensi notifikasi pengguna beserta nilai bawaannya.
     *
     * @return array<string, bool>
     */
    public function notificationPreferences(): array
    {
        $defaults = [
            'daily_report' => true,
            'new_order_popup' => true,
            'new_order_email' => true,
            'system_update' => false,
            'new_device_login' => true,
            'password_change' => true,
            'user_message' => true,
            'low_stock' => false,
        ];

        return array_merge($defaults, (array) ($this->settings['notifications'] ?? []));
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->map(fn (string $name) => Str::of($name)->substr(0, 1))
            ->implode('');
    }

    /**
     * Apakah pengguna ini administrator platform.
     */
    public function isAdmin(): bool
    {
        return $this->role === Role::Admin;
    }

    /**
     * Apakah pengguna ini penyedia layanan.
     */
    public function isProvider(): bool
    {
        return $this->role === Role::Provider;
    }

    /**
     * Seluruh transaksi yang pernah dibuat pengguna ini.
     *
     * @return HasMany<Transaction, $this>
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Seluruh langganan milik pengguna ini.
     *
     * @return HasMany<Subscription, $this>
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * Ulasan yang ditulis pengguna ini.
     *
     * @return HasMany<Review, $this>
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Kredensial akun layanan yang dibagikan kepada pengguna ini.
     *
     * @return HasMany<ServiceCredential, $this>
     */
    public function serviceCredentials(): HasMany
    {
        return $this->hasMany(ServiceCredential::class);
    }

    /**
     * Apakah akun pengguna ini masih boleh masuk.
     */
    public function canSignIn(): bool
    {
        return ($this->status ?? UserStatus::Aktif)->canSignIn();
    }

    /**
     * Status akun dalam bentuk objek enum, dengan nilai bawaan "aktif".
     */
    public function statusEnum(): UserStatus
    {
        return $this->status instanceof UserStatus ? $this->status : UserStatus::Aktif;
    }

    /**
     * Hanya pengguna dengan peran tertentu.
     *
     * @param  Builder<User>  $query
     */
    public function scopeRole(Builder $query, Role $role): void
    {
        $query->where('role', $role->value);
    }

    /**
     * Langganan yang masih aktif pada saat ini.
     *
     * @return HasMany<Subscription, $this>
     */
    public function activeSubscriptions(): HasMany
    {
        return $this->subscriptions()
            ->where('status', SubscriptionStatus::Active->value)
            ->where('ends_at', '>', now());
    }
}
