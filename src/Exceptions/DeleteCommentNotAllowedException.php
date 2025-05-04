<?php

declare(strict_types=1);

namespace Akira\Commentable\Exceptions;

use Exception;

final class DeleteCommentNotAllowedException extends Exception
{
    /**
     * Create a new exception instance.
     */
    public function __construct()
    {
        $message = __('Deleting comments is not allowed.');
        parent::__construct($message);
    }
}
