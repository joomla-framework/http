# `Joomla\Http\TransportInterface`

The contract every transport implements. `Http` holds one and delegates each request to it, so the
interface is the seam where you replace how requests are actually sent — with cURL, with a stream,
with a socket, or with a stub in tests.

## The interface

```php
namespace Joomla\Http;

use Joomla\Uri\UriInterface;
use Psr\Http\Message\ResponseInterface;

interface TransportInterface
{
    public function request($method, UriInterface $uri, $data = null, array $headers = [], $timeout = null, $userAgent = null);

    public static function isSupported();
}
```

| Member | Meaning |
|---|---|
| `request()` | Perform the request and return a PSR-7 `ResponseInterface` |
| `isSupported()` | Whether this transport can run in the current environment — static, so it can be asked before instantiating |

`AbstractTransport` implements the constructor (`__construct($options = [])`) and the option
accessors, so a custom transport normally extends it rather than implementing the interface
directly.

## The bundled transports

| Class | `isSupported()` checks | Notes |
|---|---|---|
| `Transport\Curl` | `ext-curl` loaded | The default when available |
| `Transport\Stream` | `allow_url_fopen` enabled | Configurable TLS via a stream context |
| `Transport\Socket` | `fsockopen()` exists | Cannot be configured for TLS — see below |

`HttpFactory` picks one for you:

```php
use Joomla\Http\HttpFactory;

$http = (new HttpFactory())->getHttp();                          // first supported transport
$http = (new HttpFactory())->getHttp([], 'curl');                // force one
$http = (new HttpFactory())->getHttp([], ['curl', 'stream']);    // first supported of these
```

> Prefer `curl` or `stream`. `Socket` connects with `fsockopen()`, which accepts no stream context,
> so no CA bundle, minimum TLS version or client certificate can be supplied for it.

## Writing a transport

```php
use Joomla\Http\AbstractTransport;
use Joomla\Uri\UriInterface;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\Stream;
use Psr\Http\Message\ResponseInterface;

final class RecordingTransport extends AbstractTransport
{
    /** @var array<int, array{method: string, uri: string}> */
    public array $requests = [];

    public function request($method, UriInterface $uri, $data = null, array $headers = [], $timeout = null, $userAgent = null)
    {
        $this->requests[] = ['method' => $method, 'uri' => (string) $uri];

        $body = new Stream('php://memory', 'rw');
        $body->write('{"ok":true}');

        return new Response($body, 200, ['Content-Type' => 'application/json']);
    }

    public static function isSupported()
    {
        return true;
    }
}
```

Hand it to the client directly:

```php
use Joomla\Http\Http;

$transport = new RecordingTransport();
$http      = new Http([], $transport);

$http->get('https://example.com/items');

// $transport->requests now holds what was asked for.
```

This is the cleanest way to test code that makes HTTP calls: no network, no stub server, and the
recorded requests are available for assertions.

## Options

`AbstractTransport::__construct($options = [])` accepts an array or `ArrayAccess`. Options are read
with `getOption($key, $default)` and are transport-specific; the commonly used ones are:

| Option | Used by | Meaning |
|---|---|---|
| `userauth` / `passwordauth` | Curl | HTTP Basic credentials |
| `follow_location` | Curl | Follow redirects, default `true` |
| `transport.curl` | Curl | Raw `CURLOPT_*` overrides, applied last |
| `stream.certpath` | Stream | CA bundle file or directory |
| `protocolVersion` | Curl | `1.0`, `1.1` or `2.0` |

See [the overview](../overview.md#things-to-know-before-you-build-on-this) for the caveats around
`transport.curl` and redirect handling.
