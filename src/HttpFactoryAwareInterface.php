<?php

/**
 * Part of the Joomla Framework Http Package
 *
 * @copyright  Copyright (C) 2005 - 2026 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Http;

/**
 * Interface to be implemented by classes depending on an HTTP client factory.
 *
 * @since  4.1.0
 */
interface HttpFactoryAwareInterface
{
    /**
     * Set the HTTP client factory to use.
     *
     * @param   ?HttpFactoryInterface  $httpFactory  The HTTP client factory to use.
     *
     * @return  void
     *
     * @since   4.1.0
     */
    public function setHttpFactory(?HttpFactoryInterface $httpFactory = null): void;
}
