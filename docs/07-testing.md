# Testing

Guide to testing comment functionality in your Laravel application.

## Setting Up Test Models

Create test fixtures that use the commentable traits.

### Test User Model

```php
<?php

namespace Tests\Fixtures;

use Akira\Commentable\Concerns\Commenter;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    use Commenter;

    protected $guarded = [];
}
```

### Test Commentable Model

```php
<?php

namespace Tests\Fixtures;

use Akira\Commentable\Concerns\Commentable;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use Commentable;

    protected $guarded = [];
}
```

## Testing Comment Creation

### Basic Comment Test

```php
use Tests\Fixtures\User;
use Tests\Fixtures\Post;
use Akira\Commentable\Models\Comment;

test('user can create a comment on a post', function () {
    $user = User::create(['name' => 'John Doe']);
    $post = Post::create(['title' => 'Test Post']);

    $comment = $user->comment($post, 'Great post!');

    expect($comment)
        ->toBeInstanceOf(Comment::class)
        ->and($comment->content)->toBe('Great post!')
        ->and($comment->commenter_id)->toBe($user->id)
        ->and($comment->commentable_id)->toBe($post->id)
        ->and($post->comments)->toHaveCount(1);
});
```

### Test Comment Requires Commentable Trait

```php
use Illuminate\Database\Eloquent\Model;

test('throws exception when commenting on non-commentable model', function () {
    $user = User::create(['name' => 'John Doe']);
    $model = new class extends Model {};

    $user->comment($model, 'This should fail');
})->throws(Exception::class, 'The model must use the Commentable trait');
```

## Testing Replies

### Basic Reply Test

```php
test('user can reply to a comment', function () {
    $user = User::create(['name' => 'John Doe']);
    $post = Post::create(['title' => 'Test Post']);
    
    $comment = $user->comment($post, 'Original comment');
    $reply = $user->reply($comment, 'Reply to comment');

    expect($reply)
        ->toBeInstanceOf(Reply::class)
        ->and($reply->content)->toBe('Reply to comment')
        ->and($comment->replies)->toHaveCount(1);
});
```

### Nested Replies Test

```php
test('user can create nested replies', function () {
    $user = User::create(['name' => 'John Doe']);
    $post = Post::create(['title' => 'Test Post']);
    
    $comment = $user->comment($post, 'Original');
    $reply1 = $user->reply($comment, 'Reply 1');
    $reply2 = $user->reply($reply1, 'Reply to reply');

    expect($reply2)
        ->toBeInstanceOf(Reply::class)
        ->and($reply1->replies)->toHaveCount(1)
        ->and($reply1->replies->first()->content)->toBe('Reply to reply');
});
```

## Testing Comment Deletion

### Owner Can Delete Own Comment

```php
test('user can delete their own comment', function () {
    $user = User::create(['name' => 'John Doe']);
    $post = Post::create(['title' => 'Test Post']);
    
    $comment = $user->comment($post, 'My comment');
    
    $user->deleteComment($comment);

    expect($post->comments()->count())->toBe(0);
});
```

### Non-Owner Cannot Delete Comment

```php
use Akira\Commentable\Exceptions\DeleteCommentNotAllowedException;

test('user cannot delete another users comment', function () {
    $user1 = User::create(['name' => 'John Doe']);
    $user2 = User::create(['name' => 'Jane Smith']);
    $post = Post::create(['title' => 'Test Post']);
    
    $comment = $user1->comment($post, 'Johns comment');
    
    $user2->deleteComment($comment);
})->throws(DeleteCommentNotAllowedException::class);
```

### Force Delete Bypasses Authorization

```php
test('force delete bypasses authorization checks', function () {
    $user1 = User::create(['name' => 'John Doe']);
    $user2 = User::create(['name' => 'Jane Smith']);
    $post = Post::create(['title' => 'Test Post']);
    
    $comment = $user1->comment($post, 'Johns comment');
    
    $user2->forceDeleteComment($comment);

    expect($post->comments()->count())->toBe(0);
});
```

## Testing Reactions

### Create Reaction Test

```php
use Akira\Commentable\Models\Reaction;

test('user can react to a comment', function () {
    $user = User::create(['name' => 'John Doe']);
    $post = Post::create(['title' => 'Test Post']);
    $comment = $user->comment($post, 'Great post!');

    $reaction = Reaction::create([
        'owner_type' => User::class,
        'owner_id' => $user->id,
        'comment_id' => $comment->id,
        'type' => 'like',
    ]);

    expect($reaction->type)->toBe('like')
        ->and($comment->reactions)->toHaveCount(1)
        ->and($reaction->owner->id)->toBe($user->id);
});
```

