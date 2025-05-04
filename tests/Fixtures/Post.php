<?php

declare(strict_types=1);

namespace Akira\Commentable\Tests\Fixtures;

use Akira\Commentable\Concerns\Commentable;
use Illuminate\Database\Eloquent\Model;

final class Post extends Model
{
    use Commentable;

    protected $guarded = [];

    /**
     * Indicates if the comment requires approval.
     */
    public function commentCanBeCreated(\Illuminate\Foundation\Auth\User $user): bool
    {
        return false;
    }
}
