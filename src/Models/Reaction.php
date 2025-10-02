<?php

namespace Fiachehr\Comments\Models;

use Fiachehr\Comments\Enums\ReactionType;
use Fiachehr\Comments\Database\Factories\ReactionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'comment_id',
        'user_id',
        'guest_fingerprint',
        'type'
    ];

    protected $casts = [
        'type' => ReactionType::class,
    ];

    public function comment(): BelongsTo
    {
        return $this->belongsTo(Comment::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'));
    }

    public function isGuest(): bool
    {
        return !is_null($this->guest_fingerprint);
    }

    public function isAuthenticated(): bool
    {
        return !is_null($this->user_id);
    }

    protected static function newFactory()
    {
        return ReactionFactory::new();
    }
}
