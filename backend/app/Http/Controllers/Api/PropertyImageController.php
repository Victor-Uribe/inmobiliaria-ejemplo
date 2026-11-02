<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Property;
use App\Models\PropertyImage;
use Illuminate\Support\Facades\Storage;

class PropertyImageController extends Controller
{
    //
    public function store(Request $request, Property $property)
    {
        $this->authorizeAdvisorProperty($property);

        $data = $request->validate([
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'is_main' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer'],
        ]);

        $path = $request->file('image')->store('properties', 'public');

        if ($request->boolean('is_main')) {
            $property->images()->update(['is_main' => false]);
            $property->update(['main_image' => $path]);
        }

        $image = $property->images()->create([
            'image_path' => $path,
            'is_main' => $request->boolean('is_main'),
            'sort_order' => $data['sort_order'] ?? 0,
        ]);

        return response()->json($image, 201);
    }

    public function setMain(PropertyImage $image)
    {
        $property = $image->property;

        $this->authorizeAdvisorProperty($property);

        $property->images()->update(['is_main' => false]);

        $image->update(['is_main' => true]);
        $property->update(['main_image' => $image->image_path]);

        return response()->json([
            'message' => 'Imagen principal actualizada',
            'image' => $image,
        ]);
    }

    public function destroy(PropertyImage $image)
    {
        $property = $image->property;

        $this->authorizeAdvisorProperty($property);

        Storage::disk('public')->delete($image->image_path);

        if ($property->main_image === $image->image_path) {
            $property->update(['main_image' => null]);
        }

        $image->delete();

        return response()->json([
            'message' => 'Imagen eliminada correctamente',
        ]);
    }

    private function authorizeAdvisorProperty(Property $property)
    {
        $user = auth('api')->user();

        if ($user->role->slug === 'asesor' && $property->user_id !== $user->id) {
            abort(403, 'No puedes administrar imágenes de otra propiedad');
        }
    }
}
