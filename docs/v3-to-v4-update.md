# Updating from v3 to v4

Release 4.0.0 raises the PHP requirement and removes the backwards compatibility layer that let a
`Response` be read like the 1.x object.

## At a glance

| | v3 (3.1.1) | v4 (4.0.0) |
|---|---|---|
| PHP | `^8.1.0` | `^8.3.0` |
| `$response->body`, `->code`, `->headers` | deprecated, work | **removed** |
| `psr/http-message` | `^1.0` | `^2.0` |
| `laminas/laminas-diactoros` | `^2.24.0` | `^3.6.0` |

## Minimum supported PHP version raised

All Framework packages now require **PHP 8.3** or newer.

## The `Response` compatibility getters were removed

`Joomla\Http\Response` extends the PSR-7 response. Until 3.x it also carried a `__get()` that
mapped the 1.x property names onto the PSR-7 methods and emitted a deprecation notice. 4.0.0
removes it, along with the `@property-read` annotations.

```php
// Removed in 4.0.0
$response->body;
$response->code;
$response->headers;

// Use the PSR-7 API
(string) $response->getBody();
$response->getStatusCode();
$response->getHeaders();
```

Reading one of the old names now raises *Undefined property* and evaluates to `null`, so the
failure usually surfaces later as an error on `null`.

Two notes on the replacements:

* Prefer `(string) $response->getBody()` over `$response->getBody()->getContents()`. The cast
  rewinds the stream first, so reading the body twice still works; `getContents()` returns
  everything from the current position, which is the end after the first call.
* `getHeaders()` returns `array<string, string[]>` — a list of values per header, not a flat
  string. Use `$response->getHeaderLine('Content-Type')` when you want one string.

To find the call sites:

```bash
grep -rnE -- '->(body|code|headers)\b' src/
```

## PSR-7 2.0

`psr/http-message` moved from `^1.0` to `^2.0` and Diactoros from `^2.24` to `^3.6`. PSR-7 2.0 adds
return types to every interface method:

```php
// PSR-7 1.x
public function withStatus($code, $reasonPhrase = '')

// PSR-7 2.0
public function withStatus(int $code, string $reasonPhrase = ''): ResponseInterface
```

This affects you only if you implement a PSR-7 interface yourself — a custom response, or a
decorator around one. Consuming the objects needs no change.

`psr/http-client` stays at `^1.0`.

## Dependency changes

| Package | v3 (3.1.1) | v4 (4.0.0) |
|---|---|---|
| `php` | `^8.1.0` | `^8.3.0` |
| `psr/http-message` | `^1.0` | `^2.0` |
| `laminas/laminas-diactoros` | `^2.24.0` | `^3.6.0` |
| `joomla/uri` | `^3.0` | `^4.0` |
| `psr/http-client` | `^1.0` | unchanged |
| `composer/ca-bundle` | `^1.3.5` | unchanged |

`ext-curl` is listed in `suggest` for the cURL transport.
