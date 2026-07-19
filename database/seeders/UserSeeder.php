<?php

namespace Database\Seeders;

use App\Models\TrainerCategory;
use App\Models\TrainerProfile;
use App\Models\TrainerWeeklySlot;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $password = Hash::make('Password@123');

        $admin = User::firstOrCreate(
            ['email' => 'admin@schedulars.test'],
            ['name' => 'Studio Admin', 'password' => $password, 'email_verified_at' => now(), 'is_active' => true]
        );
        $admin->assignRole('admin');

        $counsellor = User::firstOrCreate(
            ['email' => 'counsellor@schedulars.test'],
            ['name' => 'Priya Counsellor', 'password' => $password, 'email_verified_at' => now(), 'is_active' => true]
        );
        $counsellor->assignRole('counsellor');

        $trainerSeeds = [
            ['category' => 'Pre-Trial Assessment', 'email' => 'trainer.assessment@schedulars.test', 'name' => 'Ramesh Senior Trainer'],
            ['category' => 'Functional Training', 'email' => 'trainer.functional@schedulars.test', 'name' => 'Arjun Functional'],
            ['category' => 'Aerobics', 'email' => 'trainer.aerobics@schedulars.test', 'name' => 'Sneha Aerobics'],
            ['category' => 'Yoga', 'email' => 'trainer.yoga@schedulars.test', 'name' => 'Kavita Yoga'],
            ['category' => 'Strength & Conditioning', 'email' => 'trainer.strength@schedulars.test', 'name' => 'Vikram Strength'],
            ['category' => 'Zumba', 'email' => 'trainer.zumba@schedulars.test', 'name' => 'Meera Zumba'],
        ];

        foreach ($trainerSeeds as $seed) {
            $category = TrainerCategory::where('name', $seed['category'])->firstOrFail();

            $user = User::firstOrCreate(
                ['email' => $seed['email']],
                ['name' => $seed['name'], 'password' => $password, 'email_verified_at' => now(), 'is_active' => true]
            );
            $user->assignRole('trainer');

            $profile = TrainerProfile::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'bio' => $seed['name'].' is an experienced '.$seed['category'].' trainer.',
                    'session_duration_minutes' => 60,
                    'is_active' => true,
                ]
            );
            $profile->categories()->syncWithoutDetaching([$category->id]);

            if ($profile->weeklySlots()->count() === 0) {
                foreach (range(1, 5) as $day) { // Mon-Fri
                    TrainerWeeklySlot::create([
                        'trainer_profile_id' => $profile->id,
                        'day_of_week' => $day,
                        'start_time' => '09:00',
                        'end_time' => '13:00',
                    ]);
                    TrainerWeeklySlot::create([
                        'trainer_profile_id' => $profile->id,
                        'day_of_week' => $day,
                        'start_time' => '14:00',
                        'end_time' => '18:00',
                    ]);
                }
                TrainerWeeklySlot::create([
                    'trainer_profile_id' => $profile->id,
                    'day_of_week' => 6, // Saturday
                    'start_time' => '09:00',
                    'end_time' => '13:00',
                ]);
            }
        }
    }
}
