<?php

namespace App\Services;

use App\Models\NotificationLog;
use App\Models\Trial;
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

    private function notifyTrainerAndClient(Trial $trial, string $label): void
    {
        $trial->loadMissing('client', 'trainerProfile.user', 'sessions');

        $trainer = $trial->trainerProfile;
        $client = $trial->client;
        $sessions = $trial->sessions;

        $scheduleLines = $sessions->map(
            fn ($session) => $session->session_date->format('D, d M Y').' at '.\Carbon\Carbon::parse($session->start_time)->format('g:i A')
        )->implode("\n");

        $profileLink = route('trainers.public-profile', $trainer);
        $photoUrl = $trainer->photoUrl();

        $this->log(
            recipientType: NotificationLog::RECIPIENT_TRAINER,
            recipientUserId: $trainer->user_id,
            client: $client,
            phone: $trainer->phone ?? $trainer->user->phone,
            message: "New {$label} booked.\nClient: {$client->name} ({$client->phone})\n{$scheduleLines}",
            trial: $trial,
        );

        $this->log(
            recipientType: NotificationLog::RECIPIENT_CLIENT,
            recipientUserId: null,
            client: $client,
            phone: $client->phone,
            message: "Your {$label} with {$trainer->user->name} is confirmed.\n{$scheduleLines}\n\nMeet your trainer: {$profileLink}",
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
