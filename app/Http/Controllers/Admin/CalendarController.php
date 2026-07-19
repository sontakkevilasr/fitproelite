<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TrainerCategory;
use App\Models\TrainerProfile;
use App\Services\SlotAvailabilityService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    public function index(Request $request)
    {
        $categories = TrainerCategory::orderBy('name')->get();
        $trainers = TrainerProfile::with('user', 'categories')
            ->when($request->filled('category_id'), fn ($q) => $q->whereHas('categories', fn ($q) => $q->where('trainer_categories.id', $request->integer('category_id'))))
            ->get()
            ->sortBy(fn ($trainer) => $trainer->user->name);

        $selectedTrainer = $request->filled('trainer_id')
            ? TrainerProfile::find($request->integer('trainer_id'))
            : $trainers->first();

        return view('admin.calendar.index', compact('categories', 'trainers', 'selectedTrainer'));
    }

    public function events(Request $request, SlotAvailabilityService $availability)
    {
        $trainer = TrainerProfile::findOrFail($request->integer('trainer_id'));

        $from = Carbon::parse($request->query('start', now()));
        $to = Carbon::parse($request->query('end', now()->addWeek()));

        return response()->json($availability->getEvents($trainer, $from, $to));
    }
}
