<?php

namespace App\Http\Controllers\Counsellor;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Support\Facades\Auth;

class FollowUpController extends Controller
{
    public function index()
    {
        $clients = Client::where('created_by', Auth::id())
            ->where('status', Client::STATUS_FOLLOW_UP)
            ->orderBy('next_follow_up_at')
            ->paginate(15);

        return view('counsellor.follow-ups.index', compact('clients'));
    }
}
