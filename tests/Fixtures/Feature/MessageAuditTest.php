<?php

declare(strict_types=1);

use Akira\Commentable\Models\Comment;
use Akira\Commentable\Models\CommentRevision;
use Akira\Commentable\Tests\Fixtures\ModeratorUser;
use Akira\Commentable\Tests\Fixtures\Post;

beforeEach(function (): void {
    $this->user = user();
    $this->post = Post::query()->create(['name' => 'post1', 'user_id' => $this->user->id]);
});

it('soft deletes and restores comments', function (): void {
    $comment = $this->user->comment($this->post, 'comment1');

    $this->user->deleteComment($comment);

    $trashedComment = Comment::withTrashed()->find($comment->id);

    expect(Comment::query()->find($comment->id))->toBeNull()
        ->and($trashedComment)->toBeInstanceOf(Comment::class)
        ->and($trashedComment->trashed())->toBeTrue()
        ->and($trashedComment->restore())->toBeTrue()
        ->and(Comment::query()->find($comment->id))->toBeInstanceOf(Comment::class);
});

it('force deletes comments permanently', function (): void {
    $comment = user()->comment($this->post, 'comment1');
    $moderator = ModeratorUser::query()->create([
        'name' => 'moderator',
        'email' => 'moderator@example.com',
    ]);

    $moderator->forceDeleteComment($comment);

    expect(Comment::withTrashed()->find($comment->id))->toBeNull();
});

it('records comment edit revisions with editor metadata', function (): void {
    $editor = user();
    $comment = $this->user->comment($this->post, 'original');

    $comment->edit('edited', $editor, 'clarified wording');

    $revision = $comment->revisions()->first();

    expect($comment->fresh()->content)->toBe('edited')
        ->and($revision)->toBeInstanceOf(CommentRevision::class)
        ->and($revision->previous_content)->toBe('original')
        ->and($revision->new_content)->toBe('edited')
        ->and($revision->reason)->toBe('clarified wording')
        ->and($revision->editor)->toBeInstanceOf($editor::class)
        ->and($revision->editor->getKey())->toBe($editor->getKey());
});

it('records reply edit revisions', function (): void {
    $comment = $this->user->comment($this->post, 'comment1');
    $reply = $this->user->reply($comment, 'original reply');

    $reply->edit('edited reply');

    expect($reply->fresh()->content)->toBe('edited reply')
        ->and($reply->revisions)->toHaveCount(1)
        ->first()
        ->previous_content->toBe('original reply');
});
