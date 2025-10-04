<?php

namespace Fiachehr\Comments\Events;

use Fiachehr\Comments\Models\Comment;

class CommentCreated
{
    public function __construct(public Comment $comment) {}
}
