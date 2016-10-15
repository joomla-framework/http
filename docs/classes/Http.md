## Joomla\Http\Http

The `Http` class provides methods for making RESTful requests.

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
