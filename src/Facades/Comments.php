<?php

namespace Fiachehr\Comments\Facades;

use Fiachehr\Comments\Models\Comment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;

class Comments extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'comments.service';
    }

    /**
     * Create a new comment
     */
    public static function create(array $data, Model $commentable): Comment
    {
        return static::getFacadeRoot()->createComment($data, $commentable);
    }

    /**
     * Approve a comment
     */
    public static function approve(Comment $comment): Comment
    {
        return static::getFacadeRoot()->approveComment($comment);
    }

    /**
     * Convert comments to tree structure
     */
    public static function toTree(Collection $comments): array
    {
        return static::getFacadeRoot()->toTree($comments);
    }
}
