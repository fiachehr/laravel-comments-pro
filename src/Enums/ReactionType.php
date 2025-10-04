<?php

namespace Fiachehr\Comments\Enums;

enum ReactionType: string
{
    case LIKE = 'like';
    case DISLIKE = 'dislike';

    public function label(): string
    {
        return match ($this) {
            self::LIKE => 'Like',
            self::DISLIKE => 'Dislike',
        };
    }

    public function emoji(): string
    {
        return match ($this) {
            self::LIKE => '👍',
            self::DISLIKE => '👎',
        };
    }

    public static function all(): array
    {
        return self::cases();
    }

    public static function toArray(): array
    {
        return array_combine(
            array_map(fn (self $type) => $type->value, self::all()),
            array_map(fn (self $type) => $type->label(), self::all())
        );
    }
}
