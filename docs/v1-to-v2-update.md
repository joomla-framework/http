## Updating from v1 to v2

### Minimum supported PHP version raised

All Framework packages now require PHP 7.2 or newer.

### PSR-7 Support

Version 2 of the HTTP package started to use [PSR-7](http://www.php-fig.org/psr/psr-7/) compliant syntax. This means that
custom transport drivers now need to formulate their `Joomla\Http\Response` object using PSR-7 compliant syntax.

```php
$response = new \Joomla\Http\Response;
$response->withBody($body);
$response->withHeader($headerName, $headerValue);
$response->withStatus($statusCode);
```

We encourage users of the package to use PSR-7 compliant code to retrieve information from the response object, however we are maintaining
support for retrieving the body, headers and status code through the same way as in version 1 of the HTTP package.

### Factory class methods no longer static

The methods of the `Joomla\Http\HttpFactory` class are no longer static.  Users must now instantiate the factory class to access its methods.

### cacert.pem no longer included

The HTTP package no longer includes a cacert.pem file. The file packaged with the `composer/ca-bundle` package is used instead per its logic.
