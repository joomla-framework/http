# Updating from v4 to v5

Release 5.0.0 removes the socket transport, which was deprecated in 4.x.

## At a glance

| | v4 | v5 (5.0.0) |
|---|---|---|
| `Joomla\Http\Transport\Socket` | deprecated, works | **removed** |

## The socket transport was removed

`Joomla\Http\Transport\Socket` implemented HTTP/1.x by hand on top of `fsockopen()`. It could not be
configured for TLS (no CA bundle, minimum TLS version or client certificate), did not support
proxies and did not decode chunked responses. It was deprecated in 4.x, where creating it triggers
a deprecation notice, and is removed in 5.0.0.

`HttpFactory` now chooses between the cURL and the stream transport only. Code that names the
socket transport has to use one of the others:

```php
use Joomla\Http\HttpFactory;
use Joomla\Http\Transport\Stream;

// Removed in 5.0.0
$http = (new HttpFactory())->getHttp($options, 'socket');
$transport = new \Joomla\Http\Transport\Socket($options);

// Use the cURL or the stream transport
$http = (new HttpFactory())->getHttp($options, ['curl', 'stream']);
$transport = new Stream($options);
```

Asking the factory for `'socket'` alone now finds no transport: `getAvailableDriver()` returns
`false` and `getHttp()` throws a `RuntimeException`. The stream transport only needs
`allow_url_fopen`; options that were specific to the socket transport (`transport.socket`) have no
equivalent and can be dropped.

To find the call sites:

```bash
grep -rnE "Transport\\\\Socket|'socket'|transport\.socket" src/
```
