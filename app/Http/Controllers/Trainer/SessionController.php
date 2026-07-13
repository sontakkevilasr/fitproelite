<?php

namespace App\Http\Controllers\Trainer;

use App\Http\Controllers\Controller;
use App\Models\Trial;
use App\Models\TrialSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class SessionController extends Controller
{
    public function index()
    {
        $trainer = Auth::user()->trainerProfile;
        abort_unless($trainer, 403);

        $sessions = TrialSession::with('trial.client')
            ->where('trainer_profile_id', $trainer->id)
            ->orderBy('session_date')
            ->orderBy('start_time')
            ->paginate(20);

        return view('trainer.sessions.index', compact('sessions'));
    }

    public function updateStatus(Request $request, TrialSession $trialSession)
    {
        abort_unless($trialSession->trainer_profile_id === Auth::user()->trainerProfile?->id, 403);

        $validated = $request->validate([
            'status' => ['required', Rule::in([TrialSession::STATUS_COMPLETED, TrialSession::STATUS_NO_SHOW, TrialSession::STATUS_CANCELLED])],
            'notes' => ['nullable', 'string'],
        ]);

        $trialSession->update([
            'status' => $validated['status'],
            'notes' => $validated['notes'] ?? $trialSession->notes,
            'marked_by' => Auth::id(),
        ]);

        $trial = $trialSession->trial;

        if ($trial->type === Trial::TYPE_PRE_VISIT && $validated['status'] === TrialSession::STATUS_COMPLETED && ! $trial->assessment) {
            return redirect()->route('trainer.assessments.create', $trial)
                ->with('status', 'Visit marked complete. Please fill the health assessment.');
        }

        return back()->with('status', 'Session updated.');
    }
}
