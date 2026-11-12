<?php

/**
 * Part of the Joomla Framework Http Package
 *
 * @copyright  Copyright (C) 2005 - 2026 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Http;

/**
 * Interface defining a factory which can create Http client objects.
 *
 * @since  __DEPLOY_VERSION__
 */
interface HttpFactoryInterface
{
    /**
     * Method to create an Http client.
     *
     * @param   array|\ArrayAccess  $options   Client options array.
     * @param   array|string|null   $adapters  Adapter (string) or queue of adapters (array) to use for communication.
     *
     * @return  Http
     *
     * @since   __DEPLOY_VERSION__
     * @throws  \InvalidArgumentException
     * @throws  \RuntimeException
     */
    public function createHttp(array|\ArrayAccess $options = [], array|string|null $adapters = null): Http;
}
