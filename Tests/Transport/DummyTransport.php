<?php
/**
 * Part of the Joomla Framework Http Package
 *
 * @copyright  Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Http\Transport;

use Joomla\Http\Response;
use Joomla\Http\TransportInterface;
use Joomla\Uri\UriInterface;

/**
 * HTTP transport class for testing purpose only.
 *
 * @since  1.1.4
 * @todo   When HttpFactory supports custom namespace lookups, this namespace should be Joomla\Http\Tests\Transport
 */
class DummyTransport implements TransportInterface
{
	/**
	 * Constructor.
	 *
	 * @param   array  $options  Client options object.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function __construct($options = array())
	{
		$this->options = $options;
	}

	/**
	 * Send a request to the server and return a JHttpResponse object with the response.
	 *
	 * @param   string        $method     The HTTP method for sending the request.
	 * @param   UriInterface  $uri        The URI to the resource to request.
	 * @param   mixed         $data       Either an associative array or a string to be sent with the request.
	 * @param   array         $headers    An array of request headers to send with the request.
	 * @param   integer       $timeout    Read timeout in seconds.
	 * @param   string        $userAgent  The optional user agent string to send with the request.
	 *
	 * @return  Response
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  \RuntimeException
	 */
	public function request($method, UriInterface $uri, $data = null, array $headers = null, $timeout = null, $userAgent = null)
	{
		return new Response(200);
	}

	/**
	 * Method to check if HTTP transport DummyTransport is available for use
	 *
	 * @return  boolean  True if available, else false
	 *
	 * @since   1.1.4
	 */
	public static function isSupported()
	{
		return true;
	}
}
