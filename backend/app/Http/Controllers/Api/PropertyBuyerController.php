<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Property;
use App\Models\PropertyBuyer;
use Carbon\Carbon;

class PropertyBuyerController extends Controller
{
    //
    public function storeOrUpdate(Request $request, Property $property)
    {
        $data = $request->validate([
            'buyer_number' => ['nullable', 'string', 'max:100'],
            'buyer_name' => ['required', 'string', 'max:255'],
            'buyer_phone' => ['required', 'string', 'max:30'],
            'buyer_email' => ['required', 'email', 'max:255'],

            'sale_price' => ['required', 'numeric', 'min:0'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'down_payment_amount' => ['nullable', 'numeric', 'min:0'],

            'monthly_payment_amount' => ['required', 'numeric', 'min:1'],
            'payment_due_day' => ['required', 'integer', 'min:1', 'max:31'],

            'sale_date' => ['nullable', 'date'],
            'first_payment_period' => ['required', 'date'],

            'notes' => ['nullable', 'string'],
        ]);

        $data['discount_amount'] = $data['discount_amount'] ?? 0;
        $data['down_payment_amount'] = $data['down_payment_amount'] ?? 0;
        $data['first_payment_period'] = Carbon::parse($data['first_payment_period'])
            ->startOfMonth()
            ->toDateString();

        $buyer = PropertyBuyer::updateOrCreate(
            ['property_id' => $property->id],
            $data
        );

        return response()->json([
            'message' => 'Comprador guardado correctamente.',
            'buyer' => $buyer,
        ]);
    }
}
