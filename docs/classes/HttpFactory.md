## Joomla\Http\HttpFactory

The `HttpFactory` class provides an interface for creating [Joomla\Http\Http](Http.md) objects.

### Retrieving a Http object

The `getHttp()` method can be used to create `Http` objects from the factory.

```php
/*
 * @param   array|\ArrayAccess  $options   Client options array.
 * @param   array|string        $adapters  Adapter (string) or queue of adapters (array) to use for communication.
 *
 * @return  \Joomla\Http\Http
 *
 * @throws  \InvalidArgumentException
 * @throws  \RuntimeException
 */
public function getHttp($options = array(), $adapters = null)
```

The following example demonstrates basic use of the `getHttp()` method.

```php
use Joomla\Http\HttpFactory;

// Create an instance of a default Http object.
$http = (new HttpFactory)->getHttp();
```

Note that an `InvalidArgumentException` is thrown if the `$options` argument is not an array or an object implementing the
[ArrayAccess](https://secure.php.net/manual/en/class.arrayaccess.php) interface.

Note that a `RuntimeException` is thrown if no `Joomla\Http\TransportInterface` objects are available for use.

### Retrieving a Joomla\Http\TransportInterface object

The `getAvailableDriver()` method can be used to create [TransportInterface](TransportInterface.md) objects from the factory. 

```php
/*
 * @param   array|\ArrayAccess  $options  Options for creating TransportInterface object
 * @param   array|string        $default  Adapter (string) or queue of adapters (array) to use
 *
 * @return  TransportInterface|boolean  Interface sub-class or boolean false if no adapters are available
 *
 * @throws  \InvalidArgumentException
 */
public function getAvailableDriver($options = array(), $default = null)
```

The following example demonstrates basic use of the `getAvailableDriver()` method.

```php
use Joomla\Http\HttpFactory;

// Create an instance of a TransportInterface object.
$transport = (new HttpFactory)->getAvailableDriver();
```

Note that an `InvalidArgumentException` is thrown if the `$options` argument is not an array or an object implementing the ArrayAccess
interface.

### Retrieving the available TransportInterface implementations

The `getHttpTransports()` method can be used to retrieve a list of supported [TransportInterface](TransportInterface.md) objects from the factory. 

```php
/**
 * @return  array  An array of available transport handler types
 */
public function getHttpTransports()
```

The following example demonstrates basic use of the `getHttpTransports()` method.

```php
use Joomla\Http\HttpFactory;

// Get a list of available TransportInterface objects.
$transports = (new HttpFactory)->getHttpTransports();
```

