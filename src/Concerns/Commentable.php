<?php

declare(strict_types=1);

namespace Akira\Commentable\Concerns;

use Illuminate\Database\Eloquent\Relations\MorphMany;

trait Commentable
{
    /**
     * Get the comments for the model.
     */
    public function comments(): MorphMany
    {
        return $this->morphMany(config('commentable.models.comment'), 'commentable');
    }
}
