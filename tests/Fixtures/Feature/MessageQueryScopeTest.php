<?php

declare(strict_types=1);

use Akira\Commentable\Models\Comment;
use Akira\Commentable\Models\Reaction;
use Akira\Commentable\Tests\Fixtures\Post;

beforeEach(function (): void {
    $this->user = user();
    $this->post = Post::query()->create([
        'name' => 'post1',
        'user_id' => $this->user->id,
    ]);
});

it('loads approved comments with commenter and reaction counts', function (): void {
    $approved = $this->user->comment($this->post, 'approved');
    $pending = $this->user->comment($this->post, 'pending');

    $approved->approve();
    Reaction::query()->create([
        'comment_id' => $approved->id,
        'owner_type' => $this->user::class,
        'owner_id' => $this->user->id,
        'type' => 'like',
    ]);

    $comments = Comment::query()
        ->approved()
        ->withCommenter()
        ->withReactionCounts()
        ->get();

    expect($comments)->toHaveCount(1)
        ->first()
        ->content->toBe('approved')
        ->and($comments->first()->relationLoaded('commenter'))->toBeTrue()
        ->and($comments->first()->relationLoaded('reactions'))->toBeFalse()
        ->and($comments->first()->reactions_count)->toBe(1)
        ->and(Comment::query()->pending()->pluck('id')->all())->toBe([$pending->id]);
});

it('loads nested thread relationships through the commentable helper', function (): void {
    $comment = $this->user->comment($this->post, 'comment1');
    $reply = $this->user->reply($comment, 'reply1');
    $nestedReply = $this->user->reply($reply, 'reply2');

    Reaction::query()->create([
        'comment_id' => $comment->id,
        'owner_type' => $this->user::class,
        'owner_id' => $this->user->id,
        'type' => 'like',
    ]);

    $threadComment = $this->post->commentsWithThread()->first();
    $threadReply = $threadComment->replies->first();

    expect($threadComment->is($comment))->toBeTrue()
        ->and($threadComment->relationLoaded('commenter'))->toBeTrue()
        ->and($threadComment->relationLoaded('replies'))->toBeTrue()
        ->and($threadComment->reactions_count)->toBe(1)
        ->and($threadReply->is($reply))->toBeTrue()
        ->and($threadReply->relationLoaded('commenter'))->toBeTrue()
        ->and($threadReply->relationLoaded('replies'))->toBeTrue()
        ->and($threadReply->replies->first()->is($nestedReply))->toBeTrue();
});
