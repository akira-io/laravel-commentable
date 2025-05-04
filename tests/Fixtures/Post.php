<?php

declare(strict_types=1);

namespace Akira\Commentable\Tests\Fixtures;

use Akira\Commentable\Concerns\Commentable;
use Akira\Commentable\Contracts\CommentableContract;
use Illuminate\Database\Eloquent\Model;

final class Post extends Model implements CommentableContract
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
