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
     * @param  array<int, array{date: string, start: string, end: string}>  $sessionSlots
     *
     * @throws SlotUnavailableException
     */
    public function bookTrial(
        Client $client,
        TrainerProfile $trainer,
        TrainerCategory $category,
        User $bookedBy,
        string $type,
        array $sessionSlots,
    ): Trial {
        $expectedSessions = $type === Trial::TYPE_PRE_VISIT ? 1 : 3;

        if (count($sessionSlots) !== $expectedSessions) {
            throw new SlotUnavailableException("A {$type} booking requires exactly {$expectedSessions} session(s).");
        }

        return DB::transaction(function () use ($client, $trainer, $category, $bookedBy, $type, $sessionSlots, $expectedSessions) {
            foreach ($sessionSlots as $slot) {
                $date = Carbon::parse($slot['date']);

                if (! $this->availability->isSlotFree($trainer, $date, $slot['start'])) {
                    throw new SlotUnavailableException(
                        "The slot on {$date->format('d M Y')} at {$slot['start']} is no longer available. Please choose another time."
                    );
                }
            }

            $trial = Trial::create([
                'client_id' => $client->id,
                'trainer_profile_id' => $trainer->id,
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
                        'trainer_profile_id' => $trainer->id,
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
