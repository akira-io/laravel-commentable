<?php

declare(strict_types=1);

namespace Akira\Commentable\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

final class Reaction extends Model
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
     * @return BelongsTo<Model, $this> *
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo($this->configuredUserModel(), 'owner_id', 'id');
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function owner(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return BelongsTo<Comment, $this>
     */
    public function comment(): BelongsTo
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
     * @return class-string<Comment>
     */
    private function configuredCommentModel(): string
    {
        $model = config('commentable.models.comment', Comment::class);

        if (is_string($model) && is_a($model, Comment::class, true)) {
            return $model;
        }

        return Comment::class;
    }
}
