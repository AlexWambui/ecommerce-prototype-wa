<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Concerns\HasUuid;

class LoyaltyMember extends Model
{
    use HasUuid;

    protected $guarded = [];

    protected static function booted()
    {
        static::creating(function ($model) {
            // Only generate if it hasn't been manually set already
            if (empty($model->loyalty_id)) {
                $model->loyalty_id = self::generateUniqueLoyaltyId();
            }
        });
    }

    public static function generateUniqueLoyaltyId(): string
    {
        do {
            // Generate a number between 100000 and 999999
            $code = (string) random_int(100000, 999999);
        } 
        // Keep looping if this code already exists in the database
        while (self::where('loyalty_id', $code)->exists());

        return $code;
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }
}