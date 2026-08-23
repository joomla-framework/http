# `Joomla\Http\Response`

The object every transport returns. It extends `Laminas\Diactoros\Response`, which implements
`Psr\Http\Message\ResponseInterface`, so the full PSR-7 API is available and nothing beyond it is
added.

## Reading a response

```php
$response = $http->get('https://example.com/api/items');

$response->getStatusCode();               // 200
$response->getReasonPhrase();             // 'OK'
$response->getProtocolVersion();          // '1.1'

(string) $response->getBody();            // the body as a string
$response->getHeaderLine('Content-Type'); // 'application/json; charset=UTF-8'
$response->getHeaders();                  // ['Content-Type' => ['application/json; …'], …]
$response->hasHeader('Location');         // bool
```

## Reading the body

`getBody()` returns a PSR-7 stream. Cast it rather than calling `getContents()`:

```php
$body = (string) $response->getBody();
```

The cast rewinds the stream first, so a second read returns the body again. `getContents()` reads
from the current position, which is the end of the stream after the first call — a second call
returns an empty string.

```php
$data = json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);
```

## Headers are lists

PSR-7 models a header as a list of values, because a header may legitimately appear more than once:

```php
$response->getHeader('Set-Cookie');       // ['a=1; Path=/', 'b=2; Path=/']
$response->getHeaderLine('Set-Cookie');   // 'a=1; Path=/,b=2; Path=/'
```

Use `getHeaderLine()` when you want one string, `getHeader()` when you need the individual values.

Header names are matched case-insensitively, so `getHeaderLine('content-type')` and
`getHeaderLine('Content-Type')` are equivalent.

## Immutability

PSR-7 messages are immutable. The `with*()` methods return a **new** instance and leave the
original untouched:

```php
$modified = $response->withHeader('X-Trace', $id);

$response->hasHeader('X-Trace');   // false
$modified->hasHeader('X-Trace');   // true
```

This rarely matters for a response you received, but it does when you build one to hand back — see
`Joomla\Application\AbstractWebApplication`, which replaces its stored response on every
`setHeader()` call.

## Status codes

The transports do not treat a 4xx or 5xx status as an error: a response is returned and it is up
to you to check.

```php
$response = $http->get($url);

if ($response->getStatusCode() >= 400) {
    throw new \RuntimeException(
        sprintf('Request to %s failed with status %d', $url, $response->getStatusCode())
    );
}
```

`Joomla\Http\Exception\UnexpectedResponseException` exists for this purpose and carries the
response:

```php
use Joomla\Http\Exception\UnexpectedResponseException;

throw new UnexpectedResponseException($response, 'Unexpected status', $response->getStatusCode());
```

Catching it gives you the response back through `getResponse()`.

## The 1.x property access is gone

Until 3.x the class carried a `__get()` that mapped the original 1.x property names onto the PSR-7
methods, with a deprecation notice. It was removed in 4.0.0:

```php
// Removed in 4.0.0
$response->body;
$response->code;
$response->headers;
```

See [Updating from v3 to v4](../v3-to-v4-update.md#the-response-compatibility-getters-were-removed).
