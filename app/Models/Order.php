<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Concerns\HasUuid;

class Order extends Model
{
    use HasUuid;

    protected $guarded = [];

    protected $casts = [
        'sold_at' => 'datetime',
        'customer_details_snapshot' => 'array',
        'delivery_details_snapshot' => 'array',
        'pricing_snapshot' => 'array',
        'payment_snapshot' => 'array',
    ];

    // Order fulfillment statuses
    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_SHIPPED = 'shipped';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_CANCELLED = 'cancelled';

    // Payment statuses
    const PAYMENT_PENDING = 'pending';
    const PAYMENT_PARTIALLY_PAID = 'partially_paid';
    const PAYMENT_PAID = 'paid';
    const PAYMENT_DELIVERED = 'refunded';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }

    public function orderStatuses(): HasMany
    {
        return $this->hasMany(OrderStatus::class, 'order_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function getPaymentStatusAttribute(): string
    {
        $total_paid = $this->payments()->sum('amount');

        if ($total_paid <= 0) {
            return self::PAYMENT_PENDING;
        }

        if ($total_paid >= $this->total_amount) {
            return self::PAYMENT_PAID;
        }

        return self::PAYMENT_PARTIALLY_PAID;
    }

    public function getTotalPaidAttribute(): float
    {
        return (float) $this->payments()->sum('amount');
    }

    public function getBalanceAttribute(): float
    {
        return max(0, $this->total_amount - $this->total_paid);
    }

    public function isFullyPaid(): bool
    {
        return $this->total_paid >= $this->total_amount;
    }

    public function updateAmountPaid(): void
    {
        $this->amount_paid = $this->total_paid;
        $this->save();
    }

    public function getDeliveryStatusAttribute(): string
    {
        // If delivery method is 'shop', it's always 'pickup'
        if ($this->delivery_method === 'shop') {
            return 'pickup';
        }

        // For delivery orders, check the order status
        return $this->order_status ?? self::STATUS_PENDING;
    }

    public function loyaltyMember()
    {
        return $this->belongsTo(LoyaltyMember::class);
    }
}
