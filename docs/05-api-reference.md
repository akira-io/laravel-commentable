# API Reference

Complete reference for all classes, traits, contracts, and methods provided by Laravel Commentable.

## Traits

### Commentable

**Namespace:** `Akira\Commentable\Concerns\Commentable`

Makes a model able to receive comments.

#### Methods

##### `comments(): MorphMany`

Returns the polymorphic relationship to all comments on the model.

```php
public function comments(): MorphMany
```

**Returns:** `MorphMany<Comment>` - Collection of comments

**Example:**
```php
$post = Post::find(1);
$comments = $post->comments; // All comments on this post
$approvedComments = $post->comments()->where('approved', true)->get();
```

---

### Commenter

**Namespace:** `Akira\Commentable\Concerns\Commenter`

Makes a model able to create and manage comments.

#### Methods

##### `comments(): MorphMany`

Returns all comments created by this commenter.

```php
public function comments(): MorphMany
```

**Returns:** `MorphMany<Comment>` - Collection of comments created by this model

**Example:**
```php
$user = User::find(1);
$userComments = $user->comments; // All comments by this user
```

---

##### `replies(): HasMany`

Returns all replies created by this commenter.

```php
public function replies(): HasMany
```

**Returns:** `HasMany<Reply>` - Collection of replies created by this model

**Example:**
```php
$user = User::find(1);
$userReplies = $user->replies; // All replies by this user
```

---

##### `comment(Model $model, string $comment): CommentContract`

Creates a comment on a commentable model.

```php
public function comment(Model $model, string $comment): CommentContract
```

**Parameters:**
- `$model` - The model to comment on (must use `Commentable` trait)
- `$comment` - The comment content

**Returns:** `CommentContract` - The created comment instance

**Throws:** `Exception` - If the model doesn't use the `Commentable` trait

**Example:**
```php
$user = User::find(1);
$post = Post::find(1);

$comment = $user->comment($post, 'Great article!');
```

---

##### `reply(Comment|Reply $comment, string $reply): CommentContract`

Creates a reply to a comment or another reply.

```php
public function reply(Comment|Reply $comment, string $reply): CommentContract
```

**Parameters:**
- `$comment` - The comment or reply to respond to
- `$reply` - The reply content

**Returns:** `CommentContract` - The created reply instance

**Example:**
```php
$user = User::find(1);
$comment = Comment::find(1);

$reply = $user->reply($comment, 'Thanks for sharing!');
```

---

##### `deleteComment(CommentContract $comment): void`

Deletes a comment if the user is authorized.

```php
public function deleteComment(CommentContract $comment): void
```

**Parameters:**
- `$comment` - The comment to delete

**Returns:** `void`

**Throws:** `DeleteCommentNotAllowedException` - If user is not authorized to delete the comment

**Example:**
```php
$user = User::find(1);
$comment = Comment::find(1);

try {
    $user->deleteComment($comment);
} catch (DeleteCommentNotAllowedException $e) {
    // Handle unauthorized deletion
}
```

---

##### `approveCommentDeletion(CommentContract $comment): bool`

Determines if the user can delete a comment.

```php
public function approveCommentDeletion(CommentContract $comment): bool
```

**Parameters:**
- `$comment` - The comment to check

**Returns:** `bool` - True if user owns the comment, false otherwise

**Example:**
```php
$user = User::find(1);
$comment = Comment::find(1);

if ($user->approveCommentDeletion($comment)) {
    $user->deleteComment($comment);
}
```

**Note:** Override this method to implement custom authorization logic.

---

##### `forceDeleteComment(Message $comment): ?bool`

Deletes a comment after `approveForcedCommentDeletion()` allows the action.

```php
public function forceDeleteComment(Message $comment): ?bool
```

**Parameters:**
- `$comment` - The message to delete

**Returns:** `?bool` - Result of the delete operation

**Throws:** `DeleteCommentNotAllowedException` - If user is not authorized to force delete the comment

**Example:**
```php
$admin = User::find(1);
$comment = Comment::find(1);

$admin->forceDeleteComment($comment);
```

---

##### `approveForcedCommentDeletion(CommentContract $comment): bool`

Determines if the user can force delete a comment.

```php
public function approveForcedCommentDeletion(CommentContract $comment): bool
```

**Parameters:**
- `$comment` - The comment to check

**Returns:** `bool` - True if forced deletion is allowed, false otherwise

**Example:**
```php
public function approveForcedCommentDeletion(CommentContract $comment): bool
{
    return $this->isModerator();
}
```

**Note:** The default implementation falls back to `approveCommentDeletion()`.

---

## Models

### Message (Abstract)

**Namespace:** `Akira\Commentable\Models\Message`

Abstract base class for `Comment` and `Reply` models.

#### Properties

**Fillable:**
- `content` - The comment/reply text
- `commenter_type` - Polymorphic type of the commenter
- `commenter_id` - ID of the commenter
- `approved` - Boolean approval status

**Casts:**
- `approved` - boolean
- `created_at` - datetime
- `updated_at` - datetime

#### Methods

##### `commenter(): MorphTo`

Returns the polymorphic relationship to the user/model that created the comment.

```php
final public function commenter(): MorphTo
```

**Example:**
```php
$comment = Comment::find(1);
$user = $comment->commenter; // User who created the comment
```

---

##### `reactions(): HasMany`

Returns all reactions on this comment.

```php
final public function reactions(): HasMany
```

**Example:**
```php
$comment = Comment::find(1);
$reactions = $comment->reactions; // All reactions on this comment
```

---

##### `replies(): HasMany`

Returns all replies to this comment.

```php
final public function replies(): HasMany
```

**Example:**
```php
$comment = Comment::find(1);
$replies = $comment->replies; // All replies to this comment
```

