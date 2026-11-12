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
     * Validate the names and values of request headers.
     *
     * Header names must be RFC 9110 tokens and values must not contain line breaks or NUL bytes, which would
     * otherwise allow injecting further headers into the request.
     *
     * @param   array  $headers  The request headers, with a string or a list of strings per name.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     * @throws  \InvalidArgumentException
     */
    protected function validateHeaders(array $headers): void
    {
        foreach ($headers as $name => $value) {
            if (!preg_match('/^[!#$%&\'*+.^_`|~0-9A-Za-z-]+$/D', (string) $name)) {
                throw new \InvalidArgumentException(sprintf('"%s" is not a valid header name.', $name));
            }

            foreach ((array) $value as $item) {
                if (strpbrk((string) $item, "\r\n\0") !== false) {
                    throw new \InvalidArgumentException(
                        sprintf('The value of the "%s" header must not contain line breaks or NUL bytes.', $name)
                    );
                }
            }
        }
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
