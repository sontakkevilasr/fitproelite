<?php

namespace App\Services;

use App\Exceptions\SlotUnavailableException;
use App\Models\Client;
use App\Models\TrainerCategory;
use App\Models\TrainerProfile;
use App\Models\Trial;
use App\Models\TrialSession;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class TrialBookingService
{
    public function __construct(
        private SlotAvailabilityService $availability,
        private WhatsAppNotificationService $notifications,
    ) {
    }

    /**
     * Each session carries its own trainer_profile_id — usually all the same
     * trainer, but a session plan may legitimately split sessions across
     * different trainers in the category based on who was available.
     *
     * @param  array<int, array{trainer_profile_id: int, date: string, start: string, end: string}>  $sessionSlots
     *
     * @throws SlotUnavailableException
     */
    public function bookTrial(
        Client $client,
        TrainerCategory $category,
        User $bookedBy,
        string $type,
        array $sessionSlots,
        int $expectedSessions,
    ): Trial {
        if (count($sessionSlots) !== $expectedSessions) {
            throw new SlotUnavailableException("A {$type} booking requires exactly {$expectedSessions} session(s).");
        }

        return DB::transaction(function () use ($client, $category, $bookedBy, $type, $sessionSlots, $expectedSessions) {
            $trainers = TrainerProfile::whereIn('id', collect($sessionSlots)->pluck('trainer_profile_id')->unique())
                ->get()->keyBy('id');

            foreach ($sessionSlots as $slot) {
                $date = Carbon::parse($slot['date']);
                $trainer = $trainers->get($slot['trainer_profile_id']);

                abort_unless($trainer, 404);

                if (! $this->availability->isSlotFree($trainer, $date, $slot['start'])) {
                    throw new SlotUnavailableException(
                        "The slot on {$date->format('d M Y')} at {$slot['start']} is no longer available. Please choose another time."
                    );
                }
            }

            $trial = Trial::create([
                'client_id' => $client->id,
                'trainer_profile_id' => $sessionSlots[0]['trainer_profile_id'],
                'counsellor_id' => $client->created_by,
                'booked_by_user_id' => $bookedBy->id,
                'trainer_category_id' => $category->id,
                'type' => $type,
                'total_sessions' => $expectedSessions,
                'status' => Trial::STATUS_SCHEDULED,
            ]);

            try {
                foreach ($sessionSlots as $index => $slot) {
                    TrialSession::create([
                        'trial_id' => $trial->id,
                        'trainer_profile_id' => $slot['trainer_profile_id'],
                        'trainer_category_id' => $slot['category_id'] ?? $category->id,
                        'session_number' => $index + 1,
                        'session_date' => $slot['date'],
                        'start_time' => $slot['start'],
                        'end_time' => $slot['end'],
                        'status' => TrialSession::STATUS_SCHEDULED,
                    ]);
                }
            } catch (QueryException $e) {
                throw new SlotUnavailableException(
                    'One of the selected slots was just booked by someone else. Please choose another time.',
                    previous: $e,
                );
            }

            $client->update([
                'status' => $type === Trial::TYPE_PRE_VISIT ? Client::STATUS_PRE_VISIT_SCHEDULED : Client::STATUS_TRIAL_SCHEDULED,
            ]);

            if ($type === Trial::TYPE_PRE_VISIT) {
                $this->notifications->notifyPreVisitScheduled($trial);
            } else {
                $this->notifications->notifyTrialScheduled($trial);
            }

            return $trial->load('sessions');
        });
    }
}
