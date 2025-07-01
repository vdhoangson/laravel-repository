# Changelog

All notable changes to `laravel-repository` will be documented in this file.

## 2.0.0 - 2025-06-24

### Added

- Support for Laravel 12.x
- Support for PHP 8.3
- GitHub Actions workflow for automated testing
- Better type hints with return types in Service Provider
- Tagged config publishing support
- PHPUnit 10/11 support with updated configuration

### Changed

- **BREAKING**: Minimum PHP version raised to 8.2
- **BREAKING**: Updated to support Laravel 10.x and 12.x 
- Updated PHPUnit configuration to use new format
- Improved Service Provider with proper type hints and console detection
- Updated composer.json with proper Laravel dependencies

### Removed

- **BREAKING**: Dropped support for PHP 8.0 and 8.1
- **BREAKING**: Dropped support for Laravel 9.x

### Migration Guide from 1.x to 2.0

1. Ensure you're running PHP 8.2 or higher
2. Update your Laravel application to 10.x or 12.x
3. Update the package:
   ```bash
   composer update vdhoangson/laravel-repository
   ```
4. No code changes required - the API remains the same

## 1.2.3 - Previous Release

### Added

- Repository pattern implementation
- Base repository with CRUD operations
- Criteria system for flexible queries
- Caching support
- Service provider for Laravel integration
