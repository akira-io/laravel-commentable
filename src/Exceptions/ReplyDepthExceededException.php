<?php

declare(strict_types=1);

namespace Akira\Commentable\Exceptions;

use Exception;

final class ReplyDepthExceededException extends Exception
{
    /**
     * @param  int<0, max>  $maxDepth
     */
    public function __construct(int $maxDepth)
    {
        parent::__construct("Reply depth cannot exceed {$maxDepth}.");
    }
}
