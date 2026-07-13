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
        $this->call([
            RolesAndPermissionsSeeder::class,
            TrainerCategorySeeder::class,
            PackageSeeder::class,
            InterestLevelSeeder::class,
            UserSeeder::class,
            ClientSeeder::class,
            TrialSeeder::class,
        ]);
    }
}
