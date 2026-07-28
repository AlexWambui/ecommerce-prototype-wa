<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Concerns\HasUuid;
use App\Concerns\HasSlug;

class DeliveryLocation extends Model
{
    use HasUuid, HasSlug;

    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    public function deliveryAreas(): HasMany
    {
        return $this->hasMany(DeliveryArea::class, 'delivery_location_id');
    }
}
