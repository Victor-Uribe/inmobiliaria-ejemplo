<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\User;
use App\Models\Role;


class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $superAdmin = Role::where('slug', 'super-admin')->first();
        $admin = Role::where('slug', 'admin')->first();
        $asesor = Role::where('slug', 'asesor')->first();

        User::create([
            'role_id' =>  $superAdmin->id,
            'name' => 'Administrador',
            'email' => 'vic.hug.urib@gmail.com',
            'phone' => '5566266152',
            'password' => bcrypt('password123'),
            'status' => true,
        ]);

        User::updateOrCreate(
            ['email' => 'gerencia@inmobiliaria.com'],
            [
                'role_id' => $admin->id,
                'name' => 'Gerencia Comercial',
                'phone' => '5523456789',
                'password' => bcrypt('password123'),
                'status' => true,
            ]
        );

        User::updateOrCreate(
            ['email' => 'asesor@inmobiliaria.com'],
            [
                'role_id' => $asesor->id,
                'name' => 'Asesor Ixtapaluca',
                'phone' => '5534567890',
                'password' => bcrypt('password123'),
                'status' => true,
            ]
        );
    }
}
