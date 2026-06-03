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
        return $this->belongsTo(
            $this->configuredUserModel(),
            $this->configuredUserForeignKey()
        );
    }

    /**
     * @return MorphTo<Model, $this>
     */
    final public function owner(): MorphTo
    {
        return $this->morphTo();
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
     * @phpstan-return non-empty-string
     */
    private function configuredUserForeignKey(): string
    {
        $foreignKey = config('commentable.user_foreign_key', 'user_id');

        if (is_string($foreignKey) && $foreignKey !== '') {
            return $foreignKey;
        }

        return 'user_id';
    }
}
