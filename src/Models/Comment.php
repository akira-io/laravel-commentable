<?php

declare(strict_types=1);

namespace Akira\Commentable\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\MorphTo;

final class Comment extends Message
{
    protected $fillable = [
        'content',
        'commenter_type',
        'commenter_id',
        'approved',
    ];

    /**
     * Get the user that the comment belongs to.
     *
     * @return MorphTo<Model, $this>
     */
    public function commentable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the reactions for the comment.
     *
     * @return HasManyThrough<Model, $this>
     */
    public function replyReactions(): HasManyThrough
    {
        return $this->hasManyThrough(Reaction::class, Reply::class, 'reply_id', 'comment_id');
    }

    /**
     * The attributes that should be cast to native types.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {

        return [
            'approved' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
