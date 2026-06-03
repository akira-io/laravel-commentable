<?php

declare(strict_types=1);

namespace Akira\Commentable\Models;

use Akira\Commentable\Contracts\CommentContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

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
