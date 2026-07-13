<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TrainerProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'trainer_category_id',
        'photo_path',
        'bio',
        'phone',
        'session_duration_minutes',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(TrainerCategory::class, 'trainer_category_id');
    }

    public function weeklySlots(): HasMany
    {
        return $this->hasMany(TrainerWeeklySlot::class);
    }

    public function blockedSlots(): HasMany
    {
        return $this->hasMany(TrainerBlockedSlot::class);
    }

    public function trialSessions(): HasMany
    {
        return $this->hasMany(TrialSession::class);
    }

    public function trials(): HasMany
    {
        return $this->hasMany(Trial::class);
    }

    public function photoUrl(): ?string
    {
        return $this->photo_path ? asset('storage/'.$this->photo_path) : null;
    }
}
