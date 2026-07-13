<?php

namespace Database\Seeders;

use App\Models\TrainerCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TrainerCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Pre-Trial Assessment', 'is_assessment_category' => true, 'description' => 'Senior trainer health-history visit before a free trial is booked.'],
            ['name' => 'Functional Training', 'is_assessment_category' => false, 'description' => 'Full-body functional strength and movement training.'],
            ['name' => 'Aerobics', 'is_assessment_category' => false, 'description' => 'Cardio-focused group and individual aerobics sessions.'],
            ['name' => 'Yoga', 'is_assessment_category' => false, 'description' => 'Yoga, flexibility, and mindfulness sessions.'],
            ['name' => 'Strength & Conditioning', 'is_assessment_category' => false, 'description' => 'Weight training and conditioning.'],
            ['name' => 'Zumba', 'is_assessment_category' => false, 'description' => 'Dance-fitness group sessions.'],
        ];

        foreach ($categories as $category) {
            TrainerCategory::firstOrCreate(
                ['slug' => Str::slug($category['name'])],
                [
                    'name' => $category['name'],
                    'description' => $category['description'],
                    'is_assessment_category' => $category['is_assessment_category'],
                    'is_active' => true,
                ]
            );
        }
    }
}
