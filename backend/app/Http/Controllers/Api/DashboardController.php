<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Lead;
use App\Models\Property;
use App\Models\Testimonial;
use App\Models\User;

class DashboardController extends Controller
{
    //
    public function index()
    {
        $user = auth('api')->user();

        $propertiesQuery = Property::query();

        if ($user->role->slug === 'asesor') {
            $propertiesQuery->where('user_id', $user->id);
        }

        return response()->json([
            'total_properties' => (clone $propertiesQuery)->count(),
            'featured_properties' => (clone $propertiesQuery)->where('is_featured', true)->count(),
            'active_properties' => (clone $propertiesQuery)->where('status', 'active')->count(),
            'sold_properties' => (clone $propertiesQuery)->where('status', 'sold')->count(),
            'total_users' => $user->role->slug === 'asesor' ? null : User::count(),
            'total_testimonials' => Testimonial::count(),
            'total_leads' => Lead::count(),
        ]);
    }
}
