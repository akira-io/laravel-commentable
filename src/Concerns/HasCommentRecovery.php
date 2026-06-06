<?php

declare(strict_types=1);

namespace Akira\Commentable\Concerns;

use Akira\Commentable\Models\CommentRevision;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Schema;
use Throwable;

trait HasCommentRecovery
{
    use SoftDeletes {
        bootSoftDeletes as bootEloquentSoftDeletes;
        performDeleteOnModel as performSoftDeleteOnModel;
        restore as restoreSoftDeletedModel;
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
