<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\ClientAssessment;
use App\Models\TrainerCategory;
use App\Models\TrainerProfile;
use App\Models\Trial;
use App\Models\TrialSession;
use App\Models\User;
use App\Services\SlotAvailabilityService;
use App\Services\TrialBookingService;
use Illuminate\Database\Seeder;

class TrialSeeder extends Seeder
{
    public function run(): void
    {
        if (Trial::count() > 0) {
            return;
        }

        $counsellor = User::role('counsellor')->first();
        $assessmentTrainer = TrainerProfile::whereHas('category', fn ($q) => $q->where('is_assessment_category', true))->first();
        $functionalTrainer = TrainerProfile::whereHas('category', fn ($q) => $q->where('name', 'Functional Training'))->first();
        $yogaTrainer = TrainerProfile::whereHas('category', fn ($q) => $q->where('name', 'Yoga'))->first();

        if (! $counsellor || ! $assessmentTrainer || ! $functionalTrainer) {
            return;
        }

        $clients = Client::take(3)->get();
        if ($clients->count() < 3) {
            return;
        }

        [$converted, $lost, $upcoming] = $clients;

        // Converted journey: pre-visit -> assessment -> free trial -> converted.
        $preVisit = Trial::create([
            'client_id' => $converted->id,
            'trainer_profile_id' => $assessmentTrainer->id,
            'counsellor_id' => $counsellor->id,
            'booked_by_user_id' => $counsellor->id,
            'trainer_category_id' => $assessmentTrainer->trainer_category_id,
            'type' => Trial::TYPE_PRE_VISIT,
            'total_sessions' => 1,
            'status' => Trial::STATUS_COMPLETED,
        ]);
        TrialSession::create([
            'trial_id' => $preVisit->id,
            'trainer_profile_id' => $assessmentTrainer->id,
            'session_number' => 1,
            'session_date' => now()->subDays(10)->format('Y-m-d'),
            'start_time' => '10:00',
            'end_time' => '11:00',
            'status' => TrialSession::STATUS_COMPLETED,
        ]);
        ClientAssessment::create([
            'client_id' => $converted->id,
            'trial_id' => $preVisit->id,
            'first_time_gym' => true,
            'workout_objective' => 'weight_loss',
            'notes' => 'Motivated, no injuries.',
            'recommended_category_id' => $functionalTrainer->trainer_category_id,
            'filled_by' => $assessmentTrainer->user_id,
        ]);

        $convertedTrial = Trial::create([
            'client_id' => $converted->id,
            'trainer_profile_id' => $functionalTrainer->id,
            'counsellor_id' => $counsellor->id,
            'booked_by_user_id' => $assessmentTrainer->user_id,
            'trainer_category_id' => $functionalTrainer->trainer_category_id,
            'type' => Trial::TYPE_FREE_TRIAL,
            'total_sessions' => 3,
            'status' => Trial::STATUS_CONVERTED,
            'outcome_notes' => 'Signed up for the Annual package.',
            'decided_at' => now()->subDays(2),
        ]);
        foreach (range(1, 3) as $i) {
            TrialSession::create([
                'trial_id' => $convertedTrial->id,
                'trainer_profile_id' => $functionalTrainer->id,
                'session_number' => $i,
                'session_date' => now()->subDays(9 - ($i * 2))->format('Y-m-d'),
                'start_time' => '09:00',
                'end_time' => '10:00',
                'status' => TrialSession::STATUS_COMPLETED,
            ]);
        }
        $converted->update(['status' => Client::STATUS_CONVERTED]);

        // Lost journey: free trial that didn't convert.
        if ($yogaTrainer) {
            $lostTrial = Trial::create([
                'client_id' => $lost->id,
                'trainer_profile_id' => $yogaTrainer->id,
                'counsellor_id' => $counsellor->id,
                'booked_by_user_id' => $assessmentTrainer->user_id,
                'trainer_category_id' => $yogaTrainer->trainer_category_id,
                'type' => Trial::TYPE_FREE_TRIAL,
                'total_sessions' => 3,
                'status' => Trial::STATUS_LOST,
                'outcome_notes' => 'Found it too far from home.',
                'decided_at' => now()->subDay(),
            ]);
            foreach (range(1, 3) as $i) {
                TrialSession::create([
                    'trial_id' => $lostTrial->id,
                    'trainer_profile_id' => $yogaTrainer->id,
                    'session_number' => $i,
                    'session_date' => now()->subDays(7 - ($i * 2))->format('Y-m-d'),
                    'start_time' => '11:00',
                    'end_time' => '12:00',
                    'status' => $i === 3 ? TrialSession::STATUS_NO_SHOW : TrialSession::STATUS_COMPLETED,
                ]);
            }
            $lost->update(['status' => Client::STATUS_LOST]);
        }

        // Upcoming: a real pre-visit booking via the live booking service.
        $availability = app(SlotAvailabilityService::class);
        $slot = $availability->freeSlotsForDate($assessmentTrainer, now()->addDays(2))->first();

        if ($slot) {
            app(TrialBookingService::class)->bookTrial(
                client: $upcoming,
                trainer: $assessmentTrainer,
                category: $assessmentTrainer->category,
                bookedBy: $counsellor,
                type: Trial::TYPE_PRE_VISIT,
                sessionSlots: [['date' => now()->addDays(2)->format('Y-m-d'), 'start' => $slot['start'], 'end' => $slot['end']]],
            );
        }
    }
}
