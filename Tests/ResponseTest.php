<?php
/**
 * @copyright  Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Http\Tests;

use Joomla\Http\Response;

/**
 * Test class for Joomla\Http\Http.
 *
 * @since  1.0
 */
class ResponseTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @var    Response  Object under test.
	 * @since  __DEPLOY_VERSION__
	 */
	protected $object;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function setUp()
	{
		parent::setUp();

		$this->object = new Response(200, array('string' => 'foo', 'array' => array('foo' => 'bar')), 'Some random string');
	}

	/**
	 * Tests the getHeaders method
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function testGetHeaders()
	{
		$this->assertEquals(
			$this->object->getHeaders(),
			array(
				'string' => array('foo'),
				'array'  => array(
					'foo' => 'bar'
				)
			)
		);
	}

	/**
	 * Tests the getHeader method
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function testGetHeader()
	{
		$this->assertEquals(
			$this->object->getHeader('string'),
			'foo'
		);
	}

	/**
	 * Tests the getStatusCode method
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function testGetStatusCode()
	{
		$this->assertEquals(
			$this->object->getStatusCode(),
			200
		);
	}

	/**
	 * Tests the getBody method
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function testGetBody()
	{
		$this->assertEquals(
			$this->object->getBody(),
			'Some random string'
		);
	}

	/**
	 * Tests the setBody method
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function testSetBody()
	{
		$this->assertEquals(
			$this->object->setBody('foo'),
			$this->object
		);
	}

	/**
	 * Tests the setBody method
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function testSetHeader()
	{
		$value = $this->object->setHeader('bar', 'foo');

		$this->assertEquals(
			$value,
			$this->object
		);

		$this->assertEquals(
			$this->object->getHeader('bar'),
			'foo'
		);
	}	
}
