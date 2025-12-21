<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Run seeders in order (dependencies first)
        $this->call([
            CategorySeeder::class,
            UnitSeeder::class,
            UserSeeder::class,
            SettingSeeder::class,
        ]);

        // Optionally run simulation seeder for demo data
        // Uncomment the line below to populate with 2 weeks of realistic data
        // $this->call(SimulationSeeder::class);
    }
}
