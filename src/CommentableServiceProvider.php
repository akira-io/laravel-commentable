<?php

declare(strict_types=1);

namespace Akira\Commentable;

use Akira\Commentable\Commands\CommentableCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

final class CommentableServiceProvider extends PackageServiceProvider
{
    /**
     * Register the service provider.
     */
    public function configurePackage(Package $package): void
    {
        $package
            ->name('commentable')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_commentable_table')
            ->hasCommand(CommentableCommand::class);
    }
}
