<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class Package extends Model
{
    use HasFactory;

    /**
     * A package's total sessions may not exceed its weekly training days
     * times this many weeks (a 4-week month).
     */
    public const WEEKS_PER_PACKAGE = 4;

    public const WEEK_DAYS_OPTIONS = [3, 6];

    protected $fillable = [
        'name',
        'description',
        'price',
        'week_days',
        'sessions_count',
        'trial_sessions_count',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function clients(): HasMany
    {
        return $this->hasMany(Client::class);
    }

    public function trainerCategories(): BelongsToMany
    {
        return $this->belongsToMany(TrainerCategory::class, 'package_trainer_category')
            ->withPivot('sessions');
    }

    public function maxSessions(): int
    {
        return $this->week_days * self::WEEKS_PER_PACKAGE;
    }

    /**
     * Split this package's free trial sessions across its linked trainer
     * types, so the client samples the variety of trainers the full package
     * includes rather than just one. Categories with a bigger share of the
     * full program (higher pivot `sessions`) are prioritized: they get a
     * trial slot first, and absorb the remainder when the split isn't even.
     *
     * @return Collection<int, array{category: TrainerCategory, sessions: int}>
     */
    public function trialSessionPlan(): Collection
    {
        $categories = $this->trainerCategories()->orderByPivot('sessions', 'desc')->get();
        $totalTrialSessions = $this->trial_sessions_count ?? 0;
        $categoryCount = $categories->count();

        if ($categoryCount === 0 || $totalTrialSessions <= 0) {
            return collect();
        }

        if ($totalTrialSessions < $categoryCount) {
            return $categories->take($totalTrialSessions)->values()
                ->map(fn (TrainerCategory $category) => ['category' => $category, 'sessions' => 1]);
        }

        $base = intdiv($totalTrialSessions, $categoryCount);
        $remainder = $totalTrialSessions % $categoryCount;

        return $categories->values()->map(fn (TrainerCategory $category, int $i) => [
            'category' => $category,
            'sessions' => $base + ($i < $remainder ? 1 : 0),
        ]);
    }
}
