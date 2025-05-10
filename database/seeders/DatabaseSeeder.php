<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();


        $this->call([
            RoleSeeder::class,
            UserSeeder::class,
            CuatrimestreSeeder::class,
            AccionSeeder::class,
            LicenciaturaSeeder::class,
            ModalidadSeeder::class,
            MesSeeder::class,
            // Add other seeders here
        ]);


    }
}