### Multiple Reactions Test

```php
test('comment can have multiple reactions', function () {
    $user1 = User::create(['name' => 'John']);
    $user2 = User::create(['name' => 'Jane']);
    $post = Post::create(['title' => 'Test Post']);
    $comment = $user1->comment($post, 'Great!');

    Reaction::create([
        'owner_type' => User::class,
        'owner_id' => $user1->id,
        'comment_id' => $comment->id,
        'type' => 'like',
    ]);

    Reaction::create([
        'owner_type' => User::class,
        'owner_id' => $user2->id,
        'comment_id' => $comment->id,
        'type' => 'love',
    ]);

    expect($comment->reactions)->toHaveCount(2);
});
```

## Testing Approval System

### Default Approval State

```php
test('comments are not approved by default', function () {
    $user = User::create(['name' => 'John Doe']);
    $post = Post::create(['title' => 'Test Post']);
    
    $comment = $user->comment($post, 'Test comment');

    expect($comment->approved)->toBeFalse();
});
```

### Approve Comment Test

```php
test('comment can be approved', function () {
    $user = User::create(['name' => 'John Doe']);
    $post = Post::create(['title' => 'Test Post']);
    
    $comment = $user->comment($post, 'Test comment');
    $comment->approved = true;
    $comment->save();

    expect($comment->fresh()->approved)->toBeTrue();
});
```

### Query Approved Comments

```php
test('can query only approved comments', function () {
    $user = User::create(['name' => 'John Doe']);
    $post = Post::create(['title' => 'Test Post']);
    
    $approved = $user->comment($post, 'Approved');
    $approved->update(['approved' => true]);
    
    $pending = $user->comment($post, 'Pending');

    $approvedComments = $post->comments()->where('approved', true)->get();

    expect($approvedComments)->toHaveCount(1)
        ->and($approvedComments->first()->content)->toBe('Approved');
});
```

## Testing Relationships

### Comment-Commenter Relationship

```php
test('comment belongs to commenter', function () {
    $user = User::create(['name' => 'John Doe']);
    $post = Post::create(['title' => 'Test Post']);
    
    $comment = $user->comment($post, 'Test');

    expect($comment->commenter)
        ->toBeInstanceOf(User::class)
        ->and($comment->commenter->id)->toBe($user->id);
});
```

### Comment-Commentable Relationship

```php
test('commentable has many comments', function () {
    $user = User::create(['name' => 'John Doe']);
    $post = Post::create(['title' => 'Test Post']);
    
    $user->comment($post, 'Comment 1');
    $user->comment($post, 'Comment 2');
    $user->comment($post, 'Comment 3');

    expect($post->comments)->toHaveCount(3);
});
```

## Testing with Factories

Create factories for easier testing:

```php
<?php

namespace Database\Factories;

use Akira\Commentable\Models\Comment;
use Illuminate\Database\Eloquent\Factories\Factory;

class CommentFactory extends Factory
{
    protected $model = Comment::class;

    public function definition(): array
    {
        return [
            'content' => $this->faker->paragraph(),
            'commenter_type' => User::class,
            'commenter_id' => User::factory(),
            'commentable_type' => Post::class,
            'commentable_id' => Post::factory(),
            'approved' => false,
        ];
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'approved' => true,
        ]);
    }
}
```

**Usage:**
```php
test('can create comments with factory', function () {
    $comment = Comment::factory()->approved()->create();

    expect($comment->approved)->toBeTrue()
        ->and($comment->content)->not->toBeEmpty();
});
```

## Integration Testing

### Full Comment Thread Test

```php
test('can create full comment thread with reactions', function () {
    $author = User::factory()->create();
    $commenter = User::factory()->create();
    $post = Post::factory()->create();

    // Create comment
    $comment = $commenter->comment($post, 'First comment');
    
    // Create reply
    $reply = $author->reply($comment, 'Thanks!');
    
    // Add reactions
    Reaction::create([
        'owner_type' => User::class,
        'owner_id' => $author->id,
        'comment_id' => $comment->id,
        'type' => 'like',
    ]);

    expect($post->comments)->toHaveCount(1)
        ->and($comment->replies)->toHaveCount(1)
        ->and($comment->reactions)->toHaveCount(1);
});
```

**Previous:** [Database Schema](06-database-schema.md) | **Next:** [Examples](08-examples.md)
