<?php

namespace App\Http\Controllers\Counsellor;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\ClientCall;
use App\Models\InterestLevel;
use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $clients = Client::with('package', 'interestLevel')
            ->where('created_by', Auth::id())
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->string('search');
                $q->where(fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('phone', 'like', "%{$search}%"));
            })
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        return view('counsellor.clients.index', compact('clients'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'package_id' => ['nullable', 'exists:packages,id'],
            'interest_level_id' => ['nullable', 'exists:interest_levels,id'],
            'notes' => ['nullable', 'string'],
        ]);

        $client = DB::transaction(function () use ($validated) {
            $client = Client::create([
                'name' => $validated['name'],
                'phone' => $validated['phone'],
                'email' => $validated['email'] ?? null,
                'address' => $validated['address'] ?? null,
                'package_id' => $validated['package_id'] ?? null,
                'interest_level_id' => $validated['interest_level_id'] ?? null,
                'status' => Client::STATUS_NEW,
                'created_by' => Auth::id(),
            ]);

            ClientCall::create([
                'client_id' => $client->id,
                'counsellor_id' => Auth::id(),
                'call_date' => now(),
                'notes' => $validated['notes'] ?? 'Initial enquiry.',
                'outcome' => ClientCall::OUTCOME_INTERESTED,
            ]);

            return $client;
        });

        return redirect()->route('counsellor.clients.show', $client)->with('status', 'New enquiry captured.');
    }

    public function show(Client $client)
    {
        $client->load('package', 'interestLevel', 'calls.counsellor', 'trials.sessions', 'trials.trainerProfile.user', 'assessment.recommendedCategory');

        return view('counsellor.clients.show', compact('client'));
    }
}
