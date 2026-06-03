<?php

declare(strict_types=1);

namespace Akira\Commentable\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class Reply extends Message
{
    protected $fillable = [
        'commenter_type',
        'commenter_id',
        'content',
        'approved',
        'reply_id',
    ];

    /**
     * @return BelongsTo<Message, $this>
     */
    public function comment(): BelongsTo
    {
        return $this->belongsTo(self::configuredCommentModel(), 'reply_id', 'id');
    }
}
