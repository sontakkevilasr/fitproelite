<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class TrainerCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_assessment_category',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_assessment_category' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function trainerProfiles(): BelongsToMany
    {
        return $this->belongsToMany(TrainerProfile::class, 'trainer_category_trainer_profile');
    }

    public function packages(): BelongsToMany
    {
        return $this->belongsToMany(Package::class, 'package_trainer_category');
    }
}
