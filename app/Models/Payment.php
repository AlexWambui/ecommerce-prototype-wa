<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Concerns\HasUuid;

class Payment extends Model
{
    use HasUuid;

    protected $guarded = [];

    protected static function booted()
    {
        // When a payment is created or updated
        static::saved(function ($payment) {
            $payment->order->updateAmountPaid();
        });

        // When a payment is deleted
        static::deleted(function ($payment) {
            $payment->order->updateAmountPaid();
        });
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
}
