<?php
/**
 * @copyright  Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Http\Tests;

use Joomla\Http\HttpFactory;

/**
 * Test class for Joomla\Http\HttpFactory.
 *
 * @since  1.0
 */
class FactoryTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * Tests the getHttp method.
	 *
	 * @return  void
	 *
	 * @covers  Joomla\Http\HttpFactory::getHttp
	 * @since   1.0
	 */
	public function testGetHttp()
	{
		$this->assertInstanceOf(
			'Joomla\\Http\\Http',
			HttpFactory::getHttp()
		);
	}

	/**
	 * Tests the getHttp method.
	 *
	 * @return  void
	 *
	 * @covers  Joomla\Http\HttpFactory::getHttp
	 * @since   __DEPLOY_VERSION__
	 */
	public function testGetHttpCustomObject()
	{
		$this->assertInstanceOf(
			'Joomla\\Http\\Tests\\TestHttp',
			HttpFactory::getHttp(array(), null, '\\Joomla\\Http\\Tests\\TestHttp'),
			'The HttpInterface object should be an instance of the custom class specified.'
		);
	}

	/**
	 * Tests the getHttp method.
	 *
	 * @return  void
	 *
	 * @covers  Joomla\Http\HttpFactory::getHttp
	 * @expectedException RuntimeException
	 * @since   1.1.4
	 */
	public function testGetHttpException()
	{
		$this->assertInstanceOf(
			'Joomla\\Http\\Http',
			HttpFactory::getHttp(array(), array())
		);
	}

	/**
	 * Tests the getAvailableDriver method.
	 *
	 * @return  void
	 *
	 * @covers  Joomla\Http\HttpFactory::getAvailableDriver
	 * @since   1.0
	 */
	public function testGetAvailableDriver()
	{
		$this->assertInstanceOf(
			'Joomla\\Http\\TransportInterface',
			HttpFactory::getAvailableDriver(array(), null)
		);

		$this->assertFalse(
			HttpFactory::getAvailableDriver(array(), array()),
			'Passing an empty array should return false due to there being no adapters to test'
		);

		$this->assertFalse(
			HttpFactory::getAvailableDriver(array(), array('fopen')),
			'A false should be returned if a class is not present or supported'
		);
	}

	/**
	 * Tests the getAvailableDriver method with a custom lookup path for transports
	 *
	 * @return  void
	 *
	 * @covers  Joomla\Http\HttpFactory::getAvailableDriver
	 * @since   __DEPLOY_VERSION__
	 */
	public function testGetAvailableDriverWithCustomLookup()
	{
		// TODO - When HttpFactory supports custom namespace lookups, this include should be removed
		include_once __DIR__ . '/Transport/DummyTransport.php';

		$this->assertInstanceOf(
			'Joomla\\Http\\Transport\\DummyTransport',
			HttpFactory::getAvailableDriver(array(), array('DummyTransport'), array(__DIR__ . '/Transport')),
			'Should return a TransportInterface object from the custom lookup path'
		);
	}

	/**
	 * Tests the getHttpTransports method.
	 *
	 * @return  void
	 *
	 * @covers  Joomla\Http\HttpFactory::getHttpTransports
	 * @since   1.1.4
	 */
	public function testGetHttpTransports()
	{
		$transports = array('Stream', 'Socket', 'Curl');
		sort($transports);

		$this->assertEquals(
			$transports,
			HttpFactory::getHttpTransports()
		);
	}


	/**
	 * Tests the getHttpTransports method.
	 *
	 * @return  void
	 *
	 * @covers  Joomla\Http\HttpFactory::getHttpTransports
	 * @since   __DEPLOY_VERSION__
	 */
	public function testGetHttpTransportsWithCustomLookup()
	{
		$transports = array('Stream', 'Socket', 'Curl', 'DummyTransport');
		sort($transports);

		$this->assertEquals(
			$transports,
			HttpFactory::getHttpTransports(array(__DIR__ . '/Transport'))
		);
	}
}
