<?php
/**
 * Part of the Joomla Framework Http Package
 *
 * @copyright  Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Http;

/**
 * HTTP response class interface.
 *
 * @since  __DEPLOY_VERSION__
 */
interface ResponseInterface
{
	/**
	 * Get the response status code (e.g. "200", "404", etc.)
	 *
	 * @return  string
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function getStatusCode();

	/**
	 * Get the body of the message
	 *
	 * @return  string|null
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function getBody();

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
	 * @return  array  Returns an associative array of the message's headers.
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function getHeaders();

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
	public function getHeader($header, $asArray = false);

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
	 */
	public function setHeader($header, $value);
}
