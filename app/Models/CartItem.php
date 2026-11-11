<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'cart_id',
        'menu_id',
        'quantity',
        'unit_price_seen',
        'note',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price_seen' => 'integer',
    ];

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->menu?->name ?? ''
        );
    }

    protected function unitPrice(): Attribute
    {
        return Attribute::make(
            get: fn () => (int) $this->unit_price_seen
        );
    }

    protected function lineTotal(): Attribute
    {
        return Attribute::make(
            get: fn () => (int) ($this->unit_price_seen * $this->quantity)
        );
    }
}
