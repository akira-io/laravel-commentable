<?php

declare(strict_types=1);

use Akira\Commentable\Exceptions\DeleteCommentNotAllowedException;
use Akira\Commentable\Tests\Fixtures\Post;
use Akira\Commentable\Tests\Fixtures\PostPolicy;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Gate;

beforeEach(function (): void {
    $this->user = user();
    $this->post = Post::query()->create(['name' => 'post1', 'user_id' => $this->user->id]);
});

it('keeps comment lifecycle actions available without gates', function (): void {
    $comment = $this->user->comment($this->post, 'comment1');
    $reply = $this->user->reply($comment, 'reply1');

    expect($this->user->approveComment($comment))->toBeTrue()
        ->and($comment->fresh()->approved)->toBeTrue()
        ->and($this->user->rejectComment($reply))->toBeTrue()
        ->and($reply->fresh()->approved)->toBeFalse();
});

it('denies comment creation through package gates', function (): void {
    Gate::define('commentable.comment', fn ($user, Post $post, string $comment): bool => false);

    expect(fn () => $this->user->comment($this->post, 'blocked'))
        ->toThrow(AuthorizationException::class);
});

it('authorizes comment creation through model policies', function (): void {
    Gate::policy(Post::class, PostPolicy::class);

    expect(fn () => $this->user->comment($this->post, 'blocked'))
        ->toThrow(AuthorizationException::class);

    expect($this->user->comment($this->post, 'allowed comment')->content)
        ->toBe('allowed comment');
});

it('denies replies through package gates', function (): void {
    $comment = $this->user->comment($this->post, 'comment1');

    Gate::define('commentable.reply', fn ($user, $message, string $reply): bool => false);

    expect(fn () => $this->user->reply($comment, 'blocked'))
        ->toThrow(AuthorizationException::class);
});

it('uses package gates for delete decisions before ownership fallback', function (): void {
    $comment = user()->comment($this->post, 'comment1');

    Gate::define('commentable.delete', fn ($user, $message): bool => true);

    $this->user->deleteComment($comment);

    expect($this->post->comments)->toHaveCount(0);
});

it('returns deterministic delete exceptions when package gates deny', function (): void {
    $comment = $this->user->comment($this->post, 'comment1');

    Gate::define('commentable.delete', fn ($user, $message): bool => false);

    expect(fn () => $this->user->deleteComment($comment))
        ->toThrow(DeleteCommentNotAllowedException::class);
});

it('uses package gates for force delete decisions', function (): void {
    $comment = user()->comment($this->post, 'comment1');

    Gate::define('commentable.forceDelete', fn ($user, $message): bool => true);

    $this->user->forceDeleteComment($comment);

    expect($this->post->comments)->toHaveCount(0);
});

it('authorizes approval and rejection through package gates', function (): void {
    $comment = $this->user->comment($this->post, 'comment1');

    Gate::define('commentable.approve', fn ($user, $message): bool => false);
    Gate::define('commentable.reject', fn ($user, $message): bool => true);

    expect(fn () => $this->user->approveComment($comment))
        ->toThrow(AuthorizationException::class)
        ->and($this->user->rejectComment($comment))->toBeTrue()
        ->and($comment->fresh()->approved)->toBeFalse();
});
