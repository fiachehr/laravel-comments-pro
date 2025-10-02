<?php

namespace Fiachehr\Comments\Traits;

use Fiachehr\Comments\Models\Comment;
use Fiachehr\Comments\Models\Reaction;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasComments
{

    public function commentable(): MorphTo
    {
        return $this->morphTo('commentable');
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Comment::class, 'parent_id');
    }

    public function reactions(): HasMany
    {
        return $this->hasMany(Reaction::class, 'comment_id');
    }

    public function detachReaction($guestFingerprint)
    {
        $this->reactions()->where('guest_fingerprint', $guestFingerprint)->delete();
    }
}
