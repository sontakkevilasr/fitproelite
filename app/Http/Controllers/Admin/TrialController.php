<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Trial;
use Illuminate\Http\Request;

class TrialController extends Controller
{
    public function index(Request $request)
    {
        $trials = Trial::with('client', 'sessions.trainerProfile.user', 'sessions.category', 'counsellor', 'bookedBy')
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->string('type')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.trials.index', compact('trials'));
    }

    public function show(Trial $trial)
    {
        $trial->load('client', 'sessions.trainerProfile.user', 'sessions.category', 'counsellor', 'bookedBy', 'assessment');

        return view('admin.trials.show', compact('trial'));
    }
}
