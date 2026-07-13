<?php

namespace App\Http\Controllers\Counsellor;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\InterestLevel;
use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LookupController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $stats = [
            'myClients' => Client::where('created_by', $user->id)->count(),
            'followUpsDue' => Client::where('created_by', $user->id)
                ->where('status', Client::STATUS_FOLLOW_UP)
                ->where('next_follow_up_at', '<=', now())
                ->count(),
        ];

        $packages = Package::where('is_active', true)->orderBy('name')->get();
        $interestLevels = InterestLevel::where('is_active', true)->orderBy('sort_order')->get();

        return view('counsellor.lookup.index', compact('stats', 'packages', 'interestLevels'));
    }

    public function search(Request $request)
    {
        $phone = trim((string) $request->query('phone'));

        if ($phone === '') {
            return response()->json(['found' => false]);
        }

        $client = Client::where('phone', 'like', "%{$phone}%")->orderByDesc('created_at')->first();

        if (! $client) {
            return response()->json(['found' => false]);
        }

        return response()->json([
            'found' => true,
            'client' => ['id' => $client->id, 'name' => $client->name, 'phone' => $client->phone],
            'redirect' => route('counsellor.clients.show', $client),
        ]);
    }
}
