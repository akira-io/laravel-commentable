<?php

declare(strict_types=1);

namespace Akira\Commentable\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

abstract class Message extends Model
{
    protected $fillable = [
        'content',
        'commenter_type',
        'commenter_id',
        'approved',
    ];

    /**
     * Get the table associated with the model.
     */
    public function __construct(array $attributes = [])
    {
        $this->table = config('commentable.comment_table', 'comments');

        parent::__construct($attributes);
    }

    final public function commenter(): MorphTo
    {
        return $this->morphTo();
    }

    final public function reactions(): HasMany
    {
        return $this->hasMany(Reaction::class, 'comment_id', 'id');

    }

    /**
     * Get the user  commenting the comment.
     *
     * @return HasMany<Model, $this>
     */
    final public function replies(): HasMany
    {
        return $this->hasMany(Reply::class, 'reply_id', 'id');
    }

    protected function casts(): array
    {

        return [
            'approved' => 'boolean',
        ];
    }
}
