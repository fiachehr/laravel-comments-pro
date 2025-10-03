<?php

namespace Fiachehr\Comments\Facades;

use Fiachehr\Comments\Enums\ReactionType;
use Fiachehr\Comments\Models\Comment;
use Fiachehr\Comments\Models\Reaction;
use Illuminate\Support\Facades\Facade;

class Reactions extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'comments.reactions.service';
    }

    /**
     * Toggle a reaction on a comment
     */
    public static function toggle(Comment $comment, ReactionType $type, ?string $guestFingerprint = null, ?int $userId = null): Reaction
    {
        return static::getFacadeRoot()->toggleReaction($comment, $type, $guestFingerprint, $userId);
    }

    /**
     * Remove a reaction
     */
    public static function remove(Reaction $reaction): Reaction
    {
        return static::getFacadeRoot()->removeReaction($reaction);
    }

    /**
     * Get reaction statistics for a comment
     */
    public static function getStats(Comment $comment): array
    {
        return static::getFacadeRoot()->getReactionStats($comment);
    }

    /**
     * Bulk toggle reactions on multiple comments
     */
    public static function bulkToggle(array $commentIds, ReactionType $type, ?string $guestFingerprint = null): array
    {
        return static::getFacadeRoot()->bulkToggleReactions($commentIds, $type, $guestFingerprint);
    }

    /**
     * Get popular comments based on reactions
     */
    public static function getPopular(int $limit = 10, string $period = '7 days')
    {
        return static::getFacadeRoot()->getPopularComments($limit, $period);
    }
}
