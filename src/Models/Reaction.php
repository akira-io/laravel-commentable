<?php

declare(strict_types=1);

namespace Akira\Commentable\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

final class Reaction extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'comment_id',
        'type',
        'owner_id',
        'owner_type',
    ];

    /**
     * Get the table associated with the model.
     *
     * @return BelongsTo<Model, Reaction> *
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(
            config('auth.providers.users.model'),
            config('commentable', 'user_id')
        );
    }

    /**
     * Get the owner of the reaction.
     *
     * @return MorphTo<Model, Reaction>
     */
    public function owner(): MorphTo
    {
        return $this->morphTo();
    }
}
