<?php
/**
 * @copyright  Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Http\Tests;

use Joomla\Http\HttpFactory;

/**
 * Test class for Joomla\Http\HttpFactory.
 */
class HttpFactoryTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * Object being tested
	 *
	 * @var  HttpFactory
	 */
	private $instance;

	/**
	 * Sets up the fixture, for example, open a network connection.
	 *
	 * @return  void
	 */
	protected function setUp()
	{
		parent::setUp();

		$this->instance = new HttpFactory;
	}

	/**
	 * Tests the getHttp method.
	 *
	 * @covers  Joomla\Http\HttpFactory::getHttp
	 */
	public function testGetHttp()
	{
		$this->assertInstanceOf(
			'Joomla\\Http\\Http',
			$this->instance->getHttp()
		);
	}

	/**
	 * Tests the getHttp method to ensure only arrays or ArrayAccess objects are allowed
	 *
	 * @covers             Joomla\Http\HttpFactory::getHttp
	 * @expectedException  \InvalidArgumentException
	 */
	public function testGetHttpDisallowsNonArrayObjects()
	{
		$this->instance->getHttp(new \stdClass);
	}

	/**
	 * Tests the getHttp method.
	 *
	 * @covers  Joomla\Http\HttpFactory::getHttp
	 * @expectedException RuntimeException
	 */
	public function testGetHttpException()
	{
		$this->assertInstanceOf(
			'Joomla\\Http\\Http',
			$this->instance->getHttp([], [])
		);
	}

	/**
	 * Tests the getAvailableDriver method.
	 *
	 * @covers  Joomla\Http\HttpFactory::getAvailableDriver
	 */
	public function testGetAvailableDriver()
	{
		$this->assertInstanceOf(
			'Joomla\\Http\\TransportInterface',
			$this->instance->getAvailableDriver([], null)
		);

		$this->assertFalse(
			$this->instance->getAvailableDriver([], []),
			'Passing an empty array should return false due to there being no adapters to test'
		);

		$this->assertFalse(
			$this->instance->getAvailableDriver([], ['fopen']),
			'A false should be returned if a class is not present or supported'
		);

		include_once __DIR__ . '/stubs/DummyTransport.php';

		$this->assertFalse(
			$this->instance->getAvailableDriver([], ['DummyTransport']),
			'Passing an empty array should return false due to there being no adapters to test'
		);
	}

	/**
	 * Tests the getAvailableDriver method to ensure only arrays or ArrayAccess objects are allowed
	 *
	 * @covers             Joomla\Http\HttpFactory::getAvailableDriver
	 * @expectedException  \InvalidArgumentException
	 */
	public function testGetAvailableDriverDisallowsNonArrayObjects()
	{
		$this->instance->getAvailableDriver(new \stdClass);
	}

	/**
	 * Tests the getHttpTransports method.
	 *
	 * @covers  Joomla\Http\HttpFactory::getHttpTransports
	 */
	public function testGetHttpTransports()
	{
		$transports = ['Stream', 'Socket', 'Curl'];
		sort($transports);

		$this->assertEquals(
			$transports,
			$this->instance->getHttpTransports()
		);
	}
}
