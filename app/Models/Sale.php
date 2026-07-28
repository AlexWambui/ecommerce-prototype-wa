<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Concerns\HasUuid;

class Sale extends Model
{
    use HasUuid;

    protected $guarded = [];

    protected $casts = [
        'sold_at' => 'datetime',
    ];

    public function loyaltyMember(): BelongsTo
    {
        return $this->belongsTo(LoyaltyMember::class, 'loyalty_member_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
