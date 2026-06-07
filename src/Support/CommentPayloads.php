<?php

declare(strict_types=1);

namespace Akira\Commentable\Support;

use Akira\Commentable\Exceptions\ReplyDepthExceededException;
use Akira\Commentable\Models\Message;
use Akira\Commentable\Models\Reply;
use Illuminate\Database\Eloquent\Model;

final class CommentPayloads
{
    /**
     * @return array<string, mixed>
     */
    public static function commentData(Model $commenter, string $comment): array
    {

        return [
            'content' => $comment,
            'commenter_type' => $commenter::class,
            'commenter_id' => $commenter->getKey(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function replyData(Model $commenter, Message $comment, string $reply): array
    {
        [$commentableType, $commentableId] = self::commentableContextFor($comment);

        return [
            ...self::commentData($commenter, $reply),
            'commentable_type' => $commentableType,
            'commentable_id' => $commentableId,
            'thread_depth' => self::nextReplyDepth($comment),
        ];
    }

    /**
     * @phpstan-return void
     *
     * @throws ReplyDepthExceededException
     */
    public static function guardReplyDepth(Message $comment): void
    {
        $maxDepth = self::configuredMaxReplyDepth();

        if ($maxDepth === null) {
            return;
        }

        if (self::nextReplyDepth($comment) > $maxDepth) {
            throw new ReplyDepthExceededException($maxDepth);
        }
    }

    /**
     * @phpstan-return int<0, max>|null
     */
    private static function configuredMaxReplyDepth(): ?int
    {
        $maxDepth = config('commentable.max_reply_depth');

        if (! is_int($maxDepth)) {
            return null;
        }

        if ($maxDepth < 0) {
            return null;
        }

        return $maxDepth;
    }

    /**
     * @phpstan-return int
     */
    private static function nextReplyDepth(Message $comment): int
    {
        $threadDepth = $comment->getAttribute('thread_depth');

        if (is_int($threadDepth)) {
            return $threadDepth + 1;
        }

        if (is_string($threadDepth) && is_numeric($threadDepth)) {
            return (int) $threadDepth + 1;
        }

        return 1;
    }

    /**
     * @return array{0: mixed, 1: mixed}
     */
    private static function commentableContextFor(Message $comment): array
    {
        $commentableType = $comment->getAttribute('commentable_type');
        $commentableId = $comment->getAttribute('commentable_id');

        if ($commentableType !== null && $commentableId !== null) {
            return [$commentableType, $commentableId];
        }

        $ancestor = $comment;

        while ($ancestor instanceof Reply) {
            $ancestor = $ancestor->comment()->first();

            if (! $ancestor instanceof Message) {
                break;
            }

            $commentableType = $ancestor->getAttribute('commentable_type');
            $commentableId = $ancestor->getAttribute('commentable_id');

            if ($commentableType !== null && $commentableId !== null) {
                return [$commentableType, $commentableId];
            }
        }

        return [null, null];
    }
}
