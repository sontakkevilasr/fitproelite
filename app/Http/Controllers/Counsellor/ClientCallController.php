<?php

namespace App\Http\Controllers\Counsellor;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\ClientCall;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ClientCallController extends Controller
{
    public function store(Request $request, Client $client)
    {
        $validated = $request->validate([
            'notes' => ['nullable', 'string'],
            'outcome' => ['required', Rule::in([
                ClientCall::OUTCOME_INTERESTED,
                ClientCall::OUTCOME_NOT_INTERESTED,
                ClientCall::OUTCOME_FOLLOW_UP_LATER,
                ClientCall::OUTCOME_NO_ANSWER,
                ClientCall::OUTCOME_CONVERTED,
            ])],
            'next_follow_up_at' => ['nullable', 'date', 'after_or_equal:today'],
        ]);

        ClientCall::create([
            'client_id' => $client->id,
            'counsellor_id' => Auth::id(),
            'call_date' => now(),
            'notes' => $validated['notes'] ?? null,
            'outcome' => $validated['outcome'],
            'next_follow_up_at' => $validated['next_follow_up_at'] ?? null,
        ]);

        $statusMap = [
            ClientCall::OUTCOME_FOLLOW_UP_LATER => Client::STATUS_FOLLOW_UP,
            ClientCall::OUTCOME_NOT_INTERESTED => Client::STATUS_LOST,
            ClientCall::OUTCOME_CONVERTED => Client::STATUS_CONVERTED,
        ];

        if (isset($statusMap[$validated['outcome']])) {
            $client->update([
                'status' => $statusMap[$validated['outcome']],
                'next_follow_up_at' => $validated['next_follow_up_at'] ?? null,
            ]);
        }

        return back()->with('status', 'Call logged.');
    }
}
