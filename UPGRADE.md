# Upgrade Guide

## From 1.x to 2.0

### Requirements Update

Laravel Repository 2.0 requires:

- **PHP 8.2+** (was 8.0+)
- **Laravel 10.x or 11.x** (was 9.x+)

### Step-by-step Upgrade

1. **Update PHP version** to 8.2 or higher in your environment

2. **Update Laravel** to version 10.x or 11.x if you haven't already

3. **Update the package**:

   ```bash
   composer update vdhoangson/laravel-repository
   ```

4. **Clear caches** (recommended):

   ```bash
   php artisan config:clear
   php artisan cache:clear
   ```

5. **Republish config** (optional):
   ```bash
   php artisan vendor:publish --tag=laravel-repository-config --force
   ```

### What's Changed

- **No breaking API changes** - your existing repository code will continue to work
- Improved type safety with return type declarations
- Better Laravel 11 compatibility
- Updated PHPUnit support for better testing

### Testing Your Upgrade

After upgrading, run your tests to ensure everything works:

```bash
php artisan test
```

If you encounter any issues, please check the [CHANGELOG.md](CHANGELOG.md) for detailed information about changes.

### Getting Help

If you need help with the upgrade process:

1. Check the [Issues](https://github.com/vdhoangson/laravel-repository/issues) on GitHub
2. Create a new issue with details about your problem
3. Include your PHP/Laravel versions and error messages
