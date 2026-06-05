<?php

declare(strict_types=1);

namespace Akira\Commentable\Models;

use Akira\Commentable\Contracts\CommentContract;
use Akira\Commentable\Events\CommentApproved;
use Akira\Commentable\Events\CommentRejected;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property bool $approved
 */
abstract class Message extends Model implements CommentContract
{
    protected $fillable = [
        'content',
        'commenter_type',
        'commenter_id',
        'approved',
    ];

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function __construct(array $attributes = [])
    {
        $this->table = config('commentable.comment_table', 'comments');

        parent::__construct($attributes);
    }

    /**
     * @param  iterable<array-key, Message>  $comments
     *
     * @phpstan-return int
     */
    final public static function approveMany(iterable $comments): int
    {
        $approvedCount = 0;

        foreach ($comments as $comment) {
            if ($comment->approve()) {
                $approvedCount++;
            }
        }

        return $approvedCount;
    }

    /**
     * @param  iterable<array-key, Message>  $comments
     *
     * @phpstan-return int
     */
    final public static function rejectMany(iterable $comments): int
    {
        $rejectedCount = 0;

        foreach ($comments as $comment) {
            if ($comment->reject()) {
                $rejectedCount++;
            }
        }

        return $rejectedCount;
    }

    /**
     * @return MorphTo<Model, $this>
     */
    final public function commenter(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return HasMany<BaseReaction, $this>
     */
    final public function reactions(): HasMany
    {
        return $this->hasMany(self::configuredReactionModel(), 'comment_id', 'id');

    }

    /**
     * @return HasMany<Reply, $this>
     */
    final public function replies(): HasMany
    {
        return $this->hasMany(Reply::class, 'reply_id', 'id');
    }

    /**
     * @phpstan-return bool
     */
    final public function approve(): bool
    {
        $approved = $this->forceFill(['approved' => true])->save();

        if ($approved) {
            event(new CommentApproved($this));
        }

        return $approved;
    }

    /**
     * @phpstan-return bool
     */
    final public function reject(): bool
    {
        $rejected = $this->markPending();

        if ($rejected) {
            event(new CommentRejected($this));
        }

        return $rejected;
    }

    /**
     * @phpstan-return bool
     */
    final public function markPending(): bool
    {
        return $this->forceFill(['approved' => false])->save();
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    final public function scopeApproved(Builder $query): Builder
    {
        return $query->where('approved', true);
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    final public function scopePending(Builder $query): Builder
    {
        return $query->where('approved', false);
    }

    /**
     * @return class-string<Message>
     */
    final protected static function configuredCommentModel(): string
    {
        $model = config('commentable.models.comment', Comment::class);

        if (is_string($model) && is_a($model, self::class, true)) {
            return $model;
        }

        return Comment::class;
    }

    /**
     * @return class-string<BaseReaction>
     */
    final protected static function configuredReactionModel(): string
    {
        $model = config('commentable.models.reaction', Reaction::class);

        if (is_string($model) && is_a($model, BaseReaction::class, true)) {
            return $model;
        }

        return Reaction::class;
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {

        return [
            'approved' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
