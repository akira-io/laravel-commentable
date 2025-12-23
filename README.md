# Laravel Commentable

[![Latest Version on Packagist](https://img.shields.io/packagist/v/akira/laravel-commentable.svg)](https://packagist.org/packages/akira/laravel-commentable)
[![Total Downloads](https://img.shields.io/packagist/dt/akira/laravel-commentable.svg)](https://packagist.org/packages/akira/laravel-commentable)
[![PHPStan Level](https://img.shields.io/badge/phpstan-level%209-brightgreen.svg)](https://phpstan.org)
[![License](https://img.shields.io/packagist/l/akira/laravel-commentable.svg)](https://github.com/akira-io/laravel-commentable/blob/main/LICENSE)

A lightweight and flexible comment system for Laravel applications. Add threaded comments, replies, reactions, and moderation to any Eloquent model with minimal configuration.

## Features

- **Polymorphic Comments** - Make any model commentable with a single trait
- **Threaded Replies** - Support for unlimited nested reply depth
- **Reaction System** - Built-in support for likes, reactions, and custom types
- **Comment Moderation** - Approval workflow for managing user-generated content
- **Flexible Authorization** - Customizable permission logic for comment operations
- **Type Safe** - Full PHP 8.4 type declarations and PHPStan level 9 compliance
- **Well Tested** - Comprehensive test coverage with Pest PHP
- **Developer Friendly** - Clean API with extensive documentation

## Requirements

- PHP 8.4 or higher
- Laravel 12.0 or higher

## Installation

Install the package via Composer:

```bash
composer require akira/laravel-commentable
```

Publish and run the migrations:

```bash
php artisan vendor:publish --tag="commentable-migrations"
php artisan migrate
```

Optionally, publish the configuration file:

```bash
php artisan vendor:publish --tag="commentable-config"
```

## Quick Start

### Make a Model Commentable

Add the `Commentable` trait to any model that should receive comments:

```php
use Akira\Commentable\Concerns\Commentable;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use Commentable;
}
```

### Make a Model a Commenter

Add the `Commenter` trait to models that can create comments (typically your User model):

```php
use Akira\Commentable\Concerns\Commenter;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Commenter;
}
```

### Create Comments

```php
$user = User::find(1);
$post = Post::find(1);

// Create a comment
$comment = $user->comment($post, 'This is a great post!');

// Reply to a comment
$reply = $user->reply($comment, 'Thanks for your feedback!');

// Nested replies
$nestedReply = $user->reply($reply, 'You are welcome!');
```

### Retrieve Comments

```php
// Get all comments on a post
$comments = $post->comments;

// Get only approved comments
$approvedComments = $post->comments()->where('approved', true)->get();

// Get replies to a comment
$replies = $comment->replies;

// Get the commenter
$commenter = $comment->commenter;
```

### Delete Comments

```php
// Delete own comment (with authorization check)
try {
    $user->deleteComment($comment);
} catch (\Akira\Commentable\Exceptions\DeleteCommentNotAllowedException $e) {
    // Handle unauthorized deletion
}

// Force delete (bypass authorization)
$user->forceDeleteComment($comment);
```

### Add Reactions

```php
use Akira\Commentable\Models\Reaction;

// Create a reaction
Reaction::create([
    'comment_id' => $comment->id,
    'owner_type' => User::class,
    'owner_id' => $user->id,
    'type' => 'like',
]);

// Get reactions
$reactions = $comment->reactions;
```

## Documentation

Comprehensive documentation is available at:

**[https://packages.akira-io.com/packages/laravel-commentable](https://packages.akira-io.com/packages/laravel-commentable)**

### Documentation Topics

- [Installation Guide](https://packages.akira-io.com/packages/laravel-commentable/installation)
- [Basic Usage](https://packages.akira-io.com/packages/laravel-commentable/basic-usage)
- [Advanced Features](https://packages.akira-io.com/packages/laravel-commentable/advanced-features)
- [Configuration](https://packages.akira-io.com/packages/laravel-commentable/configuration)
- [API Reference](https://packages.akira-io.com/packages/laravel-commentable/api-reference)
- [Database Schema](https://packages.akira-io.com/packages/laravel-commentable/database-schema)
- [Testing Guide](https://packages.akira-io.com/packages/laravel-commentable/testing)
- [Code Examples](https://packages.akira-io.com/packages/laravel-commentable/examples)
- [Troubleshooting](https://packages.akira-io.com/packages/laravel-commentable/troubleshooting)

## Use Cases

Laravel Commentable is perfect for:

- Blog comment systems
- Forum discussions
- Product reviews and ratings
- Ticket and support systems
- Social media platforms
- Documentation feedback
- Q&A platforms
- Community engagement features

## Testing

Run the test suite:

```bash
composer test
```

Run individual test types:

```bash
composer test:lint          # Code style
composer test:types         # Static analysis
composer test:coverage      # Test coverage
composer test:type-coverage # Type coverage
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

We welcome contributions! Please see [CONTRIBUTING](CONTRIBUTING.md) for details on how to contribute to this project.

## Security Vulnerabilities

If you discover a security vulnerability, please review our [Security Policy](SECURITY.md) for information on how to report it responsibly.

## Credits

- [Kidiatoliny](https://github.com/kidiatoliny)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
