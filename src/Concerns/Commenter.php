<?php

declare(strict_types=1);

namespace Akira\Commentable\Concerns;

use Akira\Commentable\Contracts\CommentContract;
use Akira\Commentable\Exceptions\DeleteCommentNotAllowedException;
use Akira\Commentable\Models\Message;
use Akira\Commentable\Models\Reply;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait Commenter
{
    /**
     * @return MorphMany<Message, Commenter>
     */
    public function comments(): MorphMany
    {

        return $this->morphMany(config('commentable.models.comment'), 'commenter');
    }

    /**
     * @return HasMany<Reply, Commenter>
     */
    public function replies(): HasMany
    {

        return $this->hasMany(Reply::class);
    }

    /**
     * @throws Exception
     */
    public function comment(Model $model, string $comment): CommentContract
    {

        $this->requireCommentableTrait($model);

        return $model->comments()->create($this->prepareCommentData($comment));
    }

    /**
     * @phpstan-return CommentContract
     */
    public function reply(Message $comment, string $reply): CommentContract
    {

        return $comment->replies()->create($this->prepareCommentData($reply));
    }

    /**
     * @throws DeleteCommentNotAllowedException
     */
    public function deleteComment(CommentContract $comment): void
    {

        if (! $this->approveCommentDeletion($comment)) {
            throw new DeleteCommentNotAllowedException();
        }

        $comment->delete();
    }

    /**
     * @phpstan-return bool
     */
    public function approveCommentDeletion(CommentContract $comment): bool
    {

        return $this->getKey() === $comment->commenter_id;
    }

    /**
     * @throws Exception
     */
    public function forceDeleteComment(Message $comment): ?bool
    {

        return $comment->delete();
    }

    /**
     * @throws Exception
     */
    private function requireCommentableTrait(Model $model): void
    {

        if (! in_array(Commentable::class, class_uses($model))) {
            throw new Exception('The model must use the Commentable trait');
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function prepareCommentData(string $comment): array
    {

        return [
            'content' => $comment,
            'commenter_type' => $this::class,
            'commenter_id' => $this->getKey(),
        ];
    }
}
