# Installation

Laravel Commentable is a lightweight package that adds a flexible commenting system to any Laravel application.

## Requirements

- PHP 8.4 or higher
- Laravel 12.0 or higher

## Installation Steps

### Install via Composer

```bash
composer require akira/laravel-commentable
```

The package will automatically register its service provider through Laravel's package auto-discovery.

### Publish Migrations

```bash
php artisan vendor:publish --tag="commentable-migrations"
```

This creates a migration file in your `database/migrations` directory that sets up two tables:

- `comments` - Stores all comments and replies
- `reactions` - Stores user reactions to comments

### Run Migrations

```bash
php artisan migrate
```

This creates the following database structure:

**comments table:**
- `id` - Primary key
- `commentable_type` and `commentable_id` - Polymorphic relation to any model
- `commenter_type` and `commenter_id` - Polymorphic relation to the user/entity making the comment
- `reply_id` - Self-referencing foreign key for nested replies
- `content` - The comment text (longText)
- `approved` - Boolean flag for moderation
- `timestamps`

**reactions table:**
- `id` - Primary key
- `owner_type` and `owner_id` - Polymorphic relation to the reacting entity
- `comment_id` - Foreign key to comments table
- `type` - String identifier for reaction type
- `timestamps`

### Publish Configuration (Optional)

```bash
php artisan vendor:publish --tag="commentable-config"
```

This creates `config/commentable.php` where you can customize:

- Table names
- Model class references
- User foreign key name

## Verification

To verify installation, check that the migrations have run successfully:

```bash
php artisan migrate:status
```

You should see `create_commentable_table` with a "Ran" status.

**Previous:** [Roadmap](00-roadmap.md) | **Next:** [Basic Usage](02-basic-usage.md)
