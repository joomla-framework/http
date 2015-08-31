<?php
/**
 * Part of the Joomla Framework Http Package
 *
 * @copyright  Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Http;

use Zend\Diactoros\Response as PsrResponse;

/**
 * HTTP response data object class.
 *
 * @since  1.0
 */
class Response extends PsrResponse
{
	/**
	 * Magic getter to keep b/c with code usage before introduction of PSR-7 interface
	 *
	 * @return  mixed
	 */
	public function __get($name)
	{
		if (strtolower($name) === 'body')
		{
			return (string) $this->getBody();
		}

		if (strtolower($name) === 'code')
		{
			return $this->getStatusCode();
		}

		if (strtolower($name) === 'headers')
		{
			return $this->getHeaders();
		}

		$trace = debug_backtrace();
		trigger_error(
			'Undefined property via __get(): ' . $name .
			' in ' . $trace[0]['file'] .
			' on line ' . $trace[0]['line'],
			E_USER_NOTICE);

		return null;
	}
}
