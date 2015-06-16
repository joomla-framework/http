## HttpFactory

The `HttpFactory` class provides an interface for creating [Http](Http.md) objects.

### Retrieving a Http object

The `getHttp()` method can be used to create `Http` objects from the factory.

```php
/*
 * @param   array|\ArrayAccess  $options   Client options array.
 * @param   array|string        $adapters  Adapter (string) or queue of adapters (array) to use for communication.
 */
public static function getHttp($options = array(), $adapters = null)
```

The following example demonstrates basic use of the `getHttp()` method.

```php
use Joomla\Http\HttpFactory;

// Create an instance of a default Http object.
$http = HttpFactory::getHttp();
```

Note that a `RuntimeException` is thrown if no `TransportInterface` objects are available for use.

### Retrieving a TransportInterface object

The `getAvailableDriver()` method can be used to create [TransportInterface](TransportInterface.md) objects from the factory. 

```php
/*
 * @param   array|\ArrayAccess  $options  Options for creating TransportInterface object
 * @param   array|string        $default  Adapter (string) or queue of adapters (array) to use
 */
public static function getAvailableDriver($options = array(), $default = null)
```

The following example demonstrates basic use of the `getAvailableDriver()` method.

```php
use Joomla\Http\HttpFactory;

// Create an instance of a TransportInterface object.
$transport = HttpFactory::getAvailableDriver();
```

### Retrieving the available TransportInterface implementations

The `getHttpTransports()` method can be used to retrieve a list of supported [TransportInterface](TransportInterface.md) objects from the factory. 

```php
public static function getHttpTransports()
```

The following example demonstrates basic use of the `getHttpTransports()` method.

```php
use Joomla\Http\HttpFactory;

// Get a list of available TransportInterface objects.
$transports = HttpFactory::getHttpTransports();
```

