# Contributing

Thanks for your interest in contributing to curl-impersonate-php!

## Getting Started

1. Fork the repository
2. Clone your fork: `git clone https://github.com/YOUR_USERNAME/curl-impersonate-php.git`
3. Install dependencies: `composer install`
4. Create a branch: `git checkout -b feature/your-feature`

## Requirements

- PHP 7.4+
- [curl-impersonate](https://github.com/lwthiker/curl-impersonate) installed locally
- Composer

## Running Tests

```bash
composer test
# or
vendor/bin/phpunit
```

## Code Style

- Follow PSR-12
- Use PHP 7.4+ type declarations
- Run `find src tests -name "*.php" -exec php -l {} \;` to check syntax

## Pull Requests

- Keep PRs focused — one feature or fix per PR
- Update CHANGELOG.md
- Add tests for new features
- Ensure all CI checks pass

## Reporting Issues

- Use the [issue tracker](https://github.com/kelvinzer0/curl-impersonate-php/issues)
- Include PHP version, curl-impersonate version, and OS
- Provide a minimal reproduction example
