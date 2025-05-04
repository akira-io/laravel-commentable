<?php

declare(strict_types=1);

namespace Akira\Commentable\Tests\Fixtures;

use Akira\Commentable\Concerns\Commenter;
use Illuminate\Database\Eloquent\Model;

final class User extends Model
{
    use Commenter;

    protected $guarded = [];
}
