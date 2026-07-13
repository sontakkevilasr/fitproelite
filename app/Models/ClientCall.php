<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientCall extends Model
{
    use HasFactory;

    public const OUTCOME_INTERESTED = 'interested';
    public const OUTCOME_NOT_INTERESTED = 'not_interested';
    public const OUTCOME_FOLLOW_UP_LATER = 'follow_up_later';
    public const OUTCOME_NO_ANSWER = 'no_answer';
    public const OUTCOME_CONVERTED = 'converted';

    protected $fillable = [
        'client_id',
        'counsellor_id',
        'call_date',
        'notes',
        'outcome',
        'next_follow_up_at',
    ];

    protected function casts(): array
    {
        return [
            'call_date' => 'datetime',
            'next_follow_up_at' => 'date',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function counsellor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'counsellor_id');
    }
}
