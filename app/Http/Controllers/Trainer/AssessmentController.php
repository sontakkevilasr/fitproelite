<?php

namespace App\Http\Controllers\Trainer;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\ClientAssessment;
use App\Models\TrainerCategory;
use App\Models\Trial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AssessmentController extends Controller
{
    public function create(Trial $trial)
    {
        $this->authorize($trial);

        $categories = TrainerCategory::where('is_assessment_category', false)->where('is_active', true)->orderBy('name')->get();

        return view('trainer.assessments.create', [
            'trial' => $trial,
            'client' => $trial->client,
            'categories' => $categories,
            'objectives' => ClientAssessment::OBJECTIVES,
        ]);
    }

    public function store(Request $request, Trial $trial)
    {
        $this->authorize($trial);

        $validated = $request->validate([
            'first_time_gym' => ['sometimes', 'boolean'],
            'workout_objective' => ['required', 'string', 'in:'.implode(',', array_keys(ClientAssessment::OBJECTIVES))],
            'medical_conditions' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'recommended_category_id' => ['required', 'exists:trainer_categories,id'],
        ]);

        $validated['first_time_gym'] = $request->boolean('first_time_gym');

        ClientAssessment::create([
            ...$validated,
            'client_id' => $trial->client_id,
            'trial_id' => $trial->id,
            'filled_by' => Auth::id(),
        ]);

        $trial->client->update(['status' => Client::STATUS_ASSESSMENT_COMPLETED]);

        return redirect()->route('booking.category', ['type' => 'free-trial', 'client' => $trial->client])
            ->with('status', 'Assessment saved. Now book the free trial with the right trainer.');
    }

    private function authorize(Trial $trial): void
    {
        abort_unless($trial->trainer_profile_id === Auth::user()->trainerProfile?->id, 403);
        abort_unless($trial->type === Trial::TYPE_PRE_VISIT, 404);

        if ($trial->assessment) {
            abort(404, 'Assessment already submitted for this visit.');
        }
    }
}
