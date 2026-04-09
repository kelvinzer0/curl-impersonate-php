<div align="center">

# curl-impersonate-php

**Stop getting blocked. Start impersonating real browsers.**

[![Latest Version](https://img.shields.io/github/v/release/kelvinzer0/curl-impersonate-php?style=flat-square&color=blue)](https://github.com/kelvinzer0/curl-impersonate-php/releases)
[![CI](https://img.shields.io/github/actions/workflow/status/kelvinzer0/curl-impersonate-php/ci.yml?style=flat-square&label=build)](https://github.com/kelvinzer0/curl-impersonate-php/actions)
[![PHP Version](https://img.shields.io/badge/php-%3E%3D7.4-8892BF?style=flat-square&logo=php&logoColor=white)](https://php.net)
[![License](https://img.shields.io/github/license/kelvinzer0/curl-impersonate-php?style=flat-square)](LICENSE)
[![Downloads](https://img.shields.io/packagist/dt/kelvinzer0/curl-impersonate-php?style=flat-square&color=green)](https://packagist.org/packages/kelvinzer0/curl-impersonate-php)
[![Stars](https://img.shields.io/github/stars/kelvinzer0/curl-impersonate-php?style=social)](https://github.com/kelvinzer0/curl-impersonate-php)

PHP wrapper for [curl-impersonate](https://github.com/lwthiker/curl-impersonate) — execute HTTP requests that **mimic real browser TLS fingerprints**, bypassing Cloudflare, Akamai, Datadome, and other anti-bot systems.

[Installation](#installation) · [Quick Start](#quick-start) · [Browser Presets](#browser-presets) · [API](#api) · [FAQ](#faq)

</div>

---

## Why This Exists

Regular `curl` has a distinct TLS fingerprint. Anti-bot systems detect it instantly.

**curl-impersonate** uses a patched libcurl that produces the **exact same TLS ClientHello** as Chrome, Firefox, or Safari. Your requests become indistinguishable from a real browser.

This library wraps it in a clean PHP API — no shell scripting required.

```
Regular curl    →  TLS fingerprint = "bot"     →  🚫 403 Blocked
curl-impersonate →  TLS fingerprint = "Chrome"  →  ✅ 200 OK
```

## Installation

```bash
composer require kelvinzer0/curl-impersonate-php
```

### Install curl-impersonate binary

```bash
# Linux x86_64 (v0.6.1)
curl -L https://github.com/lwthiker/curl-impersonate/releases/download/v0.6.1/curl-impersonate-v0.6.1.x86_64-linux-gnu.tar.gz | tar xz
export LD_LIBRARY_PATH="$PWD:$LD_LIBRARY_PATH"

# macOS (Homebrew)
brew install curl-impersonate
```

See [curl-impersonate releases](https://github.com/lwthiker/curl-impersonate/releases) for all builds.

## Quick Start

```php
<?php
require 'vendor/autoload.php';

use CurlImpersonate\CurlImpersonate;

$curl = new CurlImpersonate();

// Option 1: Use a browser preset (auto-detects binary path)
$response = $curl
    ->setBrowser(CurlImpersonate::BROWSER_CHROME)
    ->setopt(CurlImpersonate::OPT_URL, 'https://example.com')
    ->setopt(CurlImpersonate::OPT_METHOD, 'GET')
    ->exec();

echo $response;
```

```php
<?php
// Option 2: Point to a specific binary
$curl = new CurlImpersonate();
$response = $curl
    ->setopt(CurlImpersonate::OPT_URL, 'https://example.com')
    ->setopt(CurlImpersonate::OPT_ENGINE, '/path/to/curl_chrome116')
    ->exec();
```

## Browser Presets

| Preset | Constant | Mimics |
|---|---|---|
| Chrome 116 | `BROWSER_CHROME` | Chrome 116 on Windows 10 |
| Chrome 120 | `BROWSER_CHROME_120` | Chrome 120 on Windows 10 |
| Firefox 102 | `BROWSER_FIREFOX` | Firefox 102 ESR on Linux |
| Firefox 117 | `BROWSER_FIREFOX_117` | Firefox 117 on Linux |
| Safari 15.3 | `BROWSER_SAFARI` | Safari 15.3 on macOS Monterey |
| Safari 17.0 | `BROWSER_SAFARI_17` | Safari 17.0 on macOS Sonoma |
| Edge 99 | `BROWSER_EDGE` | Edge 99 on Windows |

```php
$curl->setBrowser(CurlImpersonate::BROWSER_CHROME_120);
```

## API

### Options

```php
$curl->setopt(int $option, mixed $value): self
```

| Constant | Description | Example |
|---|---|---|
| `OPT_URL` | Target URL | `'https://api.example.com/data'` |
| `OPT_METHOD` | HTTP method | `'POST'` |
| `OPT_POSTFIELDS` | Request body (array → JSON) | `['key' => 'value']` |
| `OPT_HTTP_HEADERS` | Headers array | `['Authorization: Bearer xxx']` |
| `OPT_HEADER` | Include response headers | `true` |
| `OPT_ENGINE` | Path to curl-impersonate binary | `'/usr/local/bin/curl_chrome116'` |
| `OPT_PROXY` | Proxy (HTTP or SOCKS5) | `'socks5://127.0.0.1:1080'` |
| `OPT_TIMEOUT` | Request timeout (seconds) | `30` |
| `OPT_FOLLOW_LOCATION` | Follow redirects | `true` |
| `OPT_VERIFY_SSL` | Verify SSL certificates | `true` |
| `OPT_COOKIEFILE` | Read cookies from file | `'/tmp/cookies.txt'` |
| `OPT_COOKIEJAR` | Save cookies to file | `'/tmp/cookies.txt'` |

### Methods

```php
// Execute and get response
$response = $curl->exec(): ?string

// Execute with streaming
$curl->execStream(): self
$chunk = $curl->readStream(4096): string|false
$curl->closeStream(): void

// Build command (for debugging)
$command = $curl->buildCommand(): string

// Reset for reuse
$curl->reset(): self
```

## Examples

### POST JSON with authentication

```php
$curl = new CurlImpersonate();
$response = $curl
    ->setBrowser(CurlImpersonate::BROWSER_CHROME)
    ->setopt(CurlImpersonate::OPT_URL, 'https://api.example.com/users')
    ->setopt(CurlImpersonate::OPT_METHOD, 'POST')
    ->setopt(CurlImpersonate::OPT_POSTFIELDS, ['name' => 'Kelvin', 'role' => 'admin'])
    ->setopt(CurlImpersonate::OPT_HTTP_HEADERS, [
        'Authorization: Bearer YOUR_TOKEN',
        'Content-Type: application/json',
    ])
    ->exec();
```

### SOCKS5 proxy

```php
$curl = new CurlImpersonate();
$response = $curl
    ->setBrowser(CurlImpersonate::BROWSER_FIREFOX)
    ->setopt(CurlImpersonate::OPT_URL, 'https://check.torproject.org/api/ip')
    ->setopt(CurlImpersonate::OPT_PROXY, 'socks5h://127.0.0.1:9050')
    ->exec();
```

### Stream large responses

```php
$curl = new CurlImpersonate();
$curl
    ->setBrowser(CurlImpersonate::BROWSER_SAFARI)
    ->setopt(CurlImpersonate::OPT_URL, 'https://example.com/large-file')
    ->execStream();

while ($chunk = $curl->readStream(8192)) {
    echo $chunk; // process chunk by chunk
}
```

### Scrape with cookies

```php
$curl = new CurlImpersonate();

// Step 1: Login and save cookies
$curl
    ->setBrowser(CurlImpersonate::BROWSER_CHROME)
    ->setopt(CurlImpersonate::OPT_URL, 'https://example.com/login')
    ->setopt(CurlImpersonate::OPT_METHOD, 'POST')
    ->setopt(CurlImpersonate::OPT_POSTFIELDS, ['user' => 'admin', 'pass' => 'secret'])
    ->setopt(CurlImpersonate::OPT_COOKIEJAR, '/tmp/cookies.txt')
    ->exec();

// Step 2: Use saved cookies
$response = $curl
    ->reset()
    ->setBrowser(CurlImpersonate::BROWSER_CHROME)
    ->setopt(CurlImpersonate::OPT_URL, 'https://example.com/dashboard')
    ->setopt(CurlImpersonate::OPT_COOKIEFILE, '/tmp/cookies.txt')
    ->exec();
```

## FAQ

### Where do I get the curl-impersonate binary?

Download from [releases](https://github.com/lwthiker/curl-impersonate/releases) or install via package manager:

```bash
# macOS
brew install curl-impersonate

# Arch Linux
yay -S curl-impersonate

# Docker
docker pull lwthiker/curl-impersonate:0.6.1
```

### How do I use proxies?

Use `OPT_PROXY` with any proxy type curl supports:

```php
// HTTP proxy
->setopt(CurlImpersonate::OPT_PROXY, 'http://user:pass@proxy.example.com:8080')

// SOCKS5 proxy
->setopt(CurlImpersonate::OPT_PROXY, 'socks5://127.0.0.1:1080')

// SOCKS5 with DNS resolution through proxy
->setopt(CurlImpersonate::OPT_PROXY, 'socks5h://127.0.0.1:1080')
```

### I get "command not found" errors

Make sure `curl-impersonate` binaries are in your PATH or use the full path:

```php
// Option 1: setBrowser with explicit path
->setBrowser(CurlImpersonate::BROWSER_CHROME, '/opt/curl-impersonate/bin')

// Option 2: direct engine path
->setopt(CurlImpersonate::OPT_ENGINE, '/opt/curl-impersonate/bin/curl_chrome116')
```

### Does this work on shared hosting?

No. This library requires shell access to execute the `curl-impersonate` binary. It works on VPS, dedicated servers, Docker containers, and any environment where you can install system packages.

## Comparison

| Feature | Native curl | Guzzle | This library |
|---|---|---|---|
| TLS fingerprint | ❌ Bot detection | ❌ Bot detection | ✅ Real browser |
| HTTP/2 fingerprint | ❌ | ❌ | ✅ Real browser |
| Ja3 fingerprint | ❌ | ❌ | ✅ Matched |
| PHP API | ❌ Raw resource | ✅ Clean | ✅ Clean |
| Proxy support | ✅ | ✅ | ✅ |
| Streaming | ✅ | ✅ | ✅ |

## Who Uses This

- Web scraping at scale without IP rotation
- SEO monitoring tools
- Price comparison services
- API integration with anti-bot protected endpoints
- Security research and testing

## Contributing

See [CONTRIBUTING.md](CONTRIBUTING.md).

## License

[MIT](LICENSE) © Kelvin Yuli Andrian

---

<div align="center">

**⭐ Star this repo if it saved you from 403s**

[Report Bug](https://github.com/kelvinzer0/curl-impersonate-php/issues) · [Request Feature](https://github.com/kelvinzer0/curl-impersonate-php/issues/new)

</div>
