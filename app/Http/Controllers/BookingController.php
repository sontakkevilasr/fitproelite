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
        $trialType = $this->authorize($type, $client);

        $categoryId = $request->integer('category_id');
        $category = TrainerCategory::findOrFail($categoryId);

        $trainers = TrainerProfile::with('user')
            ->whereHas('categories', fn ($q) => $q->where('trainer_categories.id', $categoryId))
            ->where('is_active', true)
            ->get();

        $sessionsNeeded = $trialType === Trial::TYPE_PRE_VISIT ? 1 : $this->sessionsNeededFor($client, $category);

        return view('booking.select-trainer', compact('type', 'client', 'category', 'trainers', 'sessionsNeeded'));
    }

    /**
     * After an assessment is saved, show every open slot across the
     * package's linked categories, as a selectable card — instead of the
     * system pre-deciding who gets which session. The assessment trainer
     * picks whichever combination of cards they want, up to the package's
     * trial-session count; it's fine if that ends up all with one trainer,
     * or spread across several/different categories or times.
     */
    public function plan(Request $request, string $type, Client $client)
    {
        $this->authorize($type, $client);
        abort_unless($type === 'free-trial', 404);

        $from = $request->filled('from') ? Carbon::parse($request->query('from')) : now();
        $to = $request->filled('to') ? Carbon::parse($request->query('to')) : $from->copy()->addDays(7);

        $package = $client->package;
        $maxSessions = $package?->trial_sessions_count ?? 0;
        $categories = $package?->trainerCategories()->orderByPivot('sessions', 'desc')->get() ?? collect();

        $sections = $categories->map(function (TrainerCategory $category) use ($from, $to) {
            $trainers = TrainerProfile::with('user')
                ->whereHas('categories', fn ($q) => $q->where('trainer_categories.id', $category->id))
                ->where('is_active', true)
                ->get();

            $cards = collect();
            foreach ($trainers as $trainer) {
                foreach ($this->availability->freeSlotsInRange($trainer, $from, $to) as $slot) {
                    $cards->push(['category' => $category, 'trainer' => $trainer, ...$slot]);
                }
            }

            $cards = $cards->sortBy(['date', 'start'])->values();

            return ['category' => $category, 'cards' => $cards];
        });

        return view('booking.plan', [
            'type' => $type,
            'client' => $client,
            'package' => $package,
            'sections' => $sections,
            'maxSessions' => $maxSessions,
            'from' => $from->format('Y-m-d'),
            'to' => $to->format('Y-m-d'),
        ]);
    }

    /**
     * Books whichever cards the assessment trainer selected on the
     * suggestions screen — each can carry a different trainer and/or
     * category, capped at the package's trial-session count as a ceiling
     * (not a fixed requirement, so booking fewer now is fine).
     */
    public function bookPlan(Request $request, string $type, Client $client)
    {
        $this->authorize($type, $client);
        abort_unless($type === 'free-trial', 404);

        $maxSessions = $client->package?->trial_sessions_count ?? 0;

        $validated = $request->validate([
            'sessions' => ['required', 'array', 'min:1', 'max:'.max($maxSessions, 1)],
            'sessions.*.trainer_profile_id' => ['required', 'exists:trainer_profiles,id'],
            'sessions.*.category_id' => ['required', 'exists:trainer_categories,id'],
            'sessions.*.date' => ['required', 'date'],
            'sessions.*.start' => ['required', 'date_format:H:i'],
            'sessions.*.end' => ['required', 'date_format:H:i'],
        ]);

        $packageCategoryIds = $client->package?->trainerCategories()->pluck('trainer_categories.id') ?? collect();

        foreach ($validated['sessions'] as $slot) {
            abort_unless($packageCategoryIds->contains($slot['category_id']), 404);

            $belongs = TrainerProfile::whereKey($slot['trainer_profile_id'])
                ->whereHas('categories', fn ($q) => $q->where('trainer_categories.id', $slot['category_id']))
                ->exists();

            abort_unless($belongs, 404);
        }

        $primaryCategory = TrainerCategory::findOrFail($validated['sessions'][0]['category_id']);

        try {
            $trial = $this->booking->bookTrial(
                client: $client,
                category: $primaryCategory,
                bookedBy: Auth::user(),
                type: Trial::TYPE_FREE_TRIAL,
                sessionSlots: $validated['sessions'],
                expectedSessions: count($validated['sessions']),
            );
        } catch (SlotUnavailableException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('trainer.free-trials.show', $trial)
            ->with('status', 'Free trial booked successfully for '.$client->name.'.');
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
            ->whereHas('categories', fn ($q) => $q->where('trainer_categories.id', $validated['category_id']))
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

    public function calendar(Request $request, string $type, Client $client, TrainerProfile $trainerProfile)
    {
        $trialType = $this->authorize($type, $client);
        $trainerProfile->load('user', 'categories');

        $categoryId = null;
        $sessionsNeeded = 1;
        if ($trialType === Trial::TYPE_FREE_TRIAL) {
            $categoryId = $request->integer('category_id');
            $category = $trainerProfile->categories->firstWhere('id', $categoryId);
            abort_unless($category, 404);
            $sessionsNeeded = $this->sessionsNeededFor($client, $category);
        }

        return view('booking.calendar', [
            'type' => $type,
            'client' => $client,
            'trainer' => $trainerProfile,
            'categoryId' => $categoryId,
            'sessionsNeeded' => $sessionsNeeded,
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
            'category_id' => ['required', 'exists:trainer_categories,id'],
        ]);

        $category = TrainerCategory::findOrFail($validated['category_id']);
        $sessionsNeeded = $this->sessionsNeededFor($client, $category);

        $sessions = [];
        $afterDate = Carbon::parse($validated['date']);
        $preferredStart = $validated['start'];

        for ($i = 1; $i < $sessionsNeeded; $i++) {
            $next = $this->availability->nextFreeSlot($trainerProfile, $afterDate, $preferredStart);

            if (! $next) {
                break;
            }

            $sessions[] = $next;
            $afterDate = Carbon::parse($next['date']);
        }

        return response()->json(['sessions' => $sessions]);
    }

    public function store(Request $request, string $type, Client $client, TrainerProfile $trainerProfile)
    {
        $trialType = $this->authorize($type, $client);

        if ($trialType === Trial::TYPE_PRE_VISIT) {
            $category = TrainerCategory::where('is_assessment_category', true)->firstOrFail();
            $expected = 1;
        } else {
            $categoryId = $request->integer('category_id');
            abort_unless($trainerProfile->categories->contains('id', $categoryId), 404);
            $category = TrainerCategory::findOrFail($categoryId);
            $expected = $this->sessionsNeededFor($client, $category);
        }

        $validated = $request->validate([
            'sessions' => ['required', 'array', 'size:'.$expected],
            'sessions.*.date' => ['required', 'date'],
            'sessions.*.start' => ['required', 'date_format:H:i'],
            'sessions.*.end' => ['required', 'date_format:H:i'],
        ]);

        $sessionSlots = collect($validated['sessions'])
            ->map(fn (array $slot) => [...$slot, 'trainer_profile_id' => $trainerProfile->id])
            ->all();

        try {
            $trial = $this->booking->bookTrial(
                client: $client,
                category: $category,
                bookedBy: Auth::user(),
                type: $trialType,
                sessionSlots: $sessionSlots,
                expectedSessions: $expected,
            );
        } catch (SlotUnavailableException $e) {
            return back()->withErrors(['sessions' => $e->getMessage()]);
        }

        if ($trialType === Trial::TYPE_PRE_VISIT) {
            return redirect()->route('counsellor.clients.show', $client)
                ->with('status', 'Pre-trial visit booked successfully.');
        }

        return redirect()->route('trainer.free-trials.show', $trial)
            ->with('status', 'Free trial booked successfully for '.$client->name.'.');
    }

    /**
     * How many free trial sessions a given category should get for this
     * client, per their package's trial-session split. Falls back to 3 (the
     * old fixed default) when the client has no package, or the package has
     * no split covering this category — e.g. an assessment trainer browsing
     * a category manually that isn't part of the recommended package.
     */
    private function sessionsNeededFor(Client $client, TrainerCategory $category): int
    {
        $row = $client->package?->trialSessionPlan()->first(fn (array $row) => $row['category']->id === $category->id);

        return $row['sessions'] ?? 3;
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
            abort_unless($user->hasRole('trainer') && $user->trainerProfile?->isAssessmentTrainer(), 403);

            return Trial::TYPE_FREE_TRIAL;
        }

        abort(404);
    }
}
