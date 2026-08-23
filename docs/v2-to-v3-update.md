# Updating from v2 to v3

Release 3.0.0 raises the PHP requirement and reformats the codebase. **No public or protected
method signature changed**, so code written against 2.x keeps working on PHP 8.1.

## At a glance

| | v2 (2.0.x) | v3 (3.0.0) |
|---|---|---|
| PHP | `^7.2.5` | `^8.1.0` |
| Public API | — | unchanged |
| Coding style | Joomla Coding Standard | PSR-12 |

## Minimum supported PHP version raised

All Framework packages now require **PHP 8.1** or newer.

## No API changes

`Http`, `HttpFactory`, `AbstractTransport`, `Response`, the three transports and both exception
classes have the same signatures in 3.0.0 as in 2.0.0.

The magic getters on `Response` that emulate the 1.x API (`$response->body`, `->code`,
`->headers`) are still present in 3.x and still emit a deprecation notice. They were removed in
4.0.0 — see [Updating from v3 to v4](v3-to-v4-update.md).

## Codebase converted to PSR-12

The package was reformatted from the Joomla Coding Standard to PSR-12. This touches nearly every
line and changes no behaviour.

## Dependency changes

| Package | v2 (2.0.x) | v3 (3.0.0) |
|---|---|---|
| `php` | `^7.2.5` | `^8.1.0` |
| `joomla/uri` | `^1.0 \| ^2.0` | `^3.0` |
| `composer/ca-bundle` | `^1.0` | `^1.3.5` |
| `laminas/laminas-diactoros` | `^2.2.2` | `^2.24.0` |
| `psr/http-client` | `^1.0` | `^1.0` |
| `psr/http-message` | `^1.0` | `^1.0` |
