<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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

    /* =======================
       Relationships
    ======================== */

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function images()
    {
        return $this->hasMany(PropertyImage::class);
    }

    public function primaryImage()
    {
        return $this->hasOne(PropertyImage::class)->where('is_primary', true);
    }

    public function amenities()
    {
        return $this->belongsToMany(Amenity::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }

    public function inquiries()
    {
        return $this->hasMany(PropertyInquiry::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    /* =======================
       Query Scopes (SEARCH)
    ======================== */

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForStatus($query, $status)
    {
        return $query->when($status, fn ($q) =>
            $q->where('status', $status)
        );
    }

    public function scopeCity($query, $city)
    {
        return $query->when($city, fn ($q) =>
            $q->where('city', $city)
        );
    }

    public function scopeBedrooms($query, $beds)
    {
        return $query->when($beds, fn ($q) =>
            $q->where('bedrooms', '>=', $beds)
        );
    }

    public function scopePriceRange($query, $min, $max)
    {
        return $query
            ->when($min, fn ($q) => $q->where('price', '>=', $min))
            ->when($max, fn ($q) => $q->where('price', '<=', $max));
    }

    public function scopeKeyword($query, $keyword)
    {
        return $query->when($keyword, fn ($q) =>
            $q->where(function ($sub) use ($keyword) {
                $sub->where('title', 'like', "%{$keyword}%")
                    ->orWhere('city', 'like', "%{$keyword}%")
                    ->orWhere('address', 'like', "%{$keyword}%");
            })
        );
    }
}
