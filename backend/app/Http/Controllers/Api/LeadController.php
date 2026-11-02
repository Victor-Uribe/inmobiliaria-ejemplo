<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Lead;


class LeadController extends Controller
{
    //
    public function store(Request $request)
    {
        $data = $request->validate([
            'property_id' => ['nullable', 'exists:properties,id'],
            'name' => ['required', 'string', 'max:150'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:150'],
            'message' => ['required', 'string'],
            'source' => ['nullable', 'string', 'max:50'],
        ]);

        $lead = Lead::create($data);

        return response()->json($lead, 201);
    }

    public function index()
    {
        return Lead::with('property')
            ->latest()
            ->paginate(20);
    }

    public function update(Request $request, Lead $lead)
    {
        $data = $request->validate([
            'status' => ['required', 'in:new,contacted,closed'],
        ]);

        $lead->update($data);

        return response()->json($lead);
    }
}
