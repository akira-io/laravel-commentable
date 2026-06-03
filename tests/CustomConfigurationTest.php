<?php

declare(strict_types=1);

use Akira\Commentable\Tests\Fixtures\CustomComment;
use Akira\Commentable\Tests\Fixtures\CustomReaction;
use Akira\Commentable\Tests\Fixtures\Post;
use Akira\Commentable\Tests\Fixtures\User;
use Akira\Commentable\Tests\Support\CustomConfigurationTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

uses(CustomConfigurationTestCase::class, RefreshDatabase::class);

it('respects custom models and table names', function (): void {
    expect(Schema::hasTable('custom_comments'))->toBeTrue()
        ->and(Schema::hasTable('custom_reactions'))->toBeTrue()
        ->and(Schema::hasTable('comments'))->toBeFalse()
        ->and(Schema::hasTable('reactions'))->toBeFalse();

    $user = User::query()->create([
        'name' => 'Ada Lovelace',
        'email' => 'ada@example.com',
    ]);

    $post = Post::query()->create([
        'name' => 'Release notes',
        'user_id' => $user->id,
    ]);

    $comment = $user->comment($post, 'Ship the package update.');

    $reaction = $comment->reactions()->create([
        'type' => 'like',
        'owner_type' => $user::class,
        'owner_id' => $user->id,
    ]);

    $reply = $user->reply($comment, 'Confirmed.');

    expect($comment)->toBeInstanceOf(CustomComment::class)
        ->and($comment->commenter)->toBeInstanceOf(User::class)
        ->and($post->comments()->first())->toBeInstanceOf(CustomComment::class)
        ->and($reaction)->toBeInstanceOf(CustomReaction::class)
        ->and($reaction->owner)->toBeInstanceOf(User::class)
        ->and($comment->reactions()->first())->toBeInstanceOf(CustomReaction::class)
        ->and($reply->comment)->toBeInstanceOf(CustomComment::class);
});

it('uses custom table names on migration rollback', function (): void {
    $migration = include __DIR__.'/../database/migrations/create_commentable_table.php';

    $migration->down();

    expect(Schema::hasTable('custom_comments'))->toBeFalse()
        ->and(Schema::hasTable('custom_reactions'))->toBeFalse();
});
