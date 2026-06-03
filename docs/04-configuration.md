# Configuration

Laravel Commentable can be customized through the `config/commentable.php` configuration file.

## Publishing the Configuration

```bash
php artisan vendor:publish --tag="commentable-config"
```

## Configuration Options

### Table Names

Customize the database table names used by the package:

```php
return [
    'comment_table' => 'comments',
    'reaction_table' => 'reactions',
];
```

**Default:** `comments` and `reactions`

**Use case:** If you need to avoid naming conflicts with existing tables or prefer different naming conventions.

**Example:**
```php
'comment_table' => 'user_comments',
'reaction_table' => 'comment_reactions',
```

Remember to publish and modify the migration file before running `php artisan migrate`.

### User Foreign Key

Configure the foreign key name for user relationships:

```php
return [
    'user_foreign_key' => 'user_id',
];
```

**Default:** `user_id`

**Use case:** If your application uses a different convention for user foreign keys.

**Example:**
```php
'user_foreign_key' => 'author_id',
```

### Model Overrides

Override the default model classes used by the package:

```php
return [
    'models' => [
        'comment' => \Akira\Commentable\Models\Comment::class,
        'reaction' => \Akira\Commentable\Models\Reaction::class,
    ],
];
```

**Use case:** Extend the package base models with your own implementations. Custom comment models must extend `Akira\Commentable\Models\Message`; custom reaction models must extend `Akira\Commentable\Models\BaseReaction`.

**Example:**

Create your custom Comment model:

```php
<?php

namespace App\Models;

use Akira\Commentable\Models\Message;

class Comment extends Message
{
    protected $appends = ['is_edited', 'excerpt'];

    public function getIsEditedAttribute(): bool
    {
        return $this->created_at->ne($this->updated_at);
    }

    public function getExcerptAttribute(): string
    {
        return str($this->content)->limit(100);
    }
}
```

Create your custom Reaction model:

```php
<?php

namespace App\Models;

use Akira\Commentable\Models\BaseReaction;

class Reaction extends BaseReaction
{
    protected $appends = ['label'];

    public function getLabelAttribute(): string
    {
        return str($this->type)->headline();
    }
}
```

Update the configuration:

```php
'models' => [
    'comment' => \App\Models\Comment::class,
    'reaction' => \App\Models\Reaction::class,
],
```

The package relationships read these configured classes when creating comments and loading reactions:

```php
$post->comments()->create([...]);     // App\Models\Comment
$comment->reactions()->create([...]); // App\Models\Reaction
```

## Complete Configuration File

```php
<?php

return [

    'comment_table' => 'comments',

    'reaction_table' => 'reactions',

    /*
    |--------------------------------------------------------------------------
    | User Foreign Key
    |--------------------------------------------------------------------------
    |
    | This is the name of the foreign key column in the followables table that
    | will reference the user model. By default, it is set to 'user_id'.
    |
    */
    'user_foreign_key' => 'user_id',

    'models' => [
        'comment' => \Akira\Commentable\Models\Comment::class,
        'reaction' => \Akira\Commentable\Models\Reaction::class,
    ],

];
```

## Environment-Specific Configuration

You can use environment variables for dynamic configuration:

```php
return [
    'comment_table' => env('COMMENTABLE_TABLE', 'comments'),
    'reaction_table' => env('COMMENTABLE_REACTION_TABLE', 'reactions'),
];
```

Then in your `.env` file:

```env
COMMENTABLE_TABLE=user_comments
COMMENTABLE_REACTION_TABLE=user_reactions
```

**Previous:** [Advanced Features](03-advanced-features.md) | **Next:** [API Reference](05-api-reference.md)
