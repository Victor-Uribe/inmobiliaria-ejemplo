<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Lead;
use App\Models\Property;

class LeadSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $property = Property::first();

        Lead::create([
            'property_id' => $property?->id,
            'name' => 'Cliente Demo',
            'phone' => '5599999999',
            'email' => 'cliente.demo@email.com',
            'message' => 'Hola, me interesa recibir más información de esta propiedad.',
            'source' => 'landing',
            'status' => 'new',
        ]);
    }
}
