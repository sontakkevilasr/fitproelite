<?php

namespace Database\Seeders;

use App\Models\InterestLevel;
use Illuminate\Database\Seeder;

class InterestLevelSeeder extends Seeder
{
    public function run(): void
    {
        $levels = [
            ['name' => 'Hot', 'color' => '#dc2626', 'sort_order' => 1],
            ['name' => 'Warm', 'color' => '#d97706', 'sort_order' => 2],
            ['name' => 'Cold', 'color' => '#2563eb', 'sort_order' => 3],
        ];

        foreach ($levels as $level) {
            InterestLevel::firstOrCreate(
                ['name' => $level['name']],
                ['color' => $level['color'], 'sort_order' => $level['sort_order'], 'is_active' => true]
            );
        }
    }
}
