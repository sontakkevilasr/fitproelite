<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Trial;
use App\Models\TrialSession;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        if ($user->hasRole('admin')) {
            return view('dashboard.admin', [
                'totalTrainers' => User::role('trainer')->count(),
                'totalCounsellors' => User::role('counsellor')->count(),
                'totalClients' => Client::count(),
                'trialsScheduled' => Trial::where('status', Trial::STATUS_SCHEDULED)->count(),
                'converted' => Trial::where('status', Trial::STATUS_CONVERTED)->count(),
                'followUps' => Client::where('status', Client::STATUS_FOLLOW_UP)->count(),
            ]);
        }

        if ($user->hasRole('counsellor')) {
            return redirect()->route('counsellor.lookup.index');
        }

        if ($user->hasRole('trainer')) {
            $trainerProfile = $user->trainerProfile;

            return view('dashboard.trainer', [
                'trainerProfile' => $trainerProfile,
                'todaySessions' => $trainerProfile
                    ? TrialSession::where('trainer_profile_id', $trainerProfile->id)->whereDate('session_date', today())->count()
                    : 0,
                'upcomingSessions' => $trainerProfile
                    ? TrialSession::where('trainer_profile_id', $trainerProfile->id)->where('session_date', '>=', today())->where('status', TrialSession::STATUS_SCHEDULED)->count()
                    : 0,
            ]);
        }

        return view('dashboard.admin', [
            'totalTrainers' => 0, 'totalCounsellors' => 0, 'totalClients' => 0,
            'trialsScheduled' => 0, 'converted' => 0, 'followUps' => 0,
        ]);
    }
}
