<?php

namespace App\Http\Controllers\Trainer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AvailabilityController extends Controller
{
    public function index()
    {
        $trainer = Auth::user()->trainerProfile;
        $weeklySlots = $trainer ? $trainer->weeklySlots()->orderBy('day_of_week')->orderBy('start_time')->get() : collect();

        return view('trainer.availability.index', compact('trainer', 'weeklySlots'));
    }

    public function update(Request $request)
    {
        $trainer = Auth::user()->trainerProfile;
        abort_unless($trainer, 403);

        $validated = $request->validate([
            'slots' => ['array'],
            'slots.*.day_of_week' => ['required', 'integer', 'between:0,6'],
            'slots.*.start_time' => ['required', 'date_format:H:i'],
            'slots.*.end_time' => ['required', 'date_format:H:i', 'after:slots.*.start_time'],
        ]);

        DB::transaction(function () use ($trainer, $validated) {
            $trainer->weeklySlots()->delete();

            foreach ($validated['slots'] ?? [] as $slot) {
                $trainer->weeklySlots()->create($slot);
            }
        });

        return back()->with('status', 'Weekly availability updated.');
    }
}
