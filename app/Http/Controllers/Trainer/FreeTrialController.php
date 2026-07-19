<?php

namespace App\Http\Controllers\Trainer;

use App\Http\Controllers\Controller;
use App\Models\Trial;
use App\Models\TrialSession;
use App\Services\SlotAvailabilityService;
use App\Services\WhatsAppNotificationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FreeTrialController extends Controller
{
    public function __construct(
        private SlotAvailabilityService $availability,
        private WhatsAppNotificationService $notifications,
    ) {
    }

    /**
     * Every free trial this assessment trainer has personally booked —
     * distinct from "My Sessions", which lists sessions assigned TO a
     * trainer regardless of who booked them.
     */
    public function index()
    {
        $trials = Trial::with('client', 'sessions.trainerProfile.user', 'sessions.category')
            ->where('booked_by_user_id', Auth::id())
            ->where('type', Trial::TYPE_FREE_TRIAL)
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('trainer.free-trials.index', compact('trials'));
    }

    public function show(Trial $trial)
    {
        $this->authorizeTrial($trial);
        $trial->load('client', 'sessions.trainerProfile.user', 'sessions.category');

        return view('trainer.free-trials.show', compact('trial'));
    }

    public function updateSession(Request $request, TrialSession $trialSession)
    {
        $this->authorizeTrial($trialSession->trial);

        $validated = $request->validate([
            'date' => ['required', 'date'],
            'start' => ['required', 'date_format:H:i'],
            'end' => ['required', 'date_format:H:i'],
        ]);

        $trainer = $trialSession->trainerProfile;
        $date = Carbon::parse($validated['date']);
        $unchanged = $trialSession->session_date->format('Y-m-d') === $validated['date']
            && $trialSession->start_time === $validated['start'];

        if (! $unchanged && ! $this->availability->isSlotFree($trainer, $date, $validated['start'])) {
            return back()->withErrors(['session' => 'That slot is no longer available for '.$trainer->user->name.'.']);
        }

        $trialSession->update([
            'session_date' => $validated['date'],
            'start_time' => $validated['start'],
            'end_time' => $validated['end'],
        ]);

        return back()->with('status', 'Session updated.');
    }

    public function resendNotification(Trial $trial)
    {
        $this->authorizeTrial($trial);

        $this->notifications->resendClientNotification($trial);

        return back()->with('status', 'WhatsApp message with the session details was sent to '.$trial->client->name.'.');
    }

    private function authorizeTrial(Trial $trial): void
    {
        abort_unless($trial->type === Trial::TYPE_FREE_TRIAL, 404);
        abort_unless($trial->booked_by_user_id === Auth::id(), 403);
    }
}
