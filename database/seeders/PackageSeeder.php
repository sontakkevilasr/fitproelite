<?php

namespace Database\Seeders;

use App\Models\Package;
use Illuminate\Database\Seeder;

class PackageSeeder extends Seeder
{
    public function run(): void
    {
        $packages = [
            ['name' => '1 Month', 'price' => 2500],
            ['name' => '3 Month', 'price' => 6500],
            ['name' => '6 Month', 'price' => 11500],
            ['name' => 'Annual', 'price' => 20000],
            ['name' => 'Personal Training', 'price' => 30000],
        ];

        foreach ($packages as $package) {
            Package::firstOrCreate(
                ['name' => $package['name']],
                ['price' => $package['price'], 'is_active' => true]
            );
        }
    }
}
