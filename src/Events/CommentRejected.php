<?php

declare(strict_types=1);

namespace Akira\Commentable\Events;

use Akira\Commentable\Models\Message;
use Illuminate\Queue\SerializesModels;

final class CommentRejected
{
    use SerializesModels;

    /**
     * @phpstan-return void
     */
    public function __construct(public Message $comment) {}
}
