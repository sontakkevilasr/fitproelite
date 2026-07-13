<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Trial extends Model
{
    use HasFactory;

    public const TYPE_PRE_VISIT = 'pre_visit';
    public const TYPE_FREE_TRIAL = 'free_trial';

    public const STATUS_SCHEDULED = 'scheduled';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CONVERTED = 'converted';
    public const STATUS_LOST = 'lost';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'client_id',
        'trainer_profile_id',
        'counsellor_id',
        'booked_by_user_id',
        'trainer_category_id',
        'type',
        'total_sessions',
        'status',
        'outcome_notes',
        'decided_at',
    ];

    protected function casts(): array
    {
        return [
            'decided_at' => 'datetime',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function trainerProfile(): BelongsTo
    {
        return $this->belongsTo(TrainerProfile::class);
    }

    public function counsellor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'counsellor_id');
    }

    public function bookedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'booked_by_user_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(TrainerCategory::class, 'trainer_category_id');
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(TrialSession::class)->orderBy('session_number');
    }

    public function assessment(): HasOne
    {
        return $this->hasOne(ClientAssessment::class);
    }
}
