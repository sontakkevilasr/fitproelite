<?php

namespace App\Http\Controllers\Trainer;

use App\Http\Controllers\Controller;
use App\Models\TrainerBlockedSlot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BlockedSlotController extends Controller
{
    public function index()
    {
        $trainer = Auth::user()->trainerProfile;
        $blockedSlots = $trainer
            ? $trainer->blockedSlots()->orderBy('block_date', 'desc')->get()
            : collect();

        return view('trainer.blocked-slots.index', compact('blockedSlots'));
    }

    public function store(Request $request)
    {
        $trainer = Auth::user()->trainerProfile;
        abort_unless($trainer, 403);

        $validated = $request->validate([
            'block_date' => ['required', 'date', 'after_or_equal:today'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i', 'after:start_time'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        $trainer->blockedSlots()->create($validated);

        return back()->with('status', 'Blocked date added.');
    }

    public function destroy(TrainerBlockedSlot $blockedSlot)
    {
        abort_unless($blockedSlot->trainer_profile_id === Auth::user()->trainerProfile?->id, 403);

        $blockedSlot->delete();

        return back()->with('status', 'Blocked date removed.');
    }
}
