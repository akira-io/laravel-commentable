<?php

declare(strict_types=1);

use Akira\Commentable\Exceptions\ReplyDepthExceededException;
use Akira\Commentable\Models\Reply;
use Akira\Commentable\Tests\Fixtures\Post;

beforeEach(function (): void {
    $this->user = user();
    $this->post = Post::query()->create([
        'name' => 'post1',
        'user_id' => $this->user->id,
    ]);
});

it('preserves commentable context and thread depth on nested replies', function (): void {
    $comment = $this->user->comment($this->post, 'comment1');
    $reply = $this->user->reply($comment, 'reply1');
    $nestedReply = $this->user->reply($reply, 'reply2');

    expect($reply)
        ->toBeInstanceOf(Reply::class)
        ->and($reply->commentable_type)->toBe($this->post::class)
        ->and($reply->commentable_id)->toBe($this->post->id)
        ->and($reply->thread_depth)->toBe(1)
        ->and($nestedReply->commentable_type)->toBe($this->post::class)
        ->and($nestedReply->commentable_id)->toBe($this->post->id)
        ->and($nestedReply->thread_depth)->toBe(2)
        ->and($comment->load('replies.replies')->replies->first()->replies->first()->is($nestedReply))->toBeTrue();
});

it('rejects replies beyond the configured depth', function (): void {
    config()->set('commentable.max_reply_depth', 1);

    $comment = $this->user->comment($this->post, 'comment1');
    $reply = $this->user->reply($comment, 'reply1');

    expect(fn () => $this->user->reply($reply, 'reply2'))
        ->toThrow(ReplyDepthExceededException::class);
});
