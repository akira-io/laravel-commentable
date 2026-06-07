<?php

declare(strict_types=1);

namespace Akira\Commentable\Concerns;

use Akira\Commentable\Models\Message;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait Commentable
{
    /**
     * @return MorphMany<Message, Commentable>
     */
    public function comments(): MorphMany
    {
        return $this->morphMany(config('commentable.models.comment'), 'commentable');
    }

    /**
     * @return MorphMany<Message, Commentable>
     */
    public function commentsWithThread(): MorphMany
    {
        return $this->comments()->withThread()->withReactionCounts();
    }
}
