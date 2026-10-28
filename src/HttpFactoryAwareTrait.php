<?php

/**
 * Part of the Joomla Framework Http Package
 *
 * @copyright  Copyright (C) 2005 - 2026 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Http;

/**
 * Defines the trait for an HttpFactoryInterface aware class.
 *
 * @since  4.1.0
 */
trait HttpFactoryAwareTrait
{
    /**
     * HttpFactoryInterface
     *
     * @var    HttpFactoryInterface
     * @since  4.1.0
     */
    private $httpFactory;

    /**
     * Get the HttpFactoryInterface.
     *
     * @return  HttpFactoryInterface
     *
     * @since   4.1.0
     * @throws  \UnexpectedValueException May be thrown if the HttpFactory has not been set.
     */
    protected function getHttpFactory(): HttpFactoryInterface
    {
        if ($this->httpFactory) {
            return $this->httpFactory;
        }

        throw new \UnexpectedValueException('HttpFactory not set in ' . __CLASS__);
    }

    /**
     * Set the HTTP client factory to use.
     *
     * @param   ?HttpFactoryInterface  $httpFactory  The HTTP client factory to use.
     *
     * @return  void
     *
     * @since   4.1.0
     */
    public function setHttpFactory(?HttpFactoryInterface $httpFactory = null): void
    {
        $this->httpFactory = $httpFactory;
    }
}
