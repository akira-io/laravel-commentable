<?php

declare(strict_types=1);

namespace Akira\Commentable\Commands;

use Illuminate\Console\Command;

final class CommentableCommand extends Command
{
    public $signature = 'commentable';

    public $description = 'Commentable command';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
