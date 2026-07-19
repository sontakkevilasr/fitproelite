<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TrainerCategory;
use App\Models\TrainerProfile;
use Illuminate\Http\Request;

class TrainerController extends Controller
{
    public function index(Request $request)
    {
        $trainers = TrainerProfile::with('user', 'categories')
            ->when($request->filled('category_id'), fn ($q) => $q->whereHas('categories', fn ($q) => $q->where('trainer_categories.id', $request->integer('category_id'))))
            ->whereHas('user')
            ->get()
            ->sortBy(fn ($trainer) => $trainer->user->name);

        $categories = TrainerCategory::orderBy('name')->get();

        return view('admin.trainers.index', compact('trainers', 'categories'));
    }

    public function show(TrainerProfile $trainer)
    {
        $trainer->load('user', 'categories', 'weeklySlots', 'blockedSlots');

        return view('admin.trainers.show', compact('trainer'));
    }
}
