# Testing Laravel Repository

This guide explains how to test the Laravel Repository package.

## Requirements

- PHP 8.2 or higher
- Composer

## Installation

```bash
composer install
```

## Running Tests

### All Tests

```bash
composer test
# or
./vendor/bin/phpunit
```

### Tests with Coverage

```bash
composer test-coverage
# or
./vendor/bin/phpunit --coverage-html coverage
```

### Verbose Output

```bash
./vendor/bin/phpunit --testdox
```

## Test Structure

- `tests/Unit/` - Unit tests for individual components
- `tests/Feature/` - Integration tests (when added)

## Adding New Tests

1. Create test files in `tests/Unit/` or `tests/Feature/`
2. Extend `Orchestra\Testbench\TestCase` for Laravel-specific tests
3. Use the `LaravelRepositoryServiceProvider` in your test setup:

```php
protected function getPackageProviders($app)
{
    return [
        \Vdhoangson\LaravelRepository\LaravelRepositoryServiceProvider::class,
    ];
}
```

## CI/CD

The package includes GitHub Actions workflow that:

- Tests on PHP 8.2 and 8.3
- Tests with Laravel 10.x and 11.x
- Runs on Ubuntu latest
- Automatically runs on push/PR to main/develop branches
