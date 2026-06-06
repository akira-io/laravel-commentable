<?php

declare(strict_types=1);

namespace Akira\Commentable\Tests\Fixtures;

final class PostPolicy
{
    public function comment(User $user, Post $post, string $comment): bool
    {
        return str_starts_with($comment, 'allowed');
    }
}
