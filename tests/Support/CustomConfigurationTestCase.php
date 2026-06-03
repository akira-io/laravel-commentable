<?php

declare(strict_types=1);

namespace Akira\Commentable\Tests\Support;

use Akira\Commentable\Tests\Fixtures\CustomComment;
use Akira\Commentable\Tests\Fixtures\CustomReaction;
use Akira\Commentable\Tests\TestCase;

abstract class CustomConfigurationTestCase extends TestCase
{
    protected function defineEnvironment($app): void
    {
        config()->set('commentable.comment_table', 'custom_comments');
        config()->set('commentable.reaction_table', 'custom_reactions');
        config()->set('commentable.models.comment', CustomComment::class);
        config()->set('commentable.models.reaction', CustomReaction::class);
    }
}
