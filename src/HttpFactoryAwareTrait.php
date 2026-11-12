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
 * @since  __DEPLOY_VERSION__
 */
trait HttpFactoryAwareTrait
{
    /**
     * The HTTP client factory.
     *
     * @var    HttpFactoryInterface|null
     * @since  __DEPLOY_VERSION__
     */
    private ?HttpFactoryInterface $httpFactory = null;

    /**
     * Get the HTTP client factory.
     *
     * @return  HttpFactoryInterface
     *
     * @since   __DEPLOY_VERSION__
     * @throws  \UnexpectedValueException May be thrown if the HTTP client factory has not been set.
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
     * @since   __DEPLOY_VERSION__
     */
    public function setHttpFactory(?HttpFactoryInterface $httpFactory = null): void
    {
        $this->httpFactory = $httpFactory;
    }
}
