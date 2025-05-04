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

    /**
     * Get the user that the comment belongs to.
     *
     * @return MorphTo<Model, $this>
     */
    final public function commenter(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the reactions for the comment.
     *
     * @return HasMany<Reaction, $this>
     */
    final public function reactions(): HasMany
    {
        return $this->hasMany(Reaction::class, 'comment_id', 'id');

    }

    /**
     * Get the user  commenting the comment.
     *
     * @return HasMany<Reply, $this>
     */
    final public function replies(): HasMany
    {
        return $this->hasMany(Reply::class, 'reply_id', 'id');
    }

    /**
     * The attributes that should be cast to native types.
     *
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
