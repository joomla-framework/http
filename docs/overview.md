## Overview

The HTTP package includes a suite of classes to facilitate RESTful HTTP requests over a variety of transport protocols.
All methods return a [Response](classes/Response.md) object which is [PSR-7](http://www.php-fig.org/psr/psr-7/) compliant.
Additionally, the main `Http` class provides support for sending requests as covered by the [PSR-18](http://www.php-fig.org/psr/psr-18/)
`ClientInterface`.

### Making a HEAD request

An HTTP HEAD request can be made using the head method passing a URL and an optional key-value array of header variables.

```php
use Joomla\Http\HttpFactory;

// Create an instance of a default Http object.
$http = (new HttpFactory)->getHttp();

// Invoke the HEAD request.
$response = $http->head('http://example.com');

// Get the response code, see http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html
var_dump($response->getStatusCode());

// Get the response headers.
var_dump($response->getHeaders());

// Get the body of the response (not applicable for the HEAD method).
var_dump((string) $response->getBody());
```

### URI Arguments

In each of the `Http` class' methods, either a string or `Joomla\Uri\UriInterface` object may be passed representing the URI
that should be requested. Internally this argument is converted to a `Joomla\Uri\Uri` object if a string is passed and an
`InvalidArgumentException` is thrown if the URI parameter is not of one of these types.

### Making a GET request

An HTTP GET request can be made using the get method passing a URL, an optional key-value array of header variables and an
optional timeout value. In RESTful terms, a GET request is sent to read data from the server.

```php
// Invoke the GET request.
$response = $http->get('http://api.example.com/cars');
```

### Making a POST request

An HTTP POST request can be made using the post method passing a URL, a data variable, an optional key-value array of header
variables and an optional timeout value. The data can be either an associative array of POST variables, or a string to be sent
with the request. In RESTful terms, a POST request is sent to create new data on the server.

```php
// Prepare the update data.
$data = ['make' => 'Holden', model => 'EJ-Special'];

// Invoke the POST request.
$response = $http->post('http://api.example.com/cars/1', $data);
```

### Making a PUT request

An HTTP PUT request can be made using the put method passing a URL, a data variable, an optional key-value array of header
variables and an optional timeout value. The data can be either an associative array, which is sent form-encoded, or a string to
be sent with the request. In RESTful terms, a PUT request is typically sent to update existing data on the server.

```php
// Prepare the update data.
$data = ['description' => 'My first car.', 'color' => 'gray'];

// Invoke the PUT request.
$response = $http->put('http://api.example.com/cars/1', $data);
```

### Making a DELETE request

An HTTP DELETE request can be made using the delete method passing a URL, an optional key-value array of header variables
and an optional timeout value. In RESTful terms, a DELETE request is typically sent to delete existing data on the server.

```php
// Invoke the DELETE request.
$response = $http->delete('http://api.example.com/cars/1');
```

### Making a TRACE request

An HTTP TRACE request can be made using the trace method passing a URL and an optional key-value array of header variables.
In RESTful terms, a TRACE request is to echo data back to the client for debugging or testing purposes.

```php
// Invoke the TRACE request.
$response = $http->trace('http://api.example.com/cars/1');
```

### Working with options

Custom headers can be passed into each REST request, but they can also be set globally in the `headers` option, an array
of header names and values. In the case where a request method passes additional headers, those will override the
headers set in the options.

```php
use Joomla\Http\HttpFactory;

// Configure a custom Accept header for all requests.
$options = [
    'headers' => ['Accept' => 'application/vnd.github.html+json'],
];

$http = (new HttpFactory())->getHttp($options);

// Make the request, knowing the custom Accept header will be used.
$pull = $http->get('https://api.github.com/repos/joomla-framework/http/pulls/1');

// Set up custom headers for a single request.
$headers = ['Accept' => 'application/foo'];

// In this case, the Accept header in $headers will override the options header.
$pull = $http->get('https://api.github.com/repos/joomla-framework/http/pulls/1', $headers);
```

The options can also be a `Joomla\Registry\Registry`; `$options->set('headers.Accept', …)` then sets the
same value as `['headers' => ['Accept' => …]]`. With a plain array a key such as `'headers.Accept'` is not read.

The client and the bundled transports read these options:

| Option | Used by | Meaning |
|---|---|---|
| `headers` | `Http` | Headers sent with every request; a request's own headers win |
| `timeout` | `Http` | Timeout in seconds for requests that do not pass one |
| `userAgent` | `Http` | The user agent sent with every request |
| `follow_location` | transports | Follow redirects, default `true` |
| `protocolVersion` | transports | HTTP version: `1.0`, `1.1` or `2.0` (cURL only); the stream and socket transports default to `1.0` |
| `transport.curl` | Curl | `CURLOPT_*` options, applied last |
| `transport.stream` | Stream | Options for the `http` stream context, applied last |
| `transport.socket` | Socket | Extra lines added to the request head |
| `curl.certpath` / `stream.certpath` | Curl / Stream | CA bundle file (or directory for the stream transport) |

See [TransportInterface](classes/TransportInterface.md#options) for the other transport options.

## Things to know before you build on this

**There is no SSRF protection.** Any URI is accepted and fetched, including private address ranges
and non-HTTP schemes on the stream transport. If a target URL can come from user input or
configuration, validate it before the call — resolve the host and reject private ranges yourself.

**`transport.curl` overrides everything, including the security defaults.** The custom options are
applied last, so a value such as `CURLOPT_SSL_VERIFYPEER => false` silently disables certificate
verification for that client:

```php
// Anything set here wins over the defaults the transport computed.
$http = (new HttpFactory())->getHttp(['transport.curl' => [CURLOPT_TIMEOUT => 5]]);
```

Keep that array to non-security options, or re-assert the ones that matter afterwards.

**Redirects are followed by default.** `follow_location` is `true` unless you set it. The cURL
transport stops after 20 redirects (PHP's default for `CURLOPT_MAXREDIRS`), and libcurl only follows
redirects to HTTP, HTTPS, FTP and FTPS; PHP's stream wrapper also stops after 20 redirects and only
follows them to `http` and `https`. Redirects can still lead to hosts you did not intend to reach, so
disable following when the target is not fully trusted, or lower the limit with `transport.curl`
(`CURLOPT_MAXREDIRS`) or `transport.stream` (`max_redirects`):

```php
$http = (new HttpFactory())->getHttp([
    'follow_location' => false,
]);
```

**The whole response body is buffered in memory.** There is no size limit and no streaming option,
so a large or hostile response can exhaust `memory_limit`. Fetch untrusted URLs with your own
limit in place.

**The socket transport cannot be configured for TLS.** It connects with `fsockopen()`, which takes
no stream context, so no CA bundle, minimum TLS version or client certificate can be supplied — and
the connection error is suppressed, so a certificate failure is indistinguishable from an
unreachable host. Prefer the cURL or stream transport.

**The stream transport accepts any wrapper.** The URI goes to `fopen()` unfiltered, so a `file://`,
`php://` or `phar://` URL is opened rather than rejected. Check the scheme before calling if the
URL is not your own.

**`Http::get()` and friends take positional arguments.** Headers and timeouts are passed per call:

```php
$response = $http->get($url, ['Accept' => 'application/json'], 10);
```

To work with a request object instead, build any PSR-7 request and send it with the PSR-18 method
`sendRequest()`:

```php
use Laminas\Diactoros\Request;

$request  = (new Request($url, 'GET'))->withHeader('Accept', 'application/json');
$response = $http->sendRequest($request);
```
