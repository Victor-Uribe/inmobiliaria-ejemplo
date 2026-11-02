<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    //
    protected $fillable = [
        'property_id',
        'name',
        'phone',
        'email',
        'message',
        'source',
        'status',
    ];

    public function property()
    {
        return $this->belongsTo(Property::class);
    }
}
