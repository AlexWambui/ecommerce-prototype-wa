<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
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
        if ($this->amount_paid <= 0) {
            return 'pending';
        }

        if ($this->amount_paid >= $this->total_amount) {
            return 'paid';
        }

        return 'partially_paid';
    }

    public function loyaltyMember()
    {
        return $this->belongsTo(LoyaltyMember::class);
    }

    // Remaining balance on an order
    public function getBalanceAttribute(): float
    {
        return max(0, $this->total_amount - $this->amount_paid);
    }
}
