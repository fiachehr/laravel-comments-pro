<?php

namespace Fiachehr\Comments\Services;

use Fiachehr\Comments\Enums\ReactionType;
use Fiachehr\Comments\Events\ReactionToggled;
use Fiachehr\Comments\Helper\GuestFingerprint;
use Fiachehr\Comments\Models\Comment;
use Fiachehr\Comments\Models\Reaction;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReactionService
{
    public function toggleReaction(Comment $comment, ReactionType $type, ?string $guestFingerprint = null, ?int $userId = null): Reaction
    {
        if ($comment->status !== 'approved') {
            throw ValidationException::withMessages(['comment' => 'Cannot react to unapproved comment.']);
        }

        if (! $userId && ! $guestFingerprint && config('comments.guests.allowed')) {
            $guestFingerprint = GuestFingerprint::getOrCreate();
        }

        if (! $userId && $guestFingerprint && ! GuestFingerprint::validate($guestFingerprint)) {
            throw ValidationException::withMessages(['fingerprint' => 'Invalid guest fingerprint format.']);
        }

        if (! $userId && ! $guestFingerprint) {
            throw ValidationException::withMessages(['user' => 'User must be authenticated or provide guest fingerprint.']);
        }

        $existingReaction = $this->findExistingReaction($comment, $userId, $guestFingerprint);

        if ($existingReaction) {
            if ($existingReaction->type === $type->value) {
                return $this->removeReaction($existingReaction);
            }

            $existingReaction->update(['type' => $type->value]);
            $existingReaction->refresh();
            event(new ReactionToggled($existingReaction));

            return $existingReaction;
        }

        $reaction = $comment->reactions()->create([
            'user_id' => $userId,
            'guest_fingerprint' => $guestFingerprint,
            'type' => $type->value,
        ]);

        event(new ReactionToggled($reaction));

        return $reaction;
    }

    public function removeReaction(Reaction $reaction): Reaction
    {
        $reaction->delete();

        return $reaction;
    }

    public function getReactionStats(Comment $comment): array
    {
        $reactions = $comment->reactions()->selectRaw('type, COUNT(*) as count')
            ->groupBy('type')
            ->pluck('count', 'type')
            ->toArray();

        return [
            'likes' => $reactions['like'] ?? 0,
            'dislikes' => $reactions['dislike'] ?? 0,
            'total' => ($reactions['like'] ?? 0) + ($reactions['dislike'] ?? 0),
        ];
    }

    public function getReactionStatsForComments(Collection $comments): array
    {
        if ($comments->isEmpty()) {
            return [];
        }

        $commentIds = $comments->pluck('id');

        $reactions = Reaction::whereIn('comment_id', $commentIds)
            ->selectRaw('comment_id, type, COUNT(*) as count')
            ->groupBy('comment_id', 'type')
            ->get()
            ->groupBy('comment_id');

        $stats = [];
        foreach ($commentIds as $commentId) {
            $commentReactions = $reactions->get($commentId, collect());
            $stats[$commentId] = [
                'likes' => $commentReactions->where('type', 'like')->first()?->count ?? 0,
                'dislikes' => $commentReactions->where('type', 'dislike')->first()?->count ?? 0,
            ];
            $stats[$commentId]['total'] = $stats[$commentId]['likes'] + $stats[$commentId]['dislikes'];
        }

        return $stats;
    }

    public function getUserReaction(Comment $comment, ?string $guestFingerprint = null): ?array
    {
        $userId = Auth::id();
        $reaction = $this->findExistingReaction($comment, $userId, $guestFingerprint);

        if (! $reaction) {
            return null;
        }

        return [
            'type' => $reaction->type,
            'created_at' => $reaction->created_at,
        ];
    }

    public function getCommentReactions(Comment $comment, int $limit = 50, int $offset = 0): Collection
    {
        return $comment->reactions()
            ->with('user:id,name,email')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->offset($offset)
            ->get();
    }

    public function getReactionsByType(Comment $comment): array
    {
        $reactions = $comment->reactions()
            ->with('user:id,name,email')
            ->get()
            ->groupBy('type');

        return [
            'like' => $reactions->get('like', collect()),
            'dislike' => $reactions->get('dislike', collect()),
        ];
    }

    public function bulkToggleReactions(array $commentIds, ReactionType $type, ?string $guestFingerprint = null): array
    {
        $results = [];

        foreach ($commentIds as $commentId) {
            try {
                $comment = Comment::findOrFail($commentId);
                $reaction = $this->toggleReaction($comment, $type, $guestFingerprint);
                $results[$commentId] = [
                    'success' => true,
                    'reaction' => $reaction,
                ];
            } catch (\Exception $e) {
                $results[$commentId] = [
                    'success' => false,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }

    private function findExistingReaction(Comment $comment, ?int $userId, ?string $guestFingerprint): ?Reaction
    {
        $query = $comment->reactions();

        if ($userId) {
            $query->where('user_id', $userId);
        } elseif ($guestFingerprint) {
            $query->where('guest_fingerprint', $guestFingerprint);
        } else {
            return null;
        }

        return $query->first();
    }

    public function getPopularComments(int $limit = 10, string $period = '7 days'): Collection
    {
        return Comment::approved()
            ->select('comments.*')
            ->selectRaw('COUNT(reactions.id) as reaction_count')
            ->leftJoin('reactions', 'comments.id', '=', 'reactions.comment_id')
            ->where('comments.created_at', '>=', now()->sub($period))
            ->groupBy('comments.id')
            ->orderBy('reaction_count', 'desc')
            ->orderBy('comments.created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public function cleanupOrphanedReactions(): int
    {
        return Reaction::whereNotExists(function ($query) {
            $query->select(DB::raw(1))
                ->from('comments')
                ->whereRaw('comments.id = reactions.comment_id');
        })->delete();
    }
}
