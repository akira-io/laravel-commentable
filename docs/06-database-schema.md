# Database Schema

Complete documentation of the database structure used by Laravel Commentable.

## Tables

### comments

Stores all comments and replies in a single table using a self-referencing structure.

#### Columns

| Column | Type | Nullable | Indexed | Description |
|--------|------|----------|---------|-------------|
| `id` | bigInteger | No | Primary | Auto-incrementing primary key |
| `commentable_type` | string | Yes | Yes | Polymorphic type of the model being commented on |
| `commentable_id` | bigInteger | Yes | Yes | Polymorphic ID of the model being commented on |
| `commenter_type` | string | Yes | Yes | Polymorphic type of the entity creating the comment |
| `commenter_id` | bigInteger | Yes | Yes | Polymorphic ID of the entity creating the comment |
| `reply_id` | bigInteger | Yes | Yes | Foreign key to parent comment/reply (null for top-level comments) |
| `content` | longText | No | No | The comment or reply content |
| `approved` | boolean | No | Yes | Moderation flag (default: false) |
| `created_at` | timestamp | Yes | No | Creation timestamp |
| `updated_at` | timestamp | Yes | No | Last update timestamp |
| `deleted_at` | timestamp | Yes | No | Soft delete timestamp |

#### Indexes

- Primary key on `id`
- Composite index on `commentable_type` and `commentable_id` (polymorphic)
- Composite index on `commenter_type` and `commenter_id` (polymorphic)
- Index on `reply_id`
- Index on `approved`

#### Foreign Keys

- `reply_id` references `id` on `comments` table with cascade on delete

---

### comment_revisions

Stores edit history for comments and replies.

#### Columns

| Column | Type | Nullable | Indexed | Description |
|--------|------|----------|---------|-------------|
| `id` | bigInteger | No | Primary | Auto-incrementing primary key |
| `comment_id` | bigInteger | No | Foreign | Foreign key to comments table |
| `editor_type` | string | Yes | Yes | Polymorphic type of the editing model |
| `editor_id` | bigInteger | Yes | Yes | Polymorphic ID of the editing model |
| `previous_content` | longText | No | No | Content before the edit |
| `new_content` | longText | No | No | Content after the edit |
| `reason` | text | Yes | No | Optional moderation reason |
| `created_at` | timestamp | Yes | No | Revision timestamp |
| `updated_at` | timestamp | Yes | No | Last update timestamp |

#### Indexes

- Primary key on `id`
- Foreign key index on `comment_id`
- Composite index on `editor_type` and `editor_id` (polymorphic)

#### Foreign Keys

- `comment_id` references `id` on `comments` table with cascade on delete

#### Usage

**Top-level comment:**
```php
[
    'id' => 1,
    'commentable_type' => 'App\Models\Post',
    'commentable_id' => 5,
    'commenter_type' => 'App\Models\User',
    'commenter_id' => 10,
    'reply_id' => null, // No parent
    'content' => 'Great post!',
    'approved' => false,
]
```

**Reply to a comment:**
```php
[
    'id' => 2,
    'commentable_type' => null, // Replies don't link to commentable
    'commentable_id' => null,
    'commenter_type' => 'App\Models\User',
    'commenter_id' => 15,
    'reply_id' => 1, // Parent comment ID
    'content' => 'Thank you!',
    'approved' => false,
]
```

---

### reactions

Stores reactions (likes, loves, etc.) to comments and replies.

#### Columns

| Column | Type | Nullable | Indexed | Description |
|--------|------|----------|---------|-------------|
| `id` | bigInteger | No | Primary | Auto-incrementing primary key |
| `owner_type` | string | No | Yes | Polymorphic type of the entity reacting |
| `owner_id` | bigInteger | No | Yes | Polymorphic ID of the entity reacting |
| `comment_id` | bigInteger | No | Foreign | Foreign key to comments table |
| `type` | string | No | No | Reaction type identifier (e.g., 'like', 'love') |
| `created_at` | timestamp | Yes | No | Creation timestamp |
| `updated_at` | timestamp | Yes | No | Last update timestamp |

#### Indexes

- Primary key on `id`
- Composite index on `owner_type` and `owner_id` (polymorphic)
- Foreign key index on `comment_id`

#### Foreign Keys

- `comment_id` references `id` on `comments` table with cascade on delete

#### Usage

