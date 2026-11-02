<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PropertyPayment extends Model
{
    //
    protected $fillable = [
        'property_buyer_id',
        'paid_at',
        'applied_period',
        'applied_due_date',
        'amount',
        'payment_type',
        'status',
        'payment_method',
        'reference',
        'folio',
        'notes',
        'created_by',
        'cancelled_at',
        'cancelled_by',
    ];

    protected $casts = [
        'paid_at' => 'date',
        'applied_period' => 'date',
        'applied_due_date' => 'date',
        'amount' => 'decimal:2',
        'cancelled_at' => 'datetime',
    ];

    public function buyer()
    {
        return $this->belongsTo(PropertyBuyer::class, 'property_buyer_id');
    }
}
