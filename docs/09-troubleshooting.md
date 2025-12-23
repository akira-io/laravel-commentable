# Troubleshooting

Common issues and solutions when working with Laravel Commentable.

## Installation Issues

### Migration Already Exists Error

**Problem:**
```
Migration file already exists: create_commentable_table
```

**Solution:**
Check if the migration was previously published. Delete the existing migration from `database/migrations` or run the existing one:

```bash
php artisan migrate
```

---

### Table Not Found Error

**Problem:**
```
SQLSTATE[42S02]: Base table or view not found: 1146 Table 'database.comments' doesn't exist
```

**Solution:**
Ensure migrations have been run:

```bash
php artisan migrate
```

If using custom table names, verify your config matches the migration:

```bash
php artisan vendor:publish --tag="commentable-config"
```

Then update both `config/commentable.php` and the migration file to use the same table names.

---

## Usage Issues

### Trait Not Found Error

**Problem:**
```
Exception: The model must use the Commentable trait
```

**Solution:**
Ensure the target model uses the `Commentable` trait:

```php
use Akira\Commentable\Concerns\Commentable;

class Post extends Model
{
    use Commentable;
}
```

---

### DeleteCommentNotAllowedException

**Problem:**
User cannot delete comments they should be able to delete.

**Solution:**
Override the `approveCommentDeletion()` method in your `Commenter` model:

```php
public function approveCommentDeletion(CommentContract $comment): bool
{
    // Add custom authorization logic
    return $this->id === $comment->commenter_id 
        || $this->is_admin;
}
```

---

### Polymorphic Relationship Not Working

**Problem:**
```
Trying to get property of non-object when accessing $comment->commenter
```

**Solution:**
Ensure the `commenter_type` column contains the full class name with namespace:

```php
// Incorrect
'commenter_type' => 'User'

// Correct
'commenter_type' => \App\Models\User::class
```

If using custom morph maps, add them to `AppServiceProvider`:

```php
use Illuminate\Database\Eloquent\Relations\Relation;

public function boot()
{
    Relation::morphMap([
        'user' => \App\Models\User::class,
        'post' => \App\Models\Post::class,
    ]);
}
```

---

## Performance Issues

### Slow Comment Loading

**Problem:**
Pages with many comments load slowly.

**Solution:**
Use eager loading to reduce N+1 queries:

```php
$post = Post::with([
    'comments' => fn($q) => $q->where('approved', true),
    'comments.commenter',
    'comments.replies',
    'comments.replies.commenter'
])->find(1);
```

Add pagination for large comment threads:

```php
$comments = $post->comments()
    ->where('approved', true)
    ->latest()
    ->paginate(20);
```

---

### Too Many Database Queries

**Problem:**
Accessing reactions or replies causes many queries.

**Solution:**
Create query scopes for common loading patterns:

```php
// In Comment model
public function scopeWithFullThread($query)
{
    return $query->with([
        'commenter',
        'replies.commenter',
        'replies.replies.commenter',
        'reactions.owner'
    ]);
}

// Usage
$comments = $post->comments()->withFullThread()->get();
```

---

## Configuration Issues

### Custom Model Not Being Used

**Problem:**
Custom comment model defined in config is not being instantiated.

**Solution:**
Clear config cache:

```bash
php artisan config:clear
```

Ensure your config file is published and contains the correct class:

```php
'models' => [
    'comment' => \App\Models\Comment::class,
],
```

Verify the `Commentable` trait uses the config:

```php
public function comments(): MorphMany
{
    return $this->morphMany(
        config('commentable.models.comment'), 
        'commentable'
    );
}
```

---

### Config Changes Not Applying

**Problem:**
Changes to `config/commentable.php` are not taking effect.

**Solution:**
Clear all caches:

```bash
php artisan config:clear
php artisan cache:clear
php artisan optimize:clear
```

---

## Database Issues

### Foreign Key Constraint Fails

