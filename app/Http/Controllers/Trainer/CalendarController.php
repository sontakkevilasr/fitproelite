<?php

namespace App\Http\Controllers\Trainer;

use App\Http\Controllers\Controller;
use App\Services\SlotAvailabilityService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CalendarController extends Controller
{
    public function index()
    {
        abort_unless(Auth::user()->trainerProfile, 403);

        return view('trainer.calendar.index');
    }

    public function events(Request $request, SlotAvailabilityService $availability)
    {
        $trainer = Auth::user()->trainerProfile;
        abort_unless($trainer, 403);

        $from = Carbon::parse($request->query('start', now()));
        $to = Carbon::parse($request->query('end', now()->addWeek()));

        return response()->json($availability->getEvents($trainer, $from, $to));
    }
}