---

##### `approve(): bool`

Marks a comment or reply as approved and dispatches `CommentApproved`.

```php
final public function approve(): bool
```

**Returns:** `bool` - Result of the save operation

**Example:**
```php
$comment = Comment::find(1);

$comment->approve();
```

---

##### `reject(): bool`

Marks a comment or reply as pending and dispatches `CommentRejected`.

```php
final public function reject(): bool
```

**Returns:** `bool` - Result of the save operation

**Example:**
```php
$comment = Comment::find(1);

$comment->reject();
```

---

##### `markPending(): bool`

Marks a comment or reply as pending without dispatching a rejection event.

```php
final public function markPending(): bool
```

**Returns:** `bool` - Result of the save operation

**Example:**
```php
$comment = Comment::find(1);

$comment->markPending();
```

---

##### `scopeApproved(Builder $query): Builder`

Filters approved comments or replies.

```php
final public function scopeApproved(Builder $query): Builder
```

**Example:**
```php
$approvedComments = Comment::approved()->get();
```

---

##### `scopePending(Builder $query): Builder`

Filters pending comments or replies.

```php
final public function scopePending(Builder $query): Builder
```

**Example:**
```php
$pendingComments = Comment::pending()->get();
```

---

##### `approveMany(iterable $comments): int`

Approves each message in an iterable and returns the number of successful saves.

```php
final public static function approveMany(iterable $comments): int
```

**Example:**
```php
$comments = Comment::pending()->get();

Comment::approveMany($comments);
```

---

##### `rejectMany(iterable $comments): int`

Rejects each message in an iterable and returns the number of successful saves.

```php
final public static function rejectMany(iterable $comments): int
```

**Example:**
```php
$comments = Comment::approved()->get();

Comment::rejectMany($comments);
```

---

### Comment

**Namespace:** `Akira\Commentable\Models\Comment`

Represents a top-level comment on a commentable model. Extends `Message`.

**Example:**
```php
use Akira\Commentable\Models\Comment;

$comment = Comment::create([
    'commentable_type' => Post::class,
    'commentable_id' => 1,
    'commenter_type' => User::class,
    'commenter_id' => 1,
    'content' => 'This is a comment',
    'approved' => false,
]);
```

---

### Reply

**Namespace:** `Akira\Commentable\Models\Reply`

Represents a reply to a comment or another reply. Extends `Message`.

#### Properties

**Additional Fillable:**
- `reply_id` - ID of the parent comment/reply

#### Methods

##### `comment(): BelongsTo`

Returns the parent comment relationship.

```php
public function comment(): BelongsTo
```

**Example:**
```php
$reply = Reply::find(1);
$parentComment = $reply->comment; // The comment this is a reply to
```

---

### Reaction

**Namespace:** `Akira\Commentable\Models\Reaction`

Represents a reaction (like, love, etc.) to a comment.

#### Properties

**Fillable:**
- `comment_id` - The comment being reacted to
- `type` - String identifier for reaction type
- `owner_id` - ID of the entity reacting
- `owner_type` - Polymorphic type of the entity reacting

#### Methods

##### `user(): BelongsTo`

Returns the user relationship for backward compatibility. Prefer `owner()` for new code.

```php
public function user(): BelongsTo
```

**Example:**
```php
$reaction = Reaction::find(1);
$user = $reaction->user;
```

---

##### `comment(): BelongsTo`

Returns the comment or reply this reaction belongs to.

```php
public function comment(): BelongsTo
```

**Example:**
```php
$reaction = Reaction::find(1);
$comment = $reaction->comment;
```

---

##### `owner(): MorphTo`

Returns the polymorphic relationship to the entity that created the reaction.

```php
public function owner(): MorphTo
```

**Example:**
```php
$reaction = Reaction::find(1);
$owner = $reaction->owner; // User or other model that created the reaction
```

---

## Contracts

### CommentContract

**Namespace:** `Akira\Commentable\Contracts\CommentContract`

Empty interface implemented by `Comment` and `Reply` models for type hinting.

**Example:**
```php
use Akira\Commentable\Contracts\CommentContract;

function processComment(CommentContract $comment): void
{
    // Works with both Comment and Reply instances
}
```

---

## Exceptions

### DeleteCommentNotAllowedException

**Namespace:** `Akira\Commentable\Exceptions\DeleteCommentNotAllowedException`

Thrown when a user attempts to delete a comment they don't own.

**Message:** "Deleting comments is not allowed." (translated)

**Example:**
```php
use Akira\Commentable\Exceptions\DeleteCommentNotAllowedException;

try {
    $user->deleteComment($comment);
} catch (DeleteCommentNotAllowedException $e) {
    return response()->json(['error' => $e->getMessage()], 403);
}
```

## Events

### CommentApproved

**Namespace:** `Akira\Commentable\Events\CommentApproved`

Dispatched after `Message::approve()` saves an approved state.

**Properties:**
- `comment` - The approved `Message`

### CommentRejected

**Namespace:** `Akira\Commentable\Events\CommentRejected`

Dispatched after `Message::reject()` saves a pending state.

**Properties:**
- `comment` - The rejected `Message`

---

## Service Provider

### CommentableServiceProvider

**Namespace:** `Akira\Commentable\CommentableServiceProvider`

Registers the package with Laravel. Automatically loaded via package discovery.

**Publishes:**
- Config: `commentable-config`
- Migrations: `commentable-migrations`
- Views: `commentable-views`

---

## Facade

### Commentable

**Namespace:** `Akira\Commentable\Facades\Commentable`

Facade accessor for the `Commentable` class (currently empty, reserved for future functionality).

**Previous:** [Configuration](04-configuration.md) | **Next:** [Database Schema](06-database-schema.md)
