<?php

namespace App\Http\Controllers\Counsellor;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Trial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class TrialController extends Controller
{
    public function index(Request $request)
    {
        $trials = Trial::with('client', 'sessions.trainerProfile.user')
            ->where('counsellor_id', Auth::id())
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->string('type')))
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        return view('counsellor.trials.index', compact('trials'));
    }

    public function updateOutcome(Request $request, Trial $trial)
    {
        abort_unless($trial->counsellor_id === Auth::id(), 403);

        $validated = $request->validate([
            'status' => ['required', Rule::in([Trial::STATUS_CONVERTED, Trial::STATUS_LOST])],
            'outcome_notes' => ['nullable', 'string'],
        ]);

        $trial->update([
            'status' => $validated['status'],
            'outcome_notes' => $validated['outcome_notes'] ?? null,
            'decided_at' => now(),
        ]);

        $trial->client->update([
            'status' => $validated['status'] === Trial::STATUS_CONVERTED ? Client::STATUS_CONVERTED : Client::STATUS_LOST,
        ]);

        return back()->with('status', 'Trial outcome recorded.');
    }
}
