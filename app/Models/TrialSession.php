<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrialSession extends Model
{
    use HasFactory;

    public const STATUS_SCHEDULED = 'scheduled';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_NO_SHOW = 'no_show';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'trial_id',
        'trainer_profile_id',
        'trainer_category_id',
        'session_number',
        'session_date',
        'start_time',
        'end_time',
        'status',
        'notes',
        'marked_by',
    ];

    protected function casts(): array
    {
        return [
            'session_date' => 'date',
        ];
    }

    public function trial(): BelongsTo
    {
        return $this->belongsTo(Trial::class);
    }

    public function trainerProfile(): BelongsTo
    {
        return $this->belongsTo(TrainerProfile::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(TrainerCategory::class, 'trainer_category_id');
    }

    public function markedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'marked_by');
    }
}
