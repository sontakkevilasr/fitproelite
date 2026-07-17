<?php

namespace App\Http\Controllers;

use App\Exceptions\SlotUnavailableException;
use App\Models\Client;
use App\Models\TrainerCategory;
use App\Models\TrainerProfile;
use App\Models\Trial;
use App\Services\SlotAvailabilityService;
use App\Services\TrialBookingService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookingController extends Controller
{
    public function __construct(
        private SlotAvailabilityService $availability,
        private TrialBookingService $booking,
    ) {
    }

    public function selectCategory(string $type, Client $client)
    {
        $trialType = $this->authorize($type, $client);

        if ($trialType === Trial::TYPE_PRE_VISIT) {
            $category = TrainerCategory::where('is_assessment_category', true)->where('is_active', true)->first();
            abort_unless($category, 404, 'No assessment category configured.');

            return redirect()->route('booking.trainers', ['type' => $type, 'client' => $client, 'category_id' => $category->id]);
        }

        $categories = TrainerCategory::where('is_assessment_category', false)->where('is_active', true)->orderBy('name')->get();
        $recommendedId = $client->assessment?->recommended_category_id;

        return view('booking.select-category', compact('type', 'client', 'categories', 'recommendedId'));
    }

    public function selectTrainer(Request $request, string $type, Client $client)
    {
        $this->authorize($type, $client);

        $categoryId = $request->integer('category_id');
        $category = TrainerCategory::findOrFail($categoryId);

        $trainers = TrainerProfile::with('user')
            ->where('trainer_category_id', $categoryId)
            ->where('is_active', true)
            ->get();

        return view('booking.select-trainer', compact('type', 'client', 'category', 'trainers'));
    }

    /**
     * "Caller asked for Wednesday afternoon" — search every trainer in this
     * category at once and return the nearest free slots, ranked by how
     * close they land to the requested day/time. Built for speed on a live
     * call: no need to open each trainer's calendar one by one.
     */
    public function quickSuggest(Request $request, string $type, Client $client)
    {
        $this->authorize($type, $client);

        $validated = $request->validate([
            'category_id' => ['required', 'exists:trainer_categories,id'],
            'date' => ['required', 'date'],
            'time' => ['nullable', 'date_format:H:i'],
        ]);

        $trainers = TrainerProfile::with('user')
            ->where('trainer_category_id', $validated['category_id'])
            ->where('is_active', true)
            ->get();

        $suggestions = $this->availability->nearestSlotsAcrossTrainers(
            trainers: $trainers,
            preferredDate: Carbon::parse($validated['date']),
            preferredTime: $validated['time'] ?? null,
        );

        return response()->json([
            'suggestions' => collect($suggestions)->map(fn ($s) => [
                'trainer_id' => $s['trainer']->id,
                'trainer_name' => $s['trainer']->user->name,
                'trainer_photo' => $s['trainer']->photoUrl(),
                'date' => $s['date'],
                'date_label' => Carbon::parse($s['date'])->isToday() ? 'Today' : (Carbon::parse($s['date'])->isTomorrow() ? 'Tomorrow' : Carbon::parse($s['date'])->format('D, d M')),
                'start' => $s['start'],
                'end' => $s['end'],
                'start_label' => Carbon::parse($s['start'])->format('g:i A'),
            ])->values(),
        ]);
    }

    public function calendar(string $type, Client $client, TrainerProfile $trainerProfile)
    {
        $this->authorize($type, $client);
        $trainerProfile->load('user', 'category');

        return view('booking.calendar', [
            'type' => $type,
            'client' => $client,
            'trainer' => $trainerProfile,
            'sessionsNeeded' => $type === 'pre-visit' ? 1 : 3,
        ]);
    }

    public function slots(Request $request, string $type, Client $client, TrainerProfile $trainerProfile)
    {
        $this->authorize($type, $client);

        $from = Carbon::parse($request->query('start', now()));
        $to = Carbon::parse($request->query('end', now()->addWeeks(2)));

        return response()->json($this->availability->getEvents($trainerProfile, $from, $to));
    }

    public function suggestSessions(Request $request, string $type, Client $client, TrainerProfile $trainerProfile)
    {
        $this->authorize($type, $client);
        abort_unless($type === 'free-trial', 404);

        $validated = $request->validate([
            'date' => ['required', 'date'],
            'start' => ['required', 'date_format:H:i'],
        ]);

        $session1Date = Carbon::parse($validated['date']);
        $session2 = $this->availability->nextFreeSlot($trainerProfile, $session1Date, $validated['start']);

        $session3 = $session2
            ? $this->availability->nextFreeSlot($trainerProfile, Carbon::parse($session2['date']), $validated['start'])
            : null;

        return response()->json(['session2' => $session2, 'session3' => $session3]);
    }

    public function store(Request $request, string $type, Client $client, TrainerProfile $trainerProfile)
    {
        $trialType = $this->authorize($type, $client);
        $expected = $trialType === Trial::TYPE_PRE_VISIT ? 1 : 3;

        $validated = $request->validate([
            'sessions' => ['required', 'array', 'size:'.$expected],
            'sessions.*.date' => ['required', 'date'],
            'sessions.*.start' => ['required', 'date_format:H:i'],
            'sessions.*.end' => ['required', 'date_format:H:i'],
        ]);

        try {
            $trial = $this->booking->bookTrial(
                client: $client,
                trainer: $trainerProfile,
                category: $trainerProfile->category,
                bookedBy: Auth::user(),
                type: $trialType,
                sessionSlots: $validated['sessions'],
            );
        } catch (SlotUnavailableException $e) {
            return back()->withErrors(['sessions' => $e->getMessage()]);
        }

        if ($trialType === Trial::TYPE_PRE_VISIT) {
            return redirect()->route('counsellor.clients.show', $client)
                ->with('status', 'Pre-trial visit booked successfully.');
        }

        return redirect()->route('trainer.calendar.index')
            ->with('status', 'Free trial booked successfully for '.$client->name.'.');
    }

    /**
     * Counsellors and assessment trainers are trusted internal staff sharing
     * one client pool (any of them may need to pick up someone else's lead
     * for a follow-up call or booking) — so this only gates by role/type,
     * not by who created the client or filled in their assessment.
     */
    private function authorize(string $type, Client $client): string
    {
        $user = Auth::user();

        if ($type === 'pre-visit') {
            abort_unless($user->hasRole('counsellor'), 403);

            return Trial::TYPE_PRE_VISIT;
        }

        if ($type === 'free-trial') {
            abort_unless($user->hasRole('trainer') && $user->trainerProfile?->category?->is_assessment_category, 403);

            return Trial::TYPE_FREE_TRIAL;
        }

        abort(404);
    }
}
