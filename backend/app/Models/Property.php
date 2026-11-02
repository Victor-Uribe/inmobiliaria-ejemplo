<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Property extends Model
{
    protected $fillable = [
        'user_id', 'title', 'slug', 'price', 'state', 'municipality',
        'contact_name', 'contact_phone', 'description', 'main_image',
        'latitude', 'longitude', 'is_featured', 'status',
    ];

    protected $appends = ['main_image_url'];

    public function advisor()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function images()
    {
        return $this->hasMany(PropertyImage::class);
    }

    public function leads()
    {
        return $this->hasMany(Lead::class);
    }

    public function getMainImageUrlAttribute()
    {
        return $this->main_image
            ? asset('storage/' . $this->main_image)
            : null;
    }

    public function buyer()
    {
        return $this->hasOne(PropertyBuyer::class);
    }

    public function payments()
    {
        return $this->hasManyThrough(
            PropertyPayment::class,
            PropertyBuyer::class,
            'property_id',
            'property_buyer_id',
            'id',
            'id'
        );
    }
}
