<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Concerns\HasUuid;
use App\Concerns\HasSlug;

class ProductCategory extends Model
{
    use HasUuid, HasSlug;

    protected $guarded = [];

    protected $appends = [
        'thumbnail_url',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'product_category_id');
    }

    public function getThumbnailUrlAttribute(): string
    {
        if (!$this->image) {
            return asset('/assets/images/default-image.png');
        }

        return asset("storage/product-categories/{$this->name}");
    }
}
