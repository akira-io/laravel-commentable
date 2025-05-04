<?php

declare(strict_types=1);

namespace Akira\Commentable\Contracts;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

interface CommentableContract
{
    public function comments(): MorphMany;

    public function replies(): HasMany;
}
