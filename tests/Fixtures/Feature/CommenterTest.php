<?php

declare(strict_types=1);

use Akira\Commentable\Exceptions\DeleteCommentNotAllowedException;
use Akira\Commentable\Models\Comment;
use Akira\Commentable\Models\Reaction;
use Akira\Commentable\Tests\Fixtures\ModeratorUser;
use Akira\Commentable\Tests\Fixtures\Post;

beforeEach(function (): void {
    $this->user = user();
});

it(/**
 * @throws Exception
 */ 'should create a comment', function (): void {

    $post = Post::query()->create(['name' => 'post1', 'user_id' => $this->user->id]);

    $comment = $this->user->comment($post, 'comment1');

    expect($comment)
        ->toBeInstanceOf(Comment::class)
        ->and($post->comments)
        ->toHaveCount(1)
        ->first()
        ->content->toBe('comment1');
});

it(/**
 * @throws Exception
 */ 'should create a comment with a reply', function (): void {

    $post = Post::query()->create(['name' => 'post1', 'user_id' => $this->user->id]);

    $comment = $this->user->comment($post, 'comment1');

    $reply = $this->user->reply($comment, 'reply1');

    expect($reply)
        ->toBeInstanceOf(Akira\Commentable\Models\Reply::class)
        ->and($comment->replies)
        ->toHaveCount(1)
        ->first()
        ->content->toBe('reply1');
});

it('should create a comment with a reply and a reply to the reply', function (): void {

    $post = Post::query()->create(['name' => 'post1', 'user_id' => $this->user->id]);

    $comment = $this->user->comment($post, 'comment1');

    $reply = $this->user->reply($comment, 'reply1');

    $reply2 = $this->user->reply($reply, 'reply2');

    expect($reply2)
        ->toBeInstanceOf(Akira\Commentable\Models\Reply::class)
        ->and($reply->replies)
        ->toHaveCount(1)
        ->first()
        ->content->toBe('reply2');
});

it('shoulde delete a comment', function (): void {

    $post = Post::query()->create(['name' => 'post1', 'user_id' => $this->user->id]);

    $comment = $this->user->comment($post, 'comment1');

    $this->user->deleteComment($comment);

    expect($post->comments)
        ->toHaveCount(0);

});

it('shoulde delete a reply', function (): void {

    $post = Post::query()->create(['name' => 'post1', 'user_id' => $this->user->id]);

    $comment = $this->user->comment($post, 'comment1');

    $reply = $this->user->reply($comment, 'reply1');

    $this->user->deleteComment($reply);

    expect($comment->replies)
        ->toHaveCount(0);

});

it('shoulde delete a reply to a reply', function (): void {

    $post = Post::query()->create(['name' => 'post1', 'user_id' => $this->user->id]);

    $comment = $this->user->comment($post, 'comment1');

    $reply = $this->user->reply($comment, 'reply1');

    $reply2 = $this->user->reply($reply, 'reply2');

    $this->user->deleteComment($reply2);

    expect($reply->replies)
        ->toHaveCount(0);

});

it('should not delete a comment  that does not belongs to  the user', function (): void {

    $post = Post::query()->create(['name' => 'post1', 'user_id' => $this->user->id]);

    $comment = user()->comment($post, 'comment1');

    expect(fn () => $this->user->deleteComment($comment))
        ->toThrow(DeleteCommentNotAllowedException::class)
        ->and($post->comments)
        ->toHaveCount(1)
        ->first()
        ->content->toBe('comment1');
});

it('should not delete a reply that does not belongs to the user', function (): void {

    $post = Post::query()->create(['name' => 'post1', 'user_id' => $this->user->id]);

    $comment = $this->user->comment($post, 'comment1');

    $reply = user()->reply($comment, 'reply1');

    expect(fn () => $this->user->deleteComment($reply))
        ->toThrow(DeleteCommentNotAllowedException::class)
        ->and($comment->replies)
        ->toHaveCount(1)
        ->first()
        ->content->toBe('reply1');

});

it('should not force delete a comment without approval', function (): void {

    $post = Post::query()->create(['name' => 'post1', 'user_id' => $this->user->id]);

    $comment = user()->comment($post, 'comment1');

    expect(fn () => $this->user->forceDeleteComment($comment))
        ->toThrow(DeleteCommentNotAllowedException::class);

    expect($post->comments)
        ->toHaveCount(1);

});

it('should force delete a comment through explicit approval', function (): void {

    $post = Post::query()->create(['name' => 'post1', 'user_id' => $this->user->id]);

    $comment = user()->comment($post, 'comment1');

    $moderator = ModeratorUser::query()->create([
        'name' => 'moderator',
        'email' => 'moderator@example.com',
    ]);

    $moderator->forceDeleteComment($comment);

    expect($post->comments)
        ->toHaveCount(0);

});

it('should force delete a nested reply through explicit approval', function (): void {

    $post = Post::query()->create(['name' => 'post1', 'user_id' => $this->user->id]);

    $comment = $this->user->comment($post, 'comment1');

    $reply = $this->user->reply($comment, 'reply1');

    $reply2 = user()->reply($reply, 'reply2');

    $moderator = ModeratorUser::query()->create([
        'name' => 'moderator',
        'email' => 'moderator@example.com',
    ]);

    $moderator->forceDeleteComment($reply2);

    expect($post->comments)
        ->toHaveCount(1)
        ->and($comment->replies)
        ->toHaveCount(1)
        ->and($reply->replies)
        ->toHaveCount(0);

});

it('should resolve reaction relationships', function (): void {

    config()->set('auth.providers.users.model', $this->user::class);

    $post = Post::query()->create(['name' => 'post1', 'user_id' => $this->user->id]);

    $comment = $this->user->comment($post, 'comment1');

    $reaction = Reaction::query()->create([
        'comment_id' => $comment->id,
        'owner_type' => $this->user::class,
        'owner_id' => $this->user->id,
        'type' => 'like',
    ]);

    expect($reaction->owner)
        ->toBeInstanceOf($this->user::class)
        ->and($reaction->user)
        ->toBeInstanceOf($this->user::class)
        ->and($reaction->comment)
        ->toBeInstanceOf(Comment::class)
        ->and($comment->reactions)
        ->toHaveCount(1);
});
