<?php

namespace Fiachehr\Comments\Models;

use Fiachehr\Comments\Database\Factories\CommentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;

    protected static function newFactory()
    {
        return CommentFactory::new();
    }

    protected $fillable = [
        'commentable_type',
        'commentable_id',
        'user_id',
        'guest_name',
        'guest_email',
        'guest_ip',
        'body',
        'parent_id',
        'status',
        'depth',
    ];

    protected $casts = [
        'depth' => 'integer',
    ];

    public function commentable()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(config('auth.providers.users.model'));
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function reactions()
    {
        return $this->hasMany(Reaction::class);
    }

    public function scopeApproved($q)
    {
        return $q->where('status', 'approved');
    }

    public function scopeWithReactions($query)
    {
        $query->with('reactions')->withCount([
            'reactions as likes' => fn ($q) => $q->where('type', 'like'),
            'reactions as dislikes' => fn ($q) => $q->where('type', 'dislike'),
        ]);

        return $query;
    }
}
