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
 * @since  4.1.0
 */
interface HttpFactoryInterface
{
    /**
     * Method to get an instance of a Http client.
     *
     * @param   array|\ArrayAccess  $options   Client options array.
     * @param   array|string|null  $adapters  Adapter (string) or queue of adapters (array) to use for communication.
     *
     * @return  Http
     * 4.1.0
 * @since   __DEPLOY_VERSION__
     */
    public function createHttp(array|\ArrayAccess $options = [], array|string|null $adapters = null): Http;
}
