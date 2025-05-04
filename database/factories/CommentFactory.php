<?php

declare(strict_types=1);

namespace Akira\Commentable\Database\Factories;

use Akira\Commentable\Models\Comment;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

final class CommentFactory extends Factory
{
    protected $model = Comment::class;

    public function definition(): array
    {

        return [
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
