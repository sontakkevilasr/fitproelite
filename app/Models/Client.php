<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Client extends Model
{
    use HasFactory;

    public const STATUS_NEW = 'new';
    public const STATUS_PRE_VISIT_SCHEDULED = 'pre_visit_scheduled';
    public const STATUS_ASSESSMENT_COMPLETED = 'assessment_completed';
    public const STATUS_TRIAL_SCHEDULED = 'trial_scheduled';
    public const STATUS_CONVERTED = 'converted';
    public const STATUS_FOLLOW_UP = 'follow_up';
    public const STATUS_LOST = 'lost';

    protected $fillable = [
        'name',
        'phone',
        'email',
        'address',
        'package_id',
        'interest_level_id',
        'status',
        'next_follow_up_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'next_follow_up_at' => 'date',
        ];
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function interestLevel(): BelongsTo
    {
        return $this->belongsTo(InterestLevel::class);
    }

    public function counsellor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function calls(): HasMany
    {
        return $this->hasMany(ClientCall::class)->latest('call_date');
    }

    public function trials(): HasMany
    {
        return $this->hasMany(Trial::class);
    }

    public function assessment(): HasOne
    {
        return $this->hasOne(ClientAssessment::class);
    }
}
