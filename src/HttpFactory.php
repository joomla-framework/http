<?php
/**
 * Part of the Joomla Framework Http Package
 *
 * @copyright  Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Http;

/**
 * HTTP factory class.
 *
 * @since  1.0
 */
class HttpFactory
{
	/**
	 * Method to receive a HttpInterface object.
	 *
	 * @param   array         $options   Client options array.
	 * @param   string|array  $adapters  Adapter (string) or queue of adapters (array) to use for communication.
	 * @param   string        $class     Class name of the object to instantiate, must be the full class name (supported @since __DEPLOY_VERSION__)
	 *
	 * @return  HttpInterface
	 *
	 * @since   1.0
	 * @throws  \RuntimeException
	 */
	public static function getHttp($options = array(), $adapters = null, $class = '\\Joomla\\Http\\Http')
	{
		if (!$driver = static::getAvailableDriver($options, $adapters))
		{
			throw new \RuntimeException('No transport driver available.');
		}

		return new $class($options, $driver);
	}

	/**
	 * Finds an available http transport object for communication
	 *
	 * @param   array  $options  Option for creating http transport object
	 * @param   mixed  $default  Adapter (string) or queue of adapters (array) to use
	 * @param   array  $paths    An array of custom lookup paths to search for transport objects in (supported @since __DEPLOY_VERSION__)
	 *
	 * @return  TransportInterface|boolean  Interface sub-class or boolean false if no adapters are available
	 *
	 * @since   1.0
	 */
	public static function getAvailableDriver($options, $default = null, array $paths = array())
	{
		if (is_null($default))
		{
			$availableAdapters = static::getHttpTransports($paths);
		}
		else
		{
			settype($default, 'array');
			$availableAdapters = $default;
		}

		// Check if there is at least one available http transport adapter
		if (!count($availableAdapters))
		{
			return false;
		}

		foreach ($availableAdapters as $adapter)
		{
			/* @var  $class  TransportInterface */
			// TODO - Extend this to enable custom namespaces
			$class = 'Joomla\\Http\\Transport\\' . ucfirst($adapter);

			if (class_exists($class))
			{
				if ($class::isSupported())
				{
					return new $class($options);
				}
			}
		}

		return false;
	}

	/**
	 * Get the http transport handlers
	 *
	 * @param   array  $paths  An array of custom lookup paths to search for transport objects in (supported @since __DEPLOY_VERSION__)
	 *
	 * @return  array  An array of available transport handlers
	 *
	 * @since   1.0
	 */
	public static function getHttpTransports(array $paths = array())
	{
		$names = array();

		// First, pull transports from user defined paths
		foreach ($paths as $path)
		{
			$iterator = new \DirectoryIterator($path);

			/*  @var  $file  \DirectoryIterator */
			foreach ($iterator as $file)
			{
				$fileName  = $file->getFilename();
				$transport = substr($fileName, 0, strrpos($fileName, '.'));

				// Only load for php files.
				if ($file->isFile() && $file->getExtension() == 'php' && !in_array($transport, $names))
				{
					$names[] = $transport;
				}
			}
		}

		// Now add our transports if not already defined
		$iterator = new \DirectoryIterator(__DIR__ . '/Transport');

		/*  @var  $file  \DirectoryIterator */
		foreach ($iterator as $file)
		{
			$fileName  = $file->getFilename();
			$transport = substr($fileName, 0, strrpos($fileName, '.'));

			// Only load for php files.
			if ($file->isFile() && $file->getExtension() == 'php' && !in_array($transport, $names))
			{
				$names[] = $transport;
			}
		}

		// Keep alphabetical order across all environments
		sort($names);

		return $names;
	}
}
