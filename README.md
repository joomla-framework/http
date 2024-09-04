# The HTTP Package [![Build Status](https://ci.joomla.org/api/badges/joomla-framework/http/status.svg?ref=refs/heads/3.x-dev)](https://ci.joomla.org/joomla-framework/http)

[![Latest Stable Version](https://poser.pugx.org/joomla/http/v/stable)](https://packagist.org/packages/joomla/http)
[![Total Downloads](https://poser.pugx.org/joomla/http/downloads)](https://packagist.org/packages/joomla/http)
[![Latest Unstable Version](https://poser.pugx.org/joomla/http/v/unstable)](https://packagist.org/packages/joomla/http)
[![License](https://poser.pugx.org/joomla/http/license)](https://packagist.org/packages/joomla/http)

The HTTP package includes a [PSR-18](http://www.php-fig.org/psr/psr-18/) compatible HTTP client to facilitate RESTful HTTP requests
over a variety of transport protocols.

## Requirements

* PHP 8.1 or later

## Installation via Composer

Add `"joomla/http": "~3.0"` to the require block in your composer.json and then run `composer install`.

```json
{
	"require": {
		"joomla/http": "~3.0"
	}
}
```

Alternatively, you can simply run the following from the command line:

```sh
composer require joomla/http "~3.0"
```

If you want to include the test sources and docs, use

```sh
composer require --prefer-source joomla/http "~3.0"
```
