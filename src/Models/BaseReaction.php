<?php

declare(strict_types=1);

namespace Akira\Commentable\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

abstract class BaseReaction extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'comment_id',
        'type',
        'owner_id',
        'owner_type',
    ];

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function __construct(array $attributes = [])
    {
        $table = config('commentable.reaction_table', 'reactions');
        $this->table = is_string($table) ? $table : 'reactions';

        parent::__construct($attributes);
    }

    /**
     * @return BelongsTo<Model, $this> *
     */
    final public function user(): BelongsTo
    {
        return $this->belongsTo($this->configuredUserModel(), 'owner_id', 'id');
    }

    /**
     * @return MorphTo<Model, $this>
     */
    final public function owner(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return BelongsTo<Message, $this>
     */
    final public function comment(): BelongsTo
    {
        return $this->belongsTo($this->configuredCommentModel(), 'comment_id', 'id');
    }

    /**
     * @return class-string<Model>
     */
    private function configuredUserModel(): string
    {
        $model = config('auth.providers.users.model');

        if (is_string($model) && is_a($model, Model::class, true)) {
            return $model;
        }

        return Model::class;
    }

    /**
     * @return class-string<Message>
     */
    private function configuredCommentModel(): string
    {
        $model = config('commentable.models.comment', Comment::class);

        if (is_string($model) && is_a($model, Message::class, true)) {
            return $model;
        }

        return Comment::class;
    }
}
