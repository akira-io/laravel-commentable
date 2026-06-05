<?php

declare(strict_types=1);

namespace Akira\Commentable\Concerns;

use Akira\Commentable\Contracts\CommentContract;
use Akira\Commentable\Exceptions\DeleteCommentNotAllowedException;
use Akira\Commentable\Models\Message;
use Akira\Commentable\Models\Reply;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Gate;

trait Commenter
{
    private const GATE_COMMENT = 'commentable.comment';

    private const GATE_REPLY = 'commentable.reply';

    private const GATE_DELETE = 'commentable.delete';

    private const GATE_FORCE_DELETE = 'commentable.forceDelete';

    private const GATE_APPROVE = 'commentable.approve';

    private const GATE_REJECT = 'commentable.reject';

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
     * @throws AuthorizationException
     */
    public function comment(Model $model, string $comment): CommentContract
    {

        $this->requireCommentableTrait($model);
        $this->authorizeLifecycleAction(self::GATE_COMMENT, 'comment', $model, $comment);

        return $model->comments()->create($this->prepareCommentData($comment));
    }

    /**
     * @phpstan-return CommentContract
     */
    public function reply(Message $comment, string $reply): CommentContract
    {

        $this->authorizeLifecycleAction(self::GATE_REPLY, 'reply', $comment, $reply);

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

        $authorized = $this->allowsLifecycleAction(self::GATE_DELETE, 'delete', $comment);

        if (is_bool($authorized)) {
            return $authorized;
        }

        return $this->getKey() === $comment->commenter_id;
    }

    /**
     * @throws DeleteCommentNotAllowedException
     */
    public function forceDeleteComment(Message $comment): ?bool
    {
        if (! $this->approveForcedCommentDeletion($comment)) {
            throw new DeleteCommentNotAllowedException();
        }

        return $comment->delete();
    }

    /**
     * @phpstan-return bool
     */
    public function approveForcedCommentDeletion(CommentContract $comment): bool
    {

        $authorized = $this->allowsLifecycleAction(self::GATE_FORCE_DELETE, 'forceDelete', $comment);

        if (is_bool($authorized)) {
            return $authorized;
        }

        return $this->approveCommentDeletion($comment);
    }

    /**
     * @phpstan-return bool
     *
     * @throws AuthorizationException
     */
    public function approveComment(Message $comment): bool
    {
        $this->authorizeLifecycleAction(self::GATE_APPROVE, 'approve', $comment);

        return $comment->forceFill(['approved' => true])->save();
    }

    /**
     * @phpstan-return bool
     *
     * @throws AuthorizationException
     */
    public function rejectComment(Message $comment): bool
    {
        $this->authorizeLifecycleAction(self::GATE_REJECT, 'reject', $comment);

        return $comment->forceFill(['approved' => false])->save();
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
     * @phpstan-return void
     *
     * @throws AuthorizationException
     */
    private function authorizeLifecycleAction(string $gateAbility, string $policyAbility, object $subject, mixed ...$arguments): void
    {
        if (Gate::has($gateAbility)) {
            Gate::forUser($this)->authorize($gateAbility, [$subject, ...$arguments]);

            return;
        }

        if ($this->hasPolicyAbility($subject, $policyAbility)) {
            Gate::forUser($this)->authorize($policyAbility, [$subject, ...$arguments]);
        }
    }

    /**
     * @phpstan-return bool|null
     */
    private function allowsLifecycleAction(string $gateAbility, string $policyAbility, object $subject, mixed ...$arguments): ?bool
    {
        if (Gate::has($gateAbility)) {
            return Gate::forUser($this)->allows($gateAbility, [$subject, ...$arguments]);
        }

        if ($this->hasPolicyAbility($subject, $policyAbility)) {
            return Gate::forUser($this)->allows($policyAbility, [$subject, ...$arguments]);
        }

        return null;
    }

    /**
     * @phpstan-return bool
     */
    private function hasPolicyAbility(object $subject, string $ability): bool
    {
        $policy = Gate::getPolicyFor($subject);

        return is_object($policy) && method_exists($policy, $ability);
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
