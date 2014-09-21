<?php
/**
 * Part of the Joomla Framework Http Package
 *
 * @copyright  Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Http;

/**
 * HTTP response data object class.
 *
 * @since  1.0
 */
class Response implements ResponseInterface
{
	/**
	 * @var    integer  The server response code.
	 * @since  1.0
	 */
	public $code;

	/**
	 * @var    array  Response headers.
	 * @since  1.0
	 */
	private $headers = array();

	/**
	 * @var    string  Server response body.
	 * @since  1.0
	 */
	public $body;

	/**
	 * Constructor
	 *
	 * @param   string  $statusCode  The response status code (e.g. 200)
	 * @param   array   $headers     The response headers
	 * @param   string  $body        The body of the response
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function __construct($statusCode, array $headers = array(), $body = null)
	{
		$this->code = (string) $statusCode;

		if ($headers)
		{
			$this->setHeaders($headers);
		}

		if ($body)
		{
			$this->setBody($body);
		}
	}

	/**
	 * Gets all message headers.
	 *
	 * The keys represent the header name as it will be sent over the wire, and
	 * each value is an array of strings associated with the header.
	 *
	 *     // Represent the headers as a string
	 *     foreach ($message->getHeaders() as $name => $values) {
	 *         echo $name . ": " . implode(", ", $values);
	 *     }
	 *
	 * @return  array   Returns an associative array of the message's headers.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getHeaders()
	{
		return $this->headers;
	}

	/**
	 * Sets headers, replacing any headers that have already been set on the
	 * message.
	 *
	 * The array keys MUST be a string. The array values must be either a
	 * string or an array of strings.
	 *
	 * @param   array  $headers  Headers to set.
	 *
	 * @return  Response  Instance of $this to allow chaining.
	 */
	public function setHeaders(array $headers = array())
	{
		foreach ($headers as $key => $value)
		{
			$this->setHeader($key, $value);
		}

		return $this;
	}

	/**
	 * Retrieve a header by the given case-insensitive name.
	 *
	 * By default, this method returns all of the header values of the given
	 * case-insensitive header name as a string concatenated together using
	 * a comma. Because some header should not be concatenated together using a
	 * comma, this method provides a Boolean argument that can be used to
	 * retrieve the associated header values as an array of strings.
	 *
	 * @param   string   $header   Case-insensitive header name.
	 * @param   boolean  $asArray  Set to true to retrieve the header value as an array of strings.
	 *
	 * @return  array|string
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getHeader($header, $asArray = false)
	{
		$name = strtolower($header);

		if (!isset($this->headers[$name]))
		{
			return $asArray ? array() : '';
		}

		return $asArray ? $this->headers[$name] : implode(', ', $this->headers[$name]);
	}

	/**
	 * Sets a header, replacing any existing values of any headers with the
	 * same case-insensitive name.
	 *
	 * The header values MUST be a string or an array of strings.
	 *
	 * @param   string        $header Header name
	 * @param   string|array  $value  Header value(s)
	 *
	 * @return  ResponseInterface  Instance of $this to allow chaining.
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  \InvalidArgumentException
	 */
	public function setHeader($header, $value)
	{
		$name = strtolower($header);

		switch (gettype($value))
		{
			case 'string':
				$this->headers[$name] = array(trim($value));
				break;

			case 'integer':
			case 'double':
				$this->headers[$name] = array((string) $value);
				break;

			case 'array':
				foreach ($value as &$v)
				{
					$v = trim($v);
				}

				$this->headers[$name] = $value;
				break;

			default:
				throw new \InvalidArgumentException('Invalid header value provided: ' . var_export($value, true));
		}

		return $this;
	}

	/**
	 * Get the response status code (e.g. "200", "404", etc.)
	 *
	 * @return  string
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function getStatusCode()
	{
		return $this->code;
	}

	/**
	 * Get the body of the message
	 *
	 * @return  string|null
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function getBody()
	{
		return $this->body;
	}

	/**
	 * Get the body of the message
	 *
	 * @param   string|null  $body  Set the body
	 *
	 * @return  Response  Instance of $this to allow chaining.
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function setBody($body)
	{
		$this->body = $body;

		return $this;
	}
}
