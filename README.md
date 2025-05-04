# Laravel Commentable
---
[![Latest Version on Packagist](https://img.shields.io/packagist/v/akira/laravel-commentable.svg)](https://packagist.org/packages/akira/laravel-commentable)
[![Total Downloads](https://img.shields.io/packagist/dt/akira/laravel-commentable.svg)](https://packagist.org/packages/akira/laravel-commentable)
[![PHPStan Level](https://img.shields.io/badge/phpstan-level%209-brightgreen.svg)](https://phpstan.org)
[![License](https://img.shields.io/packagist/l/akira/laravel-commentable.svg)](https://github.com/akira-io/laravel-commentable/blob/main/LICENSE)

**Commentable** is a lightweight and flexible comment system package designed to seamlessly integrate into any Laravel project. Whether you’re building a blog, a forum, or a platform like [HUNTER](https://devhunter.cv) – this package makes it incredibly easy to make any model commentable.

## Installation

You can install the package via composer:

```bash
composer require akira-io/commentable
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --tag="commentable-migrations"
php artisan migrate
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="commentable-config"
```

## Documentation

You'll find installation instructions and full documentation on [Commentable website](https://commentable.akira-io.com).

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Kidiatoliny](https://github.com/akira-io)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
