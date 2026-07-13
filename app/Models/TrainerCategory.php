<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function trainerProfiles(): HasMany
    {
        return $this->hasMany(TrainerProfile::class);
    }
}
