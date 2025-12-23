# Contributing to Laravel Commentable

Thank you for considering contributing to Laravel Commentable! We welcome contributions from the community to help make this package better.

## Code of Conduct

This project adheres to the Laravel community standards. Please be respectful and constructive in all interactions.

## How Can I Contribute?

### Reporting Bugs

Before creating bug reports, please check the existing issues to avoid duplicates. When creating a bug report, include:

- A clear and descriptive title
- Steps to reproduce the behavior
- Expected behavior vs actual behavior
- Laravel and PHP versions
- Package version
- Any relevant code snippets or error messages

### Suggesting Enhancements

Enhancement suggestions are tracked as GitHub issues. When creating an enhancement suggestion, include:

- A clear and descriptive title
- A detailed description of the proposed functionality
- Why this enhancement would be useful to most users
- Examples of how the feature would be used

### Pull Requests

1. Fork the repository
2. Create a new branch from `main` for your feature or fix
3. Write tests for your changes
4. Ensure all tests pass
5. Update documentation if needed
6. Follow the coding standards below
7. Submit a pull request with a clear description of the changes

## Development Setup

### Prerequisites

- PHP 8.4 or higher
- Composer
- Laravel 12.0 or higher

### Installation

```bash
# Clone your fork
git clone https://github.com/YOUR-USERNAME/laravel-commentable.git
cd laravel-commentable

# Install dependencies
composer install

# Run tests
composer test
```

## Coding Standards

This package follows Laravel coding standards and conventions:

### Code Style

- Use PSR-12 coding standard
- Run Laravel Pint before committing:

```bash
composer lint
```

- Use strict types declaration in all PHP files:

```php
<?php

declare(strict_types=1);
```

### Type Safety

- All methods must have return type declarations
- All parameters must have type declarations
- Run PHPStan for static analysis:

```bash
composer test:types
```

### Documentation

- Document all public methods with PHPDoc blocks
- Include `@param` and `@return` annotations
- Add `@throws` for exceptions
- Provide usage examples for complex features

Example:

```php
/**
 * Creates a comment on a commentable model.
 *
 * @param Model $model The model to comment on
 * @param string $comment The comment content
 * @return CommentContract The created comment instance
 * @throws Exception If the model doesn't use the Commentable trait
 */
public function comment(Model $model, string $comment): CommentContract
{
    // Implementation
}
```

## Testing

### Running Tests

```bash
# Run all tests
composer test

# Run specific test suite
./vendor/bin/pest tests/Fixtures/Feature/CommenterTest.php

# Run tests with coverage
composer test:coverage

# Run type coverage
composer test:type-coverage
```

### Writing Tests

- Write tests using Pest PHP
- Follow Arrange-Act-Assert pattern
- Use descriptive test names
- Cover edge cases and error conditions
- Aim for high code coverage

Example:

```php
test('user can create a comment on a post', function () {
    $user = User::create(['name' => 'John Doe']);
    $post = Post::create(['title' => 'Test Post']);

    $comment = $user->comment($post, 'Great post!');

    expect($comment)
        ->toBeInstanceOf(Comment::class)
        ->and($comment->content)->toBe('Great post!');
});
```

## Git Workflow

### Commit Messages

Follow the Conventional Commits specification:

- `feat:` - New feature
- `fix:` - Bug fix
- `docs:` - Documentation changes
- `style:` - Code style changes (formatting, etc)
- `refactor:` - Code refactoring
- `test:` - Adding or updating tests
- `chore:` - Maintenance tasks

Examples:

```
feat: add reaction system for comments
fix: prevent duplicate reactions from same user
docs: update installation instructions
test: add tests for nested reply deletion
```

### Branch Naming

- `feature/description` - New features
- `fix/description` - Bug fixes
- `docs/description` - Documentation updates

Examples:

```
feature/add-soft-deletes
fix/cascading-delete-issue
docs/update-api-reference
```

## Code Review Process

1. All submissions require review
2. Maintainers will review your pull request
3. Address any feedback or requested changes
4. Once approved, a maintainer will merge your PR

## Release Process

Releases are managed by the maintainers:

1. Version numbers follow [Semantic Versioning](https://semver.org/)
2. Changelog is updated for each release
3. Tags are created for each version

## Questions?

If you have questions about contributing, feel free to:

- Open a discussion on GitHub
- Review existing issues and pull requests
- Check the documentation in the `/docs` folder

Thank you for contributing to Laravel Commentable!
