<?php

namespace Fiachehr\Comments\Enums;

enum CommentStatusType: string
{
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case SPAM = 'spam';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::APPROVED => 'Approved',
            self::SPAM => 'Spam',
        };
    }

    public static function all(): array
    {
        return self::cases();
    }

    public static function toArray(): array
    {
        return array_combine(array_map(fn(self $status) => $status->value, self::all()), array_map(fn(self $status) => $status->label(), self::all()));
    }
}
