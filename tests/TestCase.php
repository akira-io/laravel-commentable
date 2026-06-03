<?php

declare(strict_types=1);

namespace Akira\Commentable\Tests;

use Akira\Commentable\CommentableServiceProvider;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\File;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName): string => 'Akira\\Commentable\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );
    }

    final public function getEnvironmentSetUp($app): void
    {
        config()->set('database.default', 'testing');

        $this->defineEnvironment($app);

        $migrations = [
            __DIR__.'/../database/migrations',
            __DIR__.'/Fixtures/Migrations',
        ];

        foreach ($migrations as $migration) {
            foreach (File::files($migration) as $file) {
                (include $file->getRealPath())->up();
            }
        }
    }

    protected function defineEnvironment($app): void {}

    protected function getPackageProviders($app)
    {
        return [
            CommentableServiceProvider::class,
        ];
    }
}
