<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PropertyBuyer extends Model
{
    //
    protected $fillable = [
        'property_id',
        'buyer_number',
        'buyer_name',
        'buyer_phone',
        'buyer_email',
        'sale_price',
        'discount_amount',
        'down_payment_amount',
        'monthly_payment_amount',
        'payment_due_day',
        'sale_date',
        'first_payment_period',
        'status',
        'notes',
    ];

    protected $casts = [
        'sale_price' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'down_payment_amount' => 'decimal:2',
        'monthly_payment_amount' => 'decimal:2',
        'sale_date' => 'date',
        'first_payment_period' => 'date',
    ];

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function payments()
    {
        return $this->hasMany(PropertyPayment::class);
    }

    public function getFinancedAmountAttribute()
    {
        return max(
            0,
            (float) $this->sale_price
            - (float) $this->discount_amount
            - (float) $this->down_payment_amount
        );
    }
}
