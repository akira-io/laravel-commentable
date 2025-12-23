# Basic Usage

This guide covers the essential steps to add commenting functionality to your Laravel models.

## Setup Your Models

### Make a Model Commentable

Use the `Commentable` trait on any model that should receive comments:

```php
<?php

namespace App\Models;

use Akira\Commentable\Concerns\Commentable;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use Commentable;
}
```

The `Commentable` trait adds a `comments()` relationship that returns all comments on the model via a polymorphic `morphMany` relationship.

### Make a Model a Commenter

Use the `Commenter` trait on models that can create comments (typically your User model):

```php
<?php

namespace App\Models;

use Akira\Commentable\Concerns\Commenter;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Commenter;
}
```

The `Commenter` trait provides methods for creating, replying to, and deleting comments.

## Creating Comments

### Add a Comment to a Model

```php
$user = User::find(1);
$post = Post::find(1);

$comment = $user->comment($post, 'This is a great post!');
```

The `comment()` method:
- Validates that the target model uses the `Commentable` trait
- Creates a new comment record
- Returns a `CommentContract` instance (specifically a `Comment` model)
- Automatically sets the commenter relationship

### Access Comments on a Model

```php
$post = Post::find(1);

foreach ($post->comments as $comment) {
    echo $comment->content;
    echo $comment->commenter->name; // Access the user who commented
}
```

## Creating Replies

### Reply to a Comment

```php
$user = User::find(1);
$comment = Comment::find(1);

$reply = $user->reply($comment, 'Thanks for your feedback!');
```

### Reply to a Reply (Nested Threads)

```php
$user = User::find(2);
$reply = Reply::find(1);

$nestedReply = $user->reply($reply, 'I agree completely!');
```

Replies work the same way for both `Comment` and `Reply` instances, allowing unlimited nesting depth.

### Access Replies on a Comment

```php
$comment = Comment::find(1);

foreach ($comment->replies as $reply) {
    echo $reply->content;
    echo $reply->commenter->name;
}
```

## Deleting Comments

### Delete Your Own Comment

```php
$user = User::find(1);
$comment = Comment::find(1);

try {
    $user->deleteComment($comment);
} catch (\Akira\Commentable\Exceptions\DeleteCommentNotAllowedException $e) {
    // User is not authorized to delete this comment
}
```

The `deleteComment()` method:
- Checks ownership via `approveCommentDeletion()`
- Throws `DeleteCommentNotAllowedException` if the user doesn't own the comment
- Soft or hard deletes based on your model configuration

### Force Delete Any Comment

```php
$user = User::find(1); // Admin or post owner
$comment = Comment::find(1);

$user->forceDeleteComment($comment);
```

Use `forceDeleteComment()` when you need to bypass ownership checks (e.g., moderators deleting inappropriate content).

## Relationships

### Access the Commenter

```php
$comment = Comment::find(1);
$user = $comment->commenter; // Returns the User model instance
```

### Access Comment Reactions

```php
$comment = Comment::find(1);
$reactions = $comment->reactions; // Collection of Reaction models
```

**Previous:** [Installation](01-installation.md) | **Next:** [Advanced Features](03-advanced-features.md)