**Problem:**
```
SQLSTATE[23000]: Integrity constraint violation: 1451 Cannot delete or update a parent row
```

**Solution:**
This occurs when trying to delete a comment with existing replies. The migration includes `cascadeOnDelete()`, so ensure your migration is up to date:

```bash
php artisan migrate:fresh
```

Or handle deletion in your code:

```php
// Delete all replies first
$comment->replies()->delete();

// Then delete the comment
$comment->delete();
```

---

### Reply ID References Non-Existent Comment

**Problem:**
Orphaned replies after comment deletion.

**Solution:**
The migration includes a foreign key with cascade delete. Verify this exists:

```php
$table->foreign('reply_id')
    ->references('id')
    ->on(config('commentable.comment_table', 'comments'))
    ->cascadeOnDelete();
```

If missing, create a new migration:

```bash
php artisan make:migration add_cascade_delete_to_comments_table
```

```php
public function up()
{
    Schema::table('comments', function (Blueprint $table) {
        $table->dropForeign(['reply_id']);
        
        $table->foreign('reply_id')
            ->references('id')
            ->on('comments')
            ->cascadeOnDelete();
    });
}
```

---

## Testing Issues

### Commenter Not Found in Tests

**Problem:**
```
Call to undefined method commenter() on test model
```

**Solution:**
Ensure your test fixtures use the appropriate traits:

```php
use Akira\Commentable\Concerns\Commenter;
use Akira\Commentable\Concerns\Commentable;

class User extends Model
{
    use Commenter; // For users who comment
}

class Post extends Model
{
    use Commentable; // For models receiving comments
}
```

---

### Database Not Migrated in Tests

**Problem:**
```
SQLSTATE[42S02]: Base table or view not found: 1146 Table 'testing.comments' doesn't exist
```

**Solution:**
Ensure your test case runs migrations:

```php
use Illuminate\Foundation\Testing\RefreshDatabase;

class CommentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Load package migrations
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }
}
```

Or use Orchestra Testbench for package development:

```php
protected function getPackageProviders($app)
{
    return [
        \Akira\Commentable\CommentableServiceProvider::class,
    ];
}
```

---

## Authorization Issues

### Cannot Delete Own Comments

**Problem:**
`DeleteCommentNotAllowedException` thrown even for comment owner.

**Solution:**
Check that `commenter_id` matches the authenticated user ID:

```php
// Debug the issue
dd([
    'comment_owner' => $comment->commenter_id,
    'current_user' => auth()->id(),
    'match' => $comment->commenter_id === auth()->id()
]);
```

Ensure IDs are the same type (both integers or both strings):

```php
public function approveCommentDeletion(CommentContract $comment): bool
{
    return (int) $this->getKey() === (int) $comment->commenter_id;
}
```

---

## Common Mistakes

### Forgetting to Load Relationships

**Problem:**
Accessing `$comment->commenter->name` causes additional queries.

**Solution:**
Always eager load relationships when displaying multiple comments:

```php
$comments = Comment::with('commenter')->get();
```

---

### Not Checking Approval Status

**Problem:**
Unapproved comments appear in production.

**Solution:**
Always filter by approval status in user-facing queries:

```php
$comments = $post->comments()
    ->where('approved', true)
    ->get();
```

---

### Hardcoding Model Classes

**Problem:**
Custom models don't work because classes are hardcoded.

**Solution:**
Always use config values:

```php
// Incorrect
return $this->morphMany(Comment::class, 'commentable');

// Correct
return $this->morphMany(config('commentable.models.comment'), 'commentable');
```

---

## Getting Help

If you encounter issues not covered here:

1. Check the [GitHub Issues](https://github.com/akira-io/laravel-commentable/issues)
2. Review the [API Reference](05-api-reference.md) for method signatures
3. Examine the [Examples](08-examples.md) for implementation patterns
4. Enable query logging to debug database issues:

```php
\DB::enableQueryLog();

// Your code here

dd(\DB::getQueryLog());
```

**Previous:** [Examples](08-examples.md)
