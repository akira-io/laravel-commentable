<?php

declare(strict_types=1);

namespace Akira\Commentable\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Akira\Commentable\Commentable
 */
final class Commentable extends Facade
{
    /**
     * Get the facade accessor for the underlying class.
     */
    protected static function getFacadeAccessor(): string
    {
        return \Akira\Commentable\Commentable::class;
    }
}
