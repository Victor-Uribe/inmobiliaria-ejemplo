<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Testimonial;


class TestimonialSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $items = [
            [
                'client_name' => 'María González',
                'comment' => 'Nos ayudaron a encontrar una casa en Ixtapaluca con buena ubicación y dentro de nuestro presupuesto.',
            ],
            [
                'client_name' => 'Carlos Ramírez',
                'comment' => 'La atención fue clara, rápida y profesional. Me dieron confianza desde el primer contacto.',
            ],
            [
                'client_name' => 'Fernanda López',
                'comment' => 'Excelente seguimiento por WhatsApp y explicación de cada propiedad.',
            ],
        ];

        foreach ($items as $item) {
            Testimonial::updateOrCreate(
                ['client_name' => $item['client_name']],
                [
                    'comment' => $item['comment'],
                    'photo' => null,
                    'status' => true,
                ]
            );
        }
    }
}
