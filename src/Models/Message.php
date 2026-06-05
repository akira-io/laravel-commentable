<?php

declare(strict_types=1);

namespace Akira\Commentable\Models;

use Akira\Commentable\Contracts\CommentContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Schema;
use Throwable;

/**
 * @property string $content
 */
abstract class Message extends Model implements CommentContract
{
    use SoftDeletes {
        bootSoftDeletes as bootEloquentSoftDeletes;
        performDeleteOnModel as performSoftDeleteOnModel;
        restore as restoreSoftDeletedModel;
    }

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
        $this->table = self::commentTable();

        parent::__construct($attributes);
    }

    /**
     * @phpstan-return void
     */
    final public static function bootSoftDeletes(): void
    {
        if (self::supportsSoftDeleteColumn()) {
            static::bootEloquentSoftDeletes();
        }
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
     * @return HasMany<CommentRevision, $this>
     */
    final public function revisions(): HasMany
    {
        return $this->hasMany(CommentRevision::class, 'comment_id', 'id');
    }

    /**
     * @phpstan-return bool
     */
    final public function edit(string $content, ?Model $editor = null, ?string $reason = null): bool
    {
        return (bool) $this->getConnection()->transaction(function () use ($content, $editor, $reason): bool {
            $previousContent = $this->content;
            $saved = $this->forceFill(['content' => $content])->save();

            if (! $saved) {
                return false;
            }

            $this->revisions()->create($this->prepareRevisionData($previousContent, $content, $editor, $reason));

            return true;
        });
    }

    /**
     * @phpstan-return bool
     */
    final public function restore(): bool
    {
        if (! $this->supportsSoftDeletes()) {
            return false;
        }

        return (bool) $this->restoreSoftDeletedModel();
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
     * @phpstan-return mixed
     */
    protected function performDeleteOnModel(): mixed
    {
        if ($this->supportsSoftDeletes()) {
            return $this->performSoftDeleteOnModel();
        }

        parent::performDeleteOnModel();

        return null;
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {

        return [
            'approved' => 'boolean',
            'deleted_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * @phpstan-return bool
     */
    private static function supportsSoftDeleteColumn(): bool
    {
        try {
            return Schema::hasColumn(self::commentTable(), 'deleted_at');
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * @phpstan-return string
     */
    private static function commentTable(): string
    {
        $table = config('commentable.comment_table', 'comments');

        return is_string($table) ? $table : 'comments';
    }

    /**
     * @phpstan-return bool
     */
    private function supportsSoftDeletes(): bool
    {
        return self::supportsSoftDeleteColumn();
    }

    /**
     * @return array<string, mixed>
     */
    private function prepareRevisionData(string $previousContent, string $newContent, ?Model $editor, ?string $reason): array
    {
        $revisionData = [
            'previous_content' => $previousContent,
            'new_content' => $newContent,
            'reason' => $reason,
        ];

        if ($editor instanceof Model) {
            $revisionData['editor_type'] = $editor::class;
            $revisionData['editor_id'] = $editor->getKey();
        }

        return $revisionData;
    }
}
