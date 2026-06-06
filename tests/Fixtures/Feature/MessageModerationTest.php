<?php

declare(strict_types=1);

use Akira\Commentable\Events\CommentApproved;
use Akira\Commentable\Events\CommentRejected;
use Akira\Commentable\Models\Comment;
use Akira\Commentable\Tests\Fixtures\Post;
use Illuminate\Support\Facades\Event;

beforeEach(function (): void {
    $this->user = user();
    $this->post = Post::query()->create([
        'name' => 'post1',
        'user_id' => $this->user->id,
    ]);
});

it('approves rejects and marks messages pending', function (): void {
    $comment = $this->user->comment($this->post, 'comment1');
    $reply = $this->user->reply($comment, 'reply1');

    expect($comment->approve())->toBeTrue()
        ->and($comment->fresh()->approved)->toBeTrue()
        ->and($reply->approve())->toBeTrue()
        ->and($reply->fresh()->approved)->toBeTrue()
        ->and($comment->reject())->toBeTrue()
        ->and($comment->fresh()->approved)->toBeFalse()
        ->and($reply->markPending())->toBeTrue()
        ->and($reply->fresh()->approved)->toBeFalse();
});

it('filters approved and pending messages with scopes', function (): void {
    $approved = $this->user->comment($this->post, 'approved');
    $pending = $this->user->comment($this->post, 'pending');

    $approved->approve();

    expect(Comment::query()->approved()->pluck('content')->all())
        ->toBe(['approved'])
        ->and(Comment::query()->pending()->pluck('content')->all())
        ->toBe(['pending']);
});

it('bulk approves and rejects messages', function (): void {
    $first = $this->user->comment($this->post, 'first');
    $second = $this->user->comment($this->post, 'second');

    expect(Comment::approveMany([$first, $second]))->toBe(2)
        ->and($first->fresh()->approved)->toBeTrue()
        ->and($second->fresh()->approved)->toBeTrue()
        ->and(Comment::rejectMany([$first, $second]))->toBe(2)
        ->and($first->fresh()->approved)->toBeFalse()
        ->and($second->fresh()->approved)->toBeFalse();
});

it('emits moderation events for approval and rejection', function (): void {
    $comment = $this->user->comment($this->post, 'comment1');

    Event::fake([CommentApproved::class, CommentRejected::class]);

    $comment->approve();
    $comment->reject();

    Event::assertDispatched(CommentApproved::class, fn (CommentApproved $event): bool => $event->comment->is($comment));
    Event::assertDispatched(CommentRejected::class, fn (CommentRejected $event): bool => $event->comment->is($comment));
});
