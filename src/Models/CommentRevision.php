<?php

declare(strict_types=1);

namespace Akira\Commentable\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

final class CommentRevision extends Model
{
    protected $guarded = [];

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function __construct(array $attributes = [])
    {
        $this->table = $this->revisionTable();

        parent::__construct($attributes);
    }

    /**
     * @return BelongsTo<Message, $this>
     */
    public function comment(): BelongsTo
    {
        return $this->belongsTo($this->configuredCommentModel(), 'comment_id', 'id');
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function editor(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
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

    /**
     * @phpstan-return string
     */
    private function revisionTable(): string
    {
        $table = config('commentable.revision_table', 'comment_revisions');

        return is_string($table) ? $table : 'comment_revisions';
    }
}
