<?php

/**
 * Part of the Joomla Framework Http Package
 *
 * @copyright  Copyright (C) 2005 - 2021 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Http;

/**
 * Abstract transport class.
 *
 * @since  2.0.0
 */
abstract class AbstractTransport implements TransportInterface
{
    /**
     * The client options.
     *
     * @var    array|\ArrayAccess
     * @since  2.0.0
     */
    protected $options;

    /**
     * Constructor.
     *
     * @param   array|\ArrayAccess  $options  Client options array.
     *
     * @since   2.0.0
     * @throws  \RuntimeException
     */
    public function __construct($options = [])
    {
        if (!static::isSupported()) {
            throw new \RuntimeException(sprintf('The %s transport is not supported in this environment.', \get_class($this)));
        }

        if (!\is_array($options) && !($options instanceof \ArrayAccess)) {
            throw new \InvalidArgumentException(
                'The options param must be an array or implement the ArrayAccess interface.'
            );
        }

        $this->options = $options;
    }

    /**
     * Get the value of a request header, ignoring the case of its name.
     *
     * @param   array   $headers  The request headers, with a string or a list of strings per name.
     * @param   string  $name     The name of the header.
     *
     * @return  string|null  The value, with list values joined by a comma, or null if the header is not set.
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function getHeaderValue(array $headers, string $name): ?string
    {
        foreach ($headers as $key => $value) {
            if (strcasecmp((string) $key, $name) === 0) {
                return implode(', ', (array) $value);
            }
        }

        return null;
    }

    /**
     * Set a request header, replacing a header of the same name in any case.
     *
     * @param   array   $headers  The request headers.
     * @param   string  $name     The name of the header.
     * @param   mixed   $value    The value of the header.
     *
     * @return  array  The request headers.
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function setHeader(array $headers, string $name, $value): array
    {
        foreach (array_keys($headers) as $key) {
            if (strcasecmp((string) $key, $name) === 0) {
                unset($headers[$key]);
            }
        }

        $headers[$name] = $value;

        return $headers;
    }

    /**
     * Get an option from the HTTP transport.
     *
     * @param   string  $key      The name of the option to get.
     * @param   mixed   $default  The default value if the option is not set.
     *
     * @return  mixed  The option value.
     *
     * @since   2.0.0
     */
    protected function getOption(string $key, $default = null)
    {
        return $this->options[$key] ?? $default;
    }

    /**
     * Processes the headers from a transport's response data.
     *
     * @param   array  $headers  The headers to process.
     *
     * @return  array
     *
     * @since   2.0.0
     */
    protected function processHeaders(array $headers): array
    {
        $verifiedHeaders = [];

        // Add the response headers to the response object.
        foreach ($headers as $header) {
            $pos     = strpos($header, ':');
            $keyName = trim(substr($header, 0, $pos));

            if (!isset($verifiedHeaders[$keyName])) {
                $verifiedHeaders[$keyName] = [];
            }

            $verifiedHeaders[$keyName][] = trim(substr($header, ($pos + 1)));
        }

        return $verifiedHeaders;
    }
}
