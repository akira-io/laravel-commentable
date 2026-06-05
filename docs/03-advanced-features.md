# Advanced Features

This guide explores advanced functionality including reactions, comment approval, and custom authorization.

## Reactions System

The package includes a `Reaction` model for adding emoji reactions or likes to comments.

### Reaction Model Structure

The `Reaction` model includes:
- `owner_type` and `owner_id` - Polymorphic relationship to the entity reacting
- `comment_id` - The comment being reacted to
- `type` - String identifier for the reaction type (e.g., "like", "love", "laugh")

### Creating Reactions

```php
use Akira\Commentable\Models\Reaction;

$user = User::find(1);
$comment = Comment::find(1);

Reaction::create([
    'owner_type' => User::class,
    'owner_id' => $user->id,
    'comment_id' => $comment->id,
    'type' => 'like',
]);
```

### Accessing Reactions

```php
$comment = Comment::find(1);

// Get all reactions
$reactions = $comment->reactions;

// Group reactions by type
$reactionCounts = $comment->reactions
    ->groupBy('type')
    ->map(fn($group) => $group->count());

// Check if user has reacted
$hasLiked = $comment->reactions()
    ->where('owner_id', auth()->id())
    ->where('owner_type', User::class)
    ->where('type', 'like')
    ->exists();
```

### Reaction Owner Relationship

```php
$reaction = Reaction::find(1);
$owner = $reaction->owner; // Returns User or any other model that created the reaction
```

## Comment Approval System

Comments include an `approved` boolean field for moderation workflows.

### Default Approval State

By default, comments are created with `approved = false`:

```php
$comment = $user->comment($post, 'Awaiting moderation');
echo $comment->approved; // false
```

### Querying Approved Comments

```php
$approvedComments = $post->comments()->approved()->get();

$pendingComments = $post->comments()->pending()->get();
```

### Approving Comments

```php
$comment = Comment::find(1);

$comment->approve();
```

### Rejecting Comments

```php
$comment = Comment::find(1);

$comment->reject();
```

### Marking Comments Pending

```php
$comment = Comment::find(1);

$comment->markPending();
```

### Bulk Moderation

```php
$comments = Comment::pending()->latest()->take(20)->get();

Comment::approveMany($comments);
```

### Building a Moderation Queue

```php
use Akira\Commentable\Models\Comment;

$pending = Comment::pending()
    ->with(['commenter', 'commentable'])
    ->orderBy('created_at', 'desc')
    ->paginate(20);
```

### Moderation Events

The package dispatches `CommentApproved` after `approve()` and `CommentRejected` after `reject()`:

```php
use Akira\Commentable\Events\CommentApproved;
use Akira\Commentable\Events\CommentRejected;
use Illuminate\Support\Facades\Event;

Event::listen(CommentApproved::class, function (CommentApproved $event): void {
    $comment = $event->comment;
});

Event::listen(CommentRejected::class, function (CommentRejected $event): void {
    $comment = $event->comment;
});
```

## Custom Authorization

### Override Approval Logic

Override the `approveCommentDeletion()` method in your `Commenter` model to customize who can delete comments:

```php
use Akira\Commentable\Concerns\Commenter;
use Akira\Commentable\Contracts\CommentContract;

class User extends Authenticatable
{
    use Commenter;

    public function approveCommentDeletion(CommentContract $comment): bool
    {
        // Allow admins to delete any comment
        if ($this->isAdmin()) {
            return true;
        }

        // Allow users to delete their own comments
        if ($this->getKey() === $comment->commenter_id) {
            return true;
        }

        // Allow post owners to delete comments on their posts
        if ($comment->commentable_type === Post::class) {
            $post = $comment->commentable;
            return $post->user_id === $this->id;
        }

        return false;
    }
}
```

## Working with Polymorphic Relationships

### Query Comments by Commenter Type

```php
use App\Models\User;
use App\Models\Admin;

// Get all comments by users
$userComments = Comment::where('commenter_type', User::class)->get();

// Get all comments by admins
$adminComments = Comment::where('commenter_type', Admin::class)->get();
```

### Query Comments by Commentable Type

```php
use App\Models\Post;
use App\Models\Video;

// Get all comments on posts
$postComments = Comment::where('commentable_type', Post::class)->get();

// Get all comments on videos
$videoComments = Comment::where('commentable_type', Video::class)->get();
```

## Eager Loading

Optimize queries by eager loading relationships:

```php
// Load comments with their commenters and replies
$post = Post::with([
    'comments.commenter',
    'comments.replies.commenter',
    'comments.reactions.owner'
])->find(1);
```

## Comment Counts

```php
// Count comments on a model
$commentCount = $post->comments()->count();

// Count approved comments only
$approvedCount = $post->comments()->where('approved', true)->count();

// Count replies on a comment
$replyCount = $comment->replies()->count();
```

**Previous:** [Basic Usage](02-basic-usage.md) | **Next:** [Configuration](04-configuration.md)
