<?php

declare(strict_types=1);

namespace Akira\Commentable\Tests\Fixtures;

use Akira\Commentable\Concerns\Commenter;
use Akira\Commentable\Contracts\CommentContract;
use Illuminate\Database\Eloquent\Model;

final class ModeratorUser extends Model
{
    use Commenter;

    protected $table = 'users';

    protected $guarded = [];

    public function approveForcedCommentDeletion(CommentContract $comment): bool
    {
        return true;
    }
}