```php
[
    'id' => 1,
    'owner_type' => 'App\Models\User',
    'owner_id' => 10,
    'comment_id' => 1,
    'type' => 'like',
]
```

---

## Relationships

### Polymorphic Relationships

#### commentable (comments table)

- **Type:** Polymorphic (One to Many)
- **Owner:** Any model using `Commentable` trait
- **Related:** `Comment` model
- **Columns:** `commentable_type`, `commentable_id`

**Example:**
```php
// Post has many comments
$post->comments; // All comments on this post

// Comment belongs to post
$comment->commentable; // The post this comment is on
```

#### commenter (comments table)

- **Type:** Polymorphic (One to Many)
- **Owner:** Any model using `Commenter` trait
- **Related:** `Comment` and `Reply` models
- **Columns:** `commenter_type`, `commenter_id`

**Example:**
```php
// User has many comments
$user->comments; // All comments created by this user

// Comment belongs to user
$comment->commenter; // The user who created this comment
```

#### owner (reactions table)

- **Type:** Polymorphic (One to Many)
- **Owner:** Any model (typically User)
- **Related:** `Reaction` model
- **Columns:** `owner_type`, `owner_id`

**Example:**
```php
// User has many reactions
$user->morphMany(Reaction::class, 'owner');

// Preferred reaction owner relation
$reaction->owner; // The user who created this reaction

// Backward-compatible user relation
$reaction->user;
```

### Standard Relationships

#### replies (comments table)

- **Type:** One to Many (self-referencing)
- **Parent:** `Comment` or `Reply`
- **Child:** `Reply`
- **Foreign Key:** `reply_id`

**Example:**
```php
$comment->replies; // All replies to this comment
$reply->comment; // The parent comment (via BelongsTo)
```

#### reactions (comments table → reactions table)

- **Type:** One to Many
- **Parent:** `Comment` or `Reply`
- **Child:** `Reaction`
- **Foreign Key:** `comment_id`

**Example:**
```php
$comment->reactions; // All reactions to this comment
$reaction->comment; // The comment this reaction is on
```

---

## Migration File

The package provides a single migration file that creates both tables:

**File:** `database/migrations/create_commentable_table.php`

**Publish command:**
```bash
php artisan vendor:publish --tag="commentable-migrations"
```

**Full migration:**
```php
Schema::create(config('commentable.comment_table', 'comments'), function (Blueprint $table): void {
    $table->id();
    $table->nullableMorphs('commentable');
    $table->nullableMorphs('commenter');
    $table->unsignedBigInteger('reply_id')->nullable()->index();
    $table->longText('content');
    $table->boolean('approved')->default(false)->index();
    $table->foreign('reply_id')
        ->references('id')
        ->on(config('commentable.comment_table', 'comments'))
        ->cascadeOnDelete();
    $table->timestamps();
});

Schema::create(config('commentable.reaction_table', 'reactions'), function (Blueprint $table): void {
    $table->id();
    $table->morphs('owner');
    $table->foreignId('comment_id')
        ->constrained(config('commentable.comment_table', 'comments'))
        ->cascadeOnDelete();
    $table->string('type');
    $table->timestamps();
});
```

Rollback uses the same configured table names:

```php
Schema::dropIfExists(config('commentable.reaction_table', 'reactions'));
Schema::dropIfExists(config('commentable.comment_table', 'comments'));
```

---

## Query Examples

### Find all comments on a specific post

```php
use Akira\Commentable\Models\Comment;
use App\Models\Post;

$postComments = Comment::where('commentable_type', Post::class)
    ->where('commentable_id', 1)
    ->whereNull('reply_id') // Only top-level comments
    ->get();
```

### Find all comments by a user

```php
$userComments = Comment::where('commenter_type', User::class)
    ->where('commenter_id', 1)
    ->get();
```

### Find all replies to a comment

```php
$replies = Comment::where('reply_id', 1)->get();
// Or using relationship:
$comment = Comment::find(1);
$replies = $comment->replies;
```

### Count reactions by type

```php
$reactionCounts = Reaction::where('comment_id', 1)
    ->select('type', DB::raw('count(*) as count'))
    ->groupBy('type')
    ->get();
```

### Get approved comments with eager loading

```php
$post = Post::with([
    'comments' => fn($query) => $query->where('approved', true),
    'comments.commenter',
    'comments.replies.commenter',
    'comments.reactions'
])->find(1);
```

**Previous:** [API Reference](05-api-reference.md) | **Next:** [Testing](07-testing.md)
