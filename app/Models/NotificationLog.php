<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationLog extends Model
{
    use HasFactory;

    public const RECIPIENT_TRAINER = 'trainer';
    public const RECIPIENT_CLIENT = 'client';

    public const STATUS_LOGGED = 'logged';
    public const STATUS_SENT = 'sent';
    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'recipient_type',
        'recipient_user_id',
        'client_id',
        'phone',
        'channel',
        'message',
        'media_url',
        'profile_link',
        'status',
        'related_trial_id',
    ];

    public function recipientUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_user_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function trial(): BelongsTo
    {
        return $this->belongsTo(Trial::class, 'related_trial_id');
    }
}
