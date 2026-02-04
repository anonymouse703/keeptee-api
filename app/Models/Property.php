<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Property extends Model
{
    protected $fillable = [
        'title',
        'description',
        'status',
        'property_type',
        'price',
        'bedrooms',
        'bathrooms',
        'floor_area',
        'address',
        'city',
        'state',
        'country',
        'latitude',
        'longitude',
        'owner_id',
        'is_featured',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function images(): BelongsToMany
    {
        return $this->belongsToMany(File::class, 'property_images', 'property_id', 'file_id')
            ->using(PropertyImage::class)
            ->withPivot('is_primary', 'sort_order', 'image_type')
            ->orderByPivot('sort_order', 'asc');
    }

    public function primaryImage(): BelongsToMany
    {
        return $this->belongsToMany(File::class, 'property_images', 'property_id', 'file_id')
            ->using(PropertyImage::class)
            ->withPivot('is_primary', 'sort_order', 'image_type')
            ->wherePivot('is_primary', true)
            ->limit(1);
    }

    public function amenities(): BelongsToMany
    {
        return $this->belongsToMany(Amenity::class, 'property_amenities', 'property_id', 'amenity_id')
            ->using(PropertyAmenity::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'property_tags', 'property_id', 'tag_id')
            ->using(PropertyTag::class);
    }

    public function inquiries(): HasMany
    {
        return $this->hasMany(PropertyInquiry::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForStatus($query, $status)
    {
        return $query->when($status, fn($q) => $q->where('status', $status));
    }

    public function scopeCity($query, $city)
    {
        return $query->when($city, fn($q) => $q->where('city', $city));
    }

    public function scopeBedrooms($query, $beds)
    {
        return $query->when($beds, fn($q) => $q->where('bedrooms', '>=', $beds));
    }

    public function scopePriceRange($query, $min, $max)
    {
        return $query
            ->when($min, fn($q) => $q->where('price', '>=', $min))
            ->when($max, fn($q) => $q->where('price', '<=', $max));
    }

    public function scopeKeyword($query, $keyword)
    {
        return $query->when($keyword, function($q) use ($keyword) {
            $q->where(function ($sub) use ($keyword) {
                $sub->where('title', 'like', "%{$keyword}%")
                    ->orWhere('city', 'like', "%{$keyword}%")
                    ->orWhere('address', 'like', "%{$keyword}%");
            });
        });
    }

    public function getPrimaryImageUrlAttribute(): ?string
    {
        $primaryImage = $this->primaryImage()->first();
        return $primaryImage ? asset('storage/' . $primaryImage->path) : null;
    }

    public function getFormattedPriceAttribute(): string
    {
        return '$' . number_format($this->price, 2);
    }
}