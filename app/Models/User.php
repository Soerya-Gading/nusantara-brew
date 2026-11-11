<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const ROLE_CUSTOMER = 'customer';
    public const ROLE_OWNER = 'owner';
    public const ROLE_STAFF = 'staff';
    public const ROLE_ADMIN = 'admin';

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'role',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function isCustomer(): bool
    {
        return $this->role === self::ROLE_CUSTOMER;
    }

    public function isOwner(): bool
    {
        return $this->role === self::ROLE_OWNER;
    }

    public function isStaff(): bool
    {
        return $this->role === self::ROLE_STAFF;
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    protected function roleLabel(): Attribute
    {
        return Attribute::make(
            get: fn () => match ($this->role) {
                self::ROLE_CUSTOMER => 'Pelanggan',
                self::ROLE_OWNER => 'Pemilik',
                self::ROLE_STAFF => 'Staf',
                self::ROLE_ADMIN => 'Admin',
                default => ucfirst($this->role),
            }
        );
    }

    public function coffeeShops(): HasMany
    {
        return $this->hasMany(CoffeeShop::class, 'owner_user_id');
    }

    public function assignedShop(): BelongsToMany
    {
        return $this->belongsToMany(CoffeeShop::class, 'coffee_shop_staff');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function cart(): HasOne
    {
        return $this->hasOne(Cart::class);
    }

    public function currentShop(): ?CoffeeShop
    {
        return $this->getActiveShop();
    }

    public function getActiveShop(): ?CoffeeShop
    {
        if ($this->isOwner()) {
            return $this->coffeeShops()->first();
        }

        if ($this->isStaff()) {
            return $this->assignedShop()->first();
        }

        return null;
    }
}
