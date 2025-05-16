<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        User::factory()->create([
            'username' => 'Moctezuma',
            'email' => 'centrouniversitariomoctezuma@gmail.com',
            'CURP' => 'REVE123456HDFNVR00',
            'password' => bcrypt('12345678'),
        ])->assignRole('SuperAdmin');

        User::factory(1)->create()->each(function ($user) {
            $user->assignRole('Estudiante');
        });
    }
}
