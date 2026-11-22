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

### Depending on the factory

`HttpFactory` implements `Joomla\Http\HttpFactoryInterface`, so code that creates clients can type against the
interface and receive any factory, for example one configured in a DI container or a stub in tests. Its single
method `createHttp()` takes the same arguments as `getHttp()`:

```php
/**
 * @param   array|\ArrayAccess  $options   Client options array.
 * @param   array|string|null   $adapters  Adapter (string) or queue of adapters (array) to use for communication.
 *
 * @throws  \InvalidArgumentException
 * @throws  \RuntimeException
 */
public function createHttp(array|\ArrayAccess $options = [], array|string|null $adapters = null): Http
```

Classes that need a factory can implement `Joomla\Http\HttpFactoryAwareInterface` and use
`Joomla\Http\HttpFactoryAwareTrait`, which provides `setHttpFactory()` and a protected `getHttpFactory()`. The getter
throws an `UnexpectedValueException` if no factory was set.

```php
use Joomla\Http\HttpFactoryAwareInterface;
use Joomla\Http\HttpFactoryAwareTrait;

class GitHubClient implements HttpFactoryAwareInterface
{
    use HttpFactoryAwareTrait;

    public function getRepository(string $name): array
    {
        $response = $this->getHttpFactory()->createHttp()->get('https://api.github.com/repos/' . $name);

        return json_decode((string) $response->getBody(), true);
    }
}

$client = new GitHubClient();
$client->setHttpFactory(new \Joomla\Http\HttpFactory());
```
