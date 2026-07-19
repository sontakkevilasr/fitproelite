<?php

namespace Database\Seeders;

use App\Models\Package;
use App\Models\TrainerCategory;
use Illuminate\Database\Seeder;

class PackageSeeder extends Seeder
{
    public function run(): void
    {
        $packages = [
            [
                'name' => '1 Month',
                'price' => 2500,
                'week_days' => 3,
                'rows' => ['Functional Training' => 8, 'Yoga' => 4],
            ],
            [
                'name' => '3 Month',
                'price' => 6500,
                'week_days' => 3,
                'rows' => ['Functional Training' => 8, 'Aerobics' => 4],
            ],
            [
                'name' => '6 Month',
                'price' => 11500,
                'week_days' => 6,
                'rows' => ['Strength & Conditioning' => 12, 'Functional Training' => 8, 'Aerobics' => 4],
            ],
            [
                'name' => 'Annual',
                'price' => 20000,
                'week_days' => 6,
                'rows' => ['Strength & Conditioning' => 12, 'Yoga' => 8, 'Zumba' => 4],
            ],
            [
                'name' => 'Personal Training',
                'price' => 30000,
                'week_days' => 3,
                'rows' => ['Strength & Conditioning' => 12],
            ],
        ];

        foreach ($packages as $data) {
            $package = Package::firstOrCreate(
                ['name' => $data['name']],
                [
                    'price' => $data['price'],
                    'week_days' => $data['week_days'],
                    'sessions_count' => array_sum($data['rows']),
                    'trial_sessions_count' => 3,
                    'is_active' => true,
                ]
            );

            if ($package->trainerCategories()->count() === 0) {
                $pivotData = collect($data['rows'])
                    ->mapWithKeys(function ($sessions, $categoryName) {
                        $category = TrainerCategory::where('name', $categoryName)->first();

                        return $category ? [$category->id => ['sessions' => $sessions]] : [];
                    })
                    ->all();

                $package->trainerCategories()->sync($pivotData);
            }
        }
    }
}
