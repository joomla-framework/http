<?php

/**
 * Part of the Joomla Framework Http Package
 *
 * @copyright  Copyright (C) 2005 - 2026 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Http\Exception;

use Psr\Http\Client\NetworkExceptionInterface;
use Psr\Http\Message\RequestInterface;

/**
 * Exception thrown by Http::sendRequest() when a request could not be completed, for example because the connection failed
 *
 * @since  __DEPLOY_VERSION__
 */
class NetworkException extends \RuntimeException implements NetworkExceptionInterface
{
    /**
     * The request that failed.
     *
     * @var    RequestInterface
     * @since  __DEPLOY_VERSION__
     */
    private RequestInterface $request;

    /**
     * Constructor
     *
     * @param   RequestInterface  $request   The request that failed.
     * @param   string            $message   The Exception message to throw.
     * @param   integer           $code      The Exception code.
     * @param   ?\Throwable       $previous  The previous exception used for the exception chaining.
     *
     * @since   __DEPLOY_VERSION__
     */
    public function __construct(RequestInterface $request, string $message = '', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);

        $this->request = $request;
    }

    /**
     * Get the request that failed.
     *
     * @return  RequestInterface
     *
     * @since   __DEPLOY_VERSION__
     */
    public function getRequest(): RequestInterface
    {
        return $this->request;
    }
}
