<?php

declare(strict_types=1);

return [

    'comment_table' => 'comments',

    'reaction_table' => 'reactions',

    'revision_table' => 'comment_revisions',

    'user_foreign_key' => 'user_id',

    'models' => [
        'comment' => Akira\Commentable\Models\Comment::class,
        'reaction' => Akira\Commentable\Models\Reaction::class,
    ],

];
