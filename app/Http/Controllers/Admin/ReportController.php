<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Trial;
use App\Models\TrialSession;

class ReportController extends Controller
{
    public function index()
    {
        $totalFreeTrials = Trial::where('type', Trial::TYPE_FREE_TRIAL)->count();
        $convertedTrials = Trial::where('type', Trial::TYPE_FREE_TRIAL)->where('status', Trial::STATUS_CONVERTED)->count();
        $lostTrials = Trial::where('type', Trial::TYPE_FREE_TRIAL)->where('status', Trial::STATUS_LOST)->count();

        $stats = [
            'preVisitsScheduled' => Trial::where('type', Trial::TYPE_PRE_VISIT)->count(),
            'freeTrialsScheduled' => $totalFreeTrials,
            'sessionsCompleted' => TrialSession::where('status', TrialSession::STATUS_COMPLETED)->count(),
            'sessionsNoShow' => TrialSession::where('status', TrialSession::STATUS_NO_SHOW)->count(),
            'converted' => $convertedTrials,
            'lost' => $lostTrials,
            'conversionRate' => $totalFreeTrials > 0 ? round(($convertedTrials / $totalFreeTrials) * 100, 1) : 0,
            'followUps' => Client::where('status', Client::STATUS_FOLLOW_UP)->count(),
        ];

        $byCategory = Trial::where('type', Trial::TYPE_FREE_TRIAL)
            ->with('category')
            ->get()
            ->groupBy(fn ($trial) => $trial->category->name ?? 'Unknown')
            ->map(fn ($trials) => [
                'total' => $trials->count(),
                'converted' => $trials->where('status', Trial::STATUS_CONVERTED)->count(),
            ]);

        return view('admin.reports.index', compact('stats', 'byCategory'));
    }
}
