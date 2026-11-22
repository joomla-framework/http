## Joomla\Http\Http

The `Http` class provides methods for making RESTful requests. The class provides a [PSR-18](http://www.php-fig.org/psr/psr-18/) compliant interface
for making requests as well.

### Construction

Construction of `Http` object is generally done using the [Joomla\Http\HttpFactory](HttpFactory.md) class. However, `Http` is not abstract and can
be instantiated directly passing an optional array of options and an optional [Joomla\Http\TransportInterface](TransportInterface.md) object. If
the transport is omitted, the default transport will be used. The default is determined by looking up the transports folder and selecting
the first transport that is supported; this will usually be the "curl" transport.

```php
use Joomla\Http\Http;
use Joomla\Http\Transport\Stream as StreamTransport;

$options = array();

$transport = new StreamTransport($options);

// Create a 'stream' transport.
$http = new Http($options, $transport);
```

### Sending a PSR-7 request

`sendRequest()` implements the PSR-18 `ClientInterface`. The whole request body is sent, even if its stream
was already read, and an empty body is sent as no data. The request's protocol version is not used; the
transport's `protocolVersion` option decides.

```php
use Laminas\Diactoros\Request;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\NetworkExceptionInterface;

try {
    $response = $http->sendRequest(new Request('https://api.example.com/cars', 'GET'));
} catch (NetworkExceptionInterface $e) {
    // No response: the connection failed or was interrupted. $e->getRequest() is the request.
} catch (ClientExceptionInterface $e) {
    // Any other failure, such as a response without a status code.
}
```

Transport failures are thrown as `Joomla\Http\Exception\NetworkException`, which implements
`NetworkExceptionInterface` and still extends `RuntimeException`, so existing `catch (\RuntimeException $e)`
blocks keep working.
