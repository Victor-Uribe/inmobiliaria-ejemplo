<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Property;
use App\Models\User;
use Illuminate\Support\Str;

class PropertySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $asesor = User::where('email', 'asesor@inmobiliaria.com')->first();


         $properties = [
            [
                'title' => 'Casa familiar en Ixtapaluca Centro',
                'price' => 1450000,
                'municipality' => 'Ixtapaluca',
                'description' => 'Casa ideal para familia, cercana a comercios, escuelas y transporte.',
                'is_featured' => true,
                'latitude' => 19.3186,
                'longitude' => -98.8822,
            ],
            [
                'title' => 'Casa en venta cerca de Plaza Sendero',
                'price' => 1850000,
                'municipality' => 'Ixtapaluca',
                'description' => 'Propiedad con excelente ubicación, espacios amplios y buena plusvalía.',
                'is_featured' => true,
                'latitude' => 19.3069,
                'longitude' => -98.8728,
            ],
            [
                'title' => 'Departamento accesible en Estado de México',
                'price' => 890000,
                'municipality' => 'Ixtapaluca',
                'description' => 'Departamento práctico para primera inversión o vivienda familiar.',
                'is_featured' => false,
                'latitude' => 19.3150,
                'longitude' => -98.8890,
            ],
        ];

        foreach ($properties as $item) {
            Property::updateOrCreate(
                ['slug' => Str::slug($item['title'])],
                [
                    'user_id' => $asesor->id,
                    'title' => $item['title'],
                    'slug' => Str::slug($item['title']),
                    'price' => $item['price'],
                    'state' => 'Estado de México',
                    'municipality' => $item['municipality'],
                    'contact_name' => 'Inmobiliaria',
                    'contact_phone' => '5512345678',
                    'description' => $item['description'],
                    'main_image' => null,
                    'latitude' => $item['latitude'],
                    'longitude' => $item['longitude'],
                    'is_featured' => $item['is_featured'],
                    'status' => 'active',
                ]
            );
        }

    }
}
