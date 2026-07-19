<?php

namespace App\Services;

use App\Models\NotificationLog;
use App\Models\Trial;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class WhatsAppNotificationService
{
    public function notifyPreVisitScheduled(Trial $trial): void
    {
        $this->notifyTrainerAndClient($trial, 'Pre-Trial Visit');
    }

    public function notifyTrialScheduled(Trial $trial): void
    {
        $this->notifyTrainerAndClient($trial, 'Free Trial');
    }

    /**
     * Re-send just the client-facing WhatsApp message (schedule + trainer
     * photo/bio link) — e.g. after the assessment trainer edits a session's
     * time and wants the client to have the updated details, without also
     * re-notifying every trainer involved.
     */
    public function resendClientNotification(Trial $trial, string $label = 'Free Trial'): void
    {
        $trial->loadMissing('client', 'trainerProfile.user', 'sessions.trainerProfile.user');

        $this->notifyClient($trial, $label);
    }

    private function notifyTrainerAndClient(Trial $trial, string $label): void
    {
        $trial->loadMissing('client', 'trainerProfile.user', 'sessions.trainerProfile.user');

        $client = $trial->client;
        $sessions = $trial->sessions;

        // Sessions can be split across more than one trainer in the category
        // (each session independently follows whoever had the nearest free
        // slot), so each trainer only hears about their own session(s).
        foreach ($sessions->groupBy('trainer_profile_id') as $group) {
            $trainer = $group->first()->trainerProfile;

            $lines = $group->map(
                fn ($session) => $session->session_date->format('D, d M Y').' at '.Carbon::parse($session->start_time)->format('g:i A')
            )->implode("\n");

            $this->log(
                recipientType: NotificationLog::RECIPIENT_TRAINER,
                recipientUserId: $trainer->user_id,
                client: $client,
                phone: $trainer->phone ?? $trainer->user->phone,
                message: "New {$label} booked.\nClient: {$client->name} ({$client->phone})\n{$lines}",
                trial: $trial,
            );
        }

        $this->notifyClient($trial, $label);
    }

    private function notifyClient(Trial $trial, string $label): void
    {
        $client = $trial->client;
        $sessions = $trial->sessions;
        $primaryTrainer = $trial->trainerProfile;

        $clientScheduleLines = $sessions->map(
            fn ($session) => $session->session_date->format('D, d M Y').' at '.Carbon::parse($session->start_time)->format('g:i A').' with '.$session->trainerProfile->user->name
        )->implode("\n");

        $trainerNames = $sessions->pluck('trainerProfile.user.name')->unique()->implode(' & ');
        $profileLink = route('trainers.public-profile', $primaryTrainer);
        $photoUrl = $primaryTrainer->photoUrl();

        $this->log(
            recipientType: NotificationLog::RECIPIENT_CLIENT,
            recipientUserId: null,
            client: $client,
            phone: $client->phone,
            message: "Your {$label} with {$trainerNames} is confirmed.\n{$clientScheduleLines}\n\nMeet your trainer: {$profileLink}",
            trial: $trial,
            mediaUrl: $photoUrl,
            profileLink: $profileLink,
        );
    }

    private function log(
        string $recipientType,
        ?int $recipientUserId,
        $client,
        ?string $phone,
        string $message,
        Trial $trial,
        ?string $mediaUrl = null,
        ?string $profileLink = null,
    ): void {
        $log = NotificationLog::create([
            'recipient_type' => $recipientType,
            'recipient_user_id' => $recipientUserId,
            'client_id' => $client->id,
            'phone' => $phone,
            'channel' => 'whatsapp',
            'message' => $message,
            'media_url' => $mediaUrl,
            'profile_link' => $profileLink,
            'status' => NotificationLog::STATUS_LOGGED,
            'related_trial_id' => $trial->id,
        ]);

        Log::info('WhatsApp notification logged', ['notification_log_id' => $log->id, 'to' => $phone]);
    }
}
