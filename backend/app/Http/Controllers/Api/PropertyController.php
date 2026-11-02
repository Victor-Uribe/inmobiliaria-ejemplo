<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Property;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class PropertyController extends Controller
{
    public function publicIndex(Request $request)
    {
        $properties = Property::with(['advisor', 'images'])
            ->where('status', 'active')
            ->when($request->municipality, fn ($q) => $q->where('municipality', $request->municipality))
            ->when($request->state, fn ($q) => $q->where('state', $request->state))
            ->when($request->is_featured, fn ($q) => $q->where('is_featured', true))
            ->when($request->min_price, fn ($q) => $q->where('price', '>=', $request->min_price))
            ->when($request->max_price, fn ($q) => $q->where('price', '<=', $request->max_price))
            ->latest()
            ->paginate(12);

        return response()->json($properties);
    }

    public function publicFeatured()
    {
        return Property::with(['advisor', 'images'])
            ->where('status', 'active')
            ->where('is_featured', true)
            ->latest()
            ->take(8)
            ->get();
    }

    public function publicShow($slug)
    {
        $property = Property::with(['advisor', 'images'])
            ->where('slug', $slug)
            ->where('status', 'active')
            ->firstOrFail();

        return response()->json($property);
    }

    public function index(Request $request)
    {
        $user = auth('api')->user();

        $perPage = (int) $request->get('per_page', 15);
        $perPage = $perPage > 0 && $perPage <= 50 ? $perPage : 15;

        $search = trim((string) $request->get('search', ''));
        $status = $request->get('status');
        $payments = $request->get('payments');

        $properties = Property::query()
            ->with(['advisor', 'images', 'buyer.payments'])

            // Si es asesor, solo ve sus propiedades
            ->when(
                $user && $user->role && $user->role->slug === 'asesor',
                fn ($q) => $q->where('user_id', $user->id)
            )

            // Filtro buscar
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($query) use ($search) {
                    $query
                        ->where('title', 'LIKE', "%{$search}%")
                        ->orWhere('state', 'LIKE', "%{$search}%")
                        ->orWhere('municipality', 'LIKE', "%{$search}%")
                        ->orWhere('description', 'LIKE', "%{$search}%")
                        ->orWhere('price', 'LIKE', "%{$search}%")
                        ->orWhereHas('buyer', function ($buyer) use ($search) {
                            $buyer
                                ->where('buyer_name', 'LIKE', "%{$search}%")
                                ->orWhere('buyer_number', 'LIKE', "%{$search}%")
                                ->orWhere('buyer_phone', 'LIKE', "%{$search}%")
                                ->orWhere('buyer_email', 'LIKE', "%{$search}%");
                        });
                });
            })

            // Filtro estatus
            ->when($status, function ($q) use ($status) {
                if ($status === 'sold') {
                    $q->where('status', 'sold');
                }

                if ($status === 'active') {
                    $q->where('status', 'active');
                }

                if ($status === 'inactive') {
                    $q->where('status', 'inactive');
                }
            })

            // Filtro pagos
            ->when($payments, function ($q) use ($payments) {
                if ($payments === 'without_buyer') {
                    $q->whereDoesntHave('buyer');
                }

                if ($payments === 'current') {
                    $q->whereHas('buyer', function ($buyer) {
                        $buyer->where('payment_color', 'green');
                    });
                }

                if ($payments === 'pending') {
                    $q->whereHas('buyer', function ($buyer) {
                        $buyer->whereIn('payment_color', ['yellow', 'red']);
                    });
                }

                if ($payments === 'yellow') {
                    $q->whereHas('buyer', function ($buyer) {
                        $buyer->where('payment_color', 'yellow');
                    });
                }

                if ($payments === 'red') {
                    $q->whereHas('buyer', function ($buyer) {
                        $buyer->where('payment_color', 'red');
                    });
                }
            })

            ->latest()
            ->paginate($perPage);

        return response()->json($properties);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);

        if (auth('api')->user()->role->slug === 'asesor') {
            $data['user_id'] = auth('api')->id();
        }

        $data['slug'] = Str::slug($data['title']) . '-' . Str::random(6);

        if ($request->hasFile('main_image')) {
            $data['main_image'] = $request->file('main_image')->store('properties', 'public');
        }

        $property = Property::create($data);

        return response()->json($property, 201);
    }

    public function update(Request $request, Property $property)
    {

        $this->authorizeAdvisorProperty($property);

        $data = $this->validateData($request, true);

        if (isset($data['title'])) {
            $data['slug'] = Str::slug($data['title']) . '-' . Str::random(6);
        }


        if ($request->hasFile('main_image')) {
            if ($property->main_image) {
                Storage::disk('public')->delete($property->main_image);
            }

            $data['main_image'] = $request->file('main_image')->store('properties', 'public');
        }

        $property->update($data);

        return response()->json($property);
    }

    public function destroy(Property $property)
    {
        $this->authorizeAdvisorProperty($property);

        $property->delete();

        return response()->json([
            'message' => 'Propiedad eliminada correctamente',
        ]);
    }

    private function validateData(Request $request, bool $isUpdate = false)
    {
        return $request->validate([
            'user_id' => [$isUpdate ? 'sometimes' : 'required', 'exists:users,id'],
            'title' => [$isUpdate ? 'sometimes' : 'required', 'string', 'max:255'],
            'price' => [$isUpdate ? 'sometimes' : 'required', 'numeric', 'min:0'],
            'state' => ['sometimes', 'string', 'max:100'],
            'municipality' => ['sometimes', 'string', 'max:100'],
            'contact_name' => ['nullable', 'string', 'max:150'],
            'contact_phone' => ['nullable', 'string', 'max:30'],
            'description' => ['nullable', 'string'],
            'main_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'main_image' => ['nullable'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'is_featured' => ['sometimes', 'boolean'],
            'status' => ['sometimes', 'in:active,inactive,sold'],
        ]);
    }

    private function authorizeAdvisorProperty(Property $property)
    {
        $user = auth('api')->user();

        if ($user->role->slug === 'asesor' && $property->user_id !== $user->id) {
            abort(403, 'No puedes administrar propiedades de otro asesor');
        }
    }

    public function show(Property $property)
    {
        $this->authorizeAdvisorProperty($property);

        return response()->json($property->load(['advisor', 'images']));
    }


    public function share(Property $property)
    {
        $property->load('images');

        $mainImage = $property->images
            ->where('is_main', 1)
            ->first() ?? $property->images->first();

        $imageUrl = $mainImage
            ? url(Storage::url($mainImage->path))
            : asset('img/share-default.jpg');

        $frontUrl = rtrim(config('app.frontend_url', 'https://inmobiliaria.maviko.com.mx'), '/')
            . '/propiedades/' . $property->id;

        $title = $property->title
            ?? $property->name
            ?? 'Propiedad en venta';

        $location = trim(
            ($property->municipality ?? '') . ', ' . ($property->state ?? ''),
            ', '
        );

        $price = $property->price
            ? '$' . number_format($property->price, 0) . ' MXN'
            : 'Precio disponible por WhatsApp';

        $description = trim($price . ' · ' . $location . ' · Conoce esta propiedad y solicita información.');

        return response()->view('share.property', [
            'property' => $property,
            'title' => $title,
            'description' => $description,
            'imageUrl' => $imageUrl,
            'frontUrl' => $frontUrl,
        ]);
    }

}
