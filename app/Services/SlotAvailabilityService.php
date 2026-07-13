<?php

namespace App\Services;

use App\Models\TrainerProfile;
use App\Models\TrialSession;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;

class SlotAvailabilityService
{
    /**
     * Build a FullCalendar-ready list of free/busy/blocked events for a trainer
     * between $from and $to (inclusive dates).
     */
    public function getEvents(TrainerProfile $trainer, Carbon $from, Carbon $to): array
    {
        $events = [];

        foreach (CarbonPeriod::create($from->copy()->startOfDay(), $to->copy()->startOfDay()) as $date) {
            foreach ($this->freeSlotsForDate($trainer, $date) as $slot) {
                $events[] = [
                    'title' => 'Free',
                    'start' => $date->format('Y-m-d').'T'.$slot['start'],
                    'end' => $date->format('Y-m-d').'T'.$slot['end'],
                    'color' => '#14b382',
                    'extendedProps' => ['type' => 'free'],
                ];
            }

            foreach ($this->bookedSessionsForDate($trainer, $date) as $session) {
                $events[] = [
                    'title' => 'Booked',
                    'start' => $date->format('Y-m-d').'T'.$session->start_time,
                    'end' => $date->format('Y-m-d').'T'.$session->end_time,
                    'color' => '#dc2626',
                    'extendedProps' => ['type' => 'busy', 'session_id' => $session->id],
                ];
            }

            foreach ($this->blockedRangesForDate($trainer, $date) as $block) {
                $events[] = [
                    'title' => $block->reason ?? 'Blocked',
                    'start' => $date->format('Y-m-d').'T'.($block->start_time ?? '00:00'),
                    'end' => $date->format('Y-m-d').'T'.($block->end_time ?? '23:59'),
                    'display' => 'background',
                    'color' => '#9ca3af',
                ];
            }
        }

        return $events;
    }

    /**
     * Free 60-minute-ish slots (trainer's session duration) for one date, as
     * a collection of ['start' => 'H:i', 'end' => 'H:i'].
     */
    public function freeSlotsForDate(TrainerProfile $trainer, Carbon $date): Collection
    {
        $duration = $trainer->session_duration_minutes ?: 60;
        $dayOfWeek = $date->dayOfWeek;

        $weeklyRanges = $trainer->weeklySlots()
            ->where('day_of_week', $dayOfWeek)
            ->where('is_active', true)
            ->get();

        $blocked = $this->blockedRangesForDate($trainer, $date);
        $booked = $this->bookedSessionsForDate($trainer, $date);

        $free = collect();

        foreach ($weeklyRanges as $range) {
            $cursor = $date->copy()->setTimeFromTimeString($range->start_time);
            $rangeEnd = $date->copy()->setTimeFromTimeString($range->end_time);

            while ($cursor->copy()->addMinutes($duration)->lte($rangeEnd)) {
                $slotStart = $cursor->copy();
                $slotEnd = $cursor->copy()->addMinutes($duration);

                $isPast = $slotStart->lt(now());

                $isBlocked = $blocked->contains(function ($block) use ($date, $slotStart, $slotEnd) {
                    $blockStart = $block->start_time ? $date->copy()->setTimeFromTimeString($block->start_time) : $date->copy()->startOfDay();
                    $blockEnd = $block->end_time ? $date->copy()->setTimeFromTimeString($block->end_time) : $date->copy()->endOfDay();

                    return $blockStart->lt($slotEnd) && $blockEnd->gt($slotStart);
                });

                $isBooked = $booked->contains(function (TrialSession $session) use ($date, $slotStart, $slotEnd) {
                    $bookedStart = $date->copy()->setTimeFromTimeString($session->start_time);
                    $bookedEnd = $date->copy()->setTimeFromTimeString($session->end_time);

                    return $bookedStart->lt($slotEnd) && $bookedEnd->gt($slotStart);
                });

                if (! $isPast && ! $isBlocked && ! $isBooked) {
                    $free->push(['start' => $slotStart->format('H:i'), 'end' => $slotEnd->format('H:i')]);
                }

                $cursor->addMinutes($duration);
            }
        }

        return $free->values();
    }

    public function isSlotFree(TrainerProfile $trainer, Carbon $date, string $startTime): bool
    {
        return $this->freeSlotsForDate($trainer, $date)
            ->contains(fn ($slot) => $slot['start'] === Carbon::parse($startTime)->format('H:i'));
    }

    /**
     * Find the next free slot on/after $afterDate (exclusive of that date),
     * preferring the same time of day, within $lookAheadDays. Falls back to
     * the earliest free slot on the earliest day that has one.
     */
    public function nextFreeSlot(TrainerProfile $trainer, Carbon $afterDate, string $preferredStart, int $lookAheadDays = 14): ?array
    {
        $fallback = null;

        for ($i = 1; $i <= $lookAheadDays; $i++) {
            $date = $afterDate->copy()->addDays($i);
            $slots = $this->freeSlotsForDate($trainer, $date);

            if ($slots->isEmpty()) {
                continue;
            }

            $exact = $slots->firstWhere('start', Carbon::parse($preferredStart)->format('H:i'));

            if ($exact) {
                return ['date' => $date->format('Y-m-d'), 'start' => $exact['start'], 'end' => $exact['end']];
            }

            if (! $fallback) {
                $fallback = ['date' => $date->format('Y-m-d'), 'start' => $slots->first()['start'], 'end' => $slots->first()['end']];
            }
        }

        return $fallback;
    }

    private function blockedRangesForDate(TrainerProfile $trainer, Carbon $date): Collection
    {
        return $trainer->blockedSlots()->whereDate('block_date', $date->format('Y-m-d'))->get();
    }

    private function bookedSessionsForDate(TrainerProfile $trainer, Carbon $date): Collection
    {
        return $trainer->trialSessions()
            ->whereDate('session_date', $date->format('Y-m-d'))
            ->where('status', '!=', TrialSession::STATUS_CANCELLED)
            ->get();
    }
}
