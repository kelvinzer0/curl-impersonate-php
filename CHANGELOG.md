# Changelog

All notable changes to this project will be documented in this file.

## [3.0.0] - 2026-04-09

### Added
- `setBrowser()` helper — pick a browser preset instead of raw binary path
- Proxy support (HTTP, SOCKS5) via `OPT_PROXY`
- `OPT_TIMEOUT`, `OPT_FOLLOW_LOCATION`, `OPT_VERIFY_SSL` options
- `reset()` method to reuse instance
- Auto-detect curl-impersonate installation path
- Proper exception handling with exit codes and stderr messages
- PHP 7.4+ type declarations
- PHPUnit test suite
- GitHub Actions CI (PHP 7.4–8.4)
- Contributing guide and changelog

### Changed
- Minimum PHP version raised from 5.4 to 7.4
- `exec()` now throws `RuntimeException` on failure (instead of returning null silently)
- Renamed constants: `CURLCMDOPT_*` → `OPT_*` (old names still work as aliases)

### Fixed
- POST data encoding for arrays/objects now uses JSON consistently

## [2.0.0] - 2024-01-15

### Added
- Streaming support via `execStream()` / `readStream()` / `closeStream()`
- Cookie file and cookie jar support

## [1.0.0] - 2023-12-01

### Added
- Initial release
- Basic HTTP requests with browser impersonation
- Chrome, Firefox, Safari, Edge support
- Custom headers, POST data, method override
