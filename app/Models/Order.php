<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    public const STATUS_PENDING_PAYMENT = 'pending_payment';
    public const STATUS_QUEUED = 'queued';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_READY = 'ready';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    public const PAYMENT_PENDING = 'pending';
    public const PAYMENT_PAID = 'paid';
    public const PAYMENT_VOIDED = 'voided';

    public const METHOD_QRIS = 'qris_simulation';
    public const METHOD_CASH = 'cash';
    public const PAYMENT_METHOD_QRIS = 'qris_simulation';
    public const PAYMENT_METHOD_CASH = 'cash';

    public const FULFILLMENT_PICKUP = 'pickup';
    public const FULFILLMENT_DINE_IN = 'dine_in';

    protected $fillable = [
        'order_code',
        'user_id',
        'coffee_shop_id',
        'cart_id',
        'cart_version',
        'shop_name_snapshot',
        'shop_address_snapshot',
        'customer_name_snapshot',
        'customer_phone_snapshot',
        'fulfillment_type',
        'table_label',
        'status',
        'payment_method',
        'payment_status',
        'total_amount',
        'customer_note',
        'expires_at',
        'paid_at',
        'paid_by_user_id',
        'completed_at',
        'cancelled_at',
        'cancellation_reason',
        'stock_released_at',
    ];

    protected function casts(): array
    {
        return [
            'total_amount' => 'integer',
            'expires_at' => 'datetime',
            'paid_at' => 'datetime',
            'completed_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'stock_released_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function coffeeShop(): BelongsTo
    {
        return $this->belongsTo(CoffeeShop::class);
    }

    public function paidByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'paid_by_user_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class)->orderBy('created_at');
    }

    protected function statusLabel(): Attribute
    {
        return Attribute::make(
            get: fn () => match ($this->status) {
                self::STATUS_PENDING_PAYMENT => 'Menunggu pembayaran',
                self::STATUS_QUEUED => 'Dalam antrean',
                self::STATUS_PROCESSING => 'Sedang dibuat',
                self::STATUS_READY => 'Siap diambil',
                self::STATUS_COMPLETED => 'Selesai',
                self::STATUS_CANCELLED => 'Dibatalkan',
                default => ucfirst($this->status),
            }
        );
    }

    protected function badgeClass(): Attribute
    {
        return Attribute::make(
            get: fn () => match ($this->status) {
                self::STATUS_PENDING_PAYMENT => 'badge-warning',
                self::STATUS_QUEUED, self::STATUS_PROCESSING, self::STATUS_READY => 'badge-info',
                self::STATUS_COMPLETED => 'badge-success',
                self::STATUS_CANCELLED => 'badge-danger',
                default => 'badge-neutral',
            }
        );
    }

    public function isExpired(): bool
    {
        return $this->status === self::STATUS_PENDING_PAYMENT
            && $this->expires_at
            && now()->greaterThanOrEqualTo($this->expires_at);
    }
}
