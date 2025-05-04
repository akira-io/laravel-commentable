<?php

declare(strict_types=1);

namespace Akira\Commentable\Concerns;

use Akira\Commentable\Exceptions\DeleteCommentNotAllowedException;
use Akira\Commentable\Models\Comment;
use Akira\Commentable\Models\Reply;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait Commenter
{
    /**
     * Get the comments for the model.
     *
     * @return MorphMany<Comment, Commenter>
     */
    public function comments(): MorphMany
    {

        return $this->morphMany(Comment::class, 'commenter');
    }

    /**
     * Get the replies for the model.
     *
     * @return HasMany<Reply, Commenter>
     */
    public function replies(): HasMany
    {

        return $this->hasMany(Reply::class);
    }

    /**
     * The name of the commenter.
     *
     * @throws Exception
     */
    public function comment(Model $model, string $comment): Comment
    {

        $this->requireCommentableTrait($model);

        return $model->comments()->create($this->prepareCommentData($comment));
    }

    /**
     * Reply to a comment
     */
    public function reply(Comment|Reply $comment, string $reply): Reply
    {

        return $comment->replies()->create($this->prepareCommentData($reply));
    }

    /**
     * Delete a comment
     *
     * @throws DeleteCommentNotAllowedException
     */
    public function deleteComment(Comment|Reply $comment): void
    {

        if (! $this->approveCommentDeletion($comment)) {
            throw new DeleteCommentNotAllowedException();
        }

        $comment->delete();
    }

    /**
     * Approve comment deletion
     */
    public function approveCommentDeletion(Comment|Reply $comment): bool
    {

        return $this->getKey() === $comment->commenter_id;
    }

    /**
     * force delete a comment
     *
     * @throws Exception
     */
    public function forceDeleteComment(Comment|Reply $comment): ?bool
    {

        return $comment->delete();
    }

    /**
     * Force delete a comment
     *
     * @throws Exception
     */
    private function requireCommentableTrait(Model $model): void
    {

        if (! in_array(Commentable::class, class_uses($model))) {
            throw new Exception('The model must use the Commentable trait');
        }
    }

    /**
     * Prepare the comment data
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
