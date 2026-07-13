<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientAssessment extends Model
{
    use HasFactory;

    public const OBJECTIVES = [
        'weight_loss' => 'Weight Loss',
        'muscle_gain' => 'Muscle Gain',
        'general_fitness' => 'General Fitness',
        'flexibility' => 'Flexibility',
        'rehab' => 'Rehabilitation',
        'other' => 'Other',
    ];

    protected $fillable = [
        'client_id',
        'trial_id',
        'first_time_gym',
        'workout_objective',
        'medical_conditions',
        'notes',
        'recommended_category_id',
        'filled_by',
    ];

    protected function casts(): array
    {
        return [
            'first_time_gym' => 'boolean',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function trial(): BelongsTo
    {
        return $this->belongsTo(Trial::class);
    }

    public function recommendedCategory(): BelongsTo
    {
        return $this->belongsTo(TrainerCategory::class, 'recommended_category_id');
    }

    public function filledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'filled_by');
    }
}
