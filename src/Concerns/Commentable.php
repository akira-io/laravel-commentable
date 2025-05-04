<?php

declare(strict_types=1);

namespace Akira\Commentable\Concerns;

use Akira\Commentable\Models\Comment;
use Akira\Commentable\Models\Reply;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait Commentable
{
    /**
     * Get the comments for the model.
     */
    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    /**
     * Get the user  commenting the comment.
     *
     * @return HasMany<Model, $this>
     */
    public function replies(): HasMany
    {
        return $this->hasMany(Reply::class, 'reply_id', 'id');
    }
}
