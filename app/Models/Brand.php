<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Concerns\HasUuid;
use App\Concerns\HasSlug;

class Brand extends Model
{
    use HasUuid, HasSlug;

    protected $guarded = [];

    protected $appends = [
        'thumbnail_url',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function booted()
    {
        static::updating(function ($brand) {
            if ($brand->isDirty('name')) {
                $old_name = $brand->getOriginal('name');
                $new_name = $brand->name;
                
                // Update slug
                $brand->slug = Str::slug($new_name);
                
                // Rename image
                $old_image = $brand->getOriginal('image');
                $new_slug = Str::slug($new_name);
                
                if ($old_image) {
                    $old_slug = Str::slug($old_name);
                    $new_filename = str_replace($old_slug, $new_slug, $old_image);
                    
                    // Only rename if the filename actually changed
                    if ($old_image !== $new_filename) {
                        $old_path = 'brands/' . $old_image;
                        $new_path = 'brands/' . $new_filename;
                        
                        // Rename the actual file
                        if (Storage::disk('public')->exists($old_path)) {
                            Storage::disk('public')->move($old_path, $new_path);
                            
                            // ✅ Update the image filename in the database
                            $brand->image = $new_filename;
                        }
                    }
                }
            }
        });

        static::deleting(function ($brand) {
            if ($brand->image) {
                $path = "brands/{$brand->image}";
                if (Storage::disk('public')->exists($path)) {
                    Storage::disk('public')->delete($path);
                }
            }
        });
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'brand_id');
    }

    public function getThumbnailUrlAttribute(): string
    {
        if (!$this->image) {
            return asset('/assets/images/default-image.png');
        }

        return asset("storage/brands/{$this->image}");
    }
}
