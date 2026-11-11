<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CoffeeShop extends Model
{
    use HasFactory;

    protected $fillable = [
        'owner_user_id',
        'name',
        'slug',
        'description',
        'address',
        'phone',
        'opening_hours_text',
        'logo_path',
        'status',
        'is_accepting_orders',
    ];

    protected function casts(): array
    {
        return [
            'is_accepting_orders' => 'boolean',
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function staff(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'coffee_shop_staff');
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class)->orderBy('sort_order');
    }

    public function menus(): HasMany
    {
        return $this->hasMany(Menu::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    protected function statusLabel(): Attribute
    {
        return Attribute::make(
            get: fn () => match ($this->status) {
                'active' => 'Aktif',
                'pending' => 'Menunggu persetujuan',
                'inactive' => 'Nonaktif',
                default => ucfirst($this->status),
            }
        );
    }

    protected function ownerName(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->owner?->name ?? '-'
        );
    }

    protected function submittedAt(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->created_at?->timezone('Asia/Jakarta')->translatedFormat('d M Y') ?? ''
        );
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
