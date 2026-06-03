<?php

declare(strict_types=1);

use Akira\Commentable\Tests\Fixtures\User;
use Akira\Commentable\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(TestCase::class, RefreshDatabase::class)
    ->in(__DIR__.'/Fixtures');

function user(): User
{
    return User::query()->create([
        'name' => fake()->name(),
        'email' => fake()->email(),
    ]);

}
