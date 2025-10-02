<?php

namespace Fiachehr\Comments\Services;


use Fiachehr\Comments\Enums\CommentStatusType;
use Fiachehr\Comments\Events\CommentCreated;
use Fiachehr\Comments\Models\Comment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class CommentsService
{

    public function createComment(array $data, Model $commentable): Comment
    {
        $depth = 0;

        $body = trim((string)($data['body'] ?? ''));
        if ($body === '') {
            throw ValidationException::withMessages(['body' => 'The body field is required.']);
        }

        $parentId  = $data['parent_id'] ?? null;
        $maxDepth  = (int) config('comments.max_depth', 5);

        $autoApproveAuthenticated = (bool) config('comments.auto_approve_authenticated', false);
        $requireApprovedParent    = (bool) config('comments.reply_only_to_approved_parent', true);

        $status = $autoApproveAuthenticated && Auth::check()
            ? CommentStatusType::APPROVED->value
            : CommentStatusType::PENDING->value;

        $guestAllowed = (bool) config('comments.guests.allowed', true);
        $requireEmail = (bool) config('comments.guests.require_email', true);

        if (!$guestAllowed && !Auth::check()) {
            throw ValidationException::withMessages(['guest' => 'Guests are not allowed to comment.']);
        }

        if ($requireEmail && !Auth::check() && !isset($data['guest_email'])) {
            throw ValidationException::withMessages(['guest_email' => 'Email is required for guest comments.']);
        }

        $guestFields = [];
        if (!Auth::check()) {
            $guestFields = [
                'guest_name' => $data['guest_name'] ?? null,
                'guest_email' => $data['guest_email'] ?? null,
                'guest_ip' => request()->ip(),
            ];
        }

        if ($parentId) {
            $parent = $commentable->comments()->approved()
                ->select('id', 'status', 'depth')
                ->whereKey($parentId)
                ->first();

            if (!$parent) {
                throw ValidationException::withMessages(['parent_id' => 'Invalid parent comment.']);
            }

            if ($requireApprovedParent && $parent->status !== CommentStatusType::APPROVED->value) {
                throw ValidationException::withMessages(['parent_id' => 'Parent comment is not approved.']);
            }

            $depth = (int) $parent->depth + 1;

            if ($maxDepth > 0 && $depth > $maxDepth) {
                throw ValidationException::withMessages(['parent_id' => 'Max depth reached.']);
            }
        }

        $comment = $commentable->comments()->create([
            'user_id'    => Auth::id(),
            'body'       => $body,
            'parent_id'  => $parentId,
            'status'     => $status,
            'depth'      => $depth,
        ] + $guestFields);

        event(new CommentCreated($comment));

        return $comment;
    }

    public function approveComment(Comment $comment): Comment
    {
        $comment->updateOrFail(['status' => CommentStatusType::APPROVED->value]);
        $comment->refresh();
        return $comment;
    }

    public function toTree(Collection $comments): array
    {
        $commentsByParent = $comments->groupBy('parent_id');

        $buildTree = function ($parentId = null) use (&$buildTree, $commentsByParent): array {
            $levelComments = $commentsByParent->get($parentId, collect());

            return $levelComments->sortBy('created_at')->map(function (Comment $comment) use (&$buildTree) {
                return [
                    'id'         => $comment->id,
                    'user'       => $comment->user ?? $comment->guest_name,
                    'body'       => $comment->body,
                    'created_at' => $comment->created_at?->toDateTimeString(),
                    'depth'      => $comment->depth,
                    'likes'      => $comment->likes,
                    'dislikes'   => $comment->dislikes,
                    'children'   => $buildTree($comment->id),
                ];
            })->values()->toArray();
        };

        return $buildTree();
    }
}
