<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Testimonial;

class TestimonialController extends Controller
{
    //
    public function publicIndex()
    {
        return Testimonial::where('status', true)->latest()->get();
    }

    public function index()
    {
        return Testimonial::latest()->paginate(15);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'client_name' => ['required', 'string', 'max:150'],
            'comment' => ['required', 'string'],
            'photo' => ['nullable', 'string'],
            'status' => ['nullable', 'boolean'],
        ]);

        return response()->json(Testimonial::create($data), 201);
    }

    public function update(Request $request, Testimonial $testimonial)
    {
        $data = $request->validate([
            'client_name' => ['sometimes', 'string', 'max:150'],
            'comment' => ['sometimes', 'string'],
            'photo' => ['nullable', 'string'],
            'status' => ['sometimes', 'boolean'],
        ]);

        $testimonial->update($data);

        return response()->json($testimonial);
    }

    public function destroy(Testimonial $testimonial)
    {
        $testimonial->delete();

        return response()->json(['message' => 'Testimonio eliminado']);
    }
}
