<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Banner;

class BannerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        Banner::updateOrCreate(
            ['title' => 'Encuentra tu próxima propiedad en Ixtapaluca'],
            [
                'subtitle' => 'Casas, departamentos y oportunidades inmobiliarias en Estado de México.',
                'image' => null,
                'button_text' => 'Ver propiedades',
                'button_url' => '/propiedades',
                'status' => true,
            ]
        );
    }
}
