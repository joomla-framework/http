<?php
/**
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Http\Tests;

use Joomla\Http\Http;
use Joomla\Http\Response;
use Joomla\Http\TransportInterface;
use Joomla\Uri\Uri;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Zend\Diactoros\Request;

/**
 * Test class for Joomla\Http\Http.
 */
class HttpTest extends TestCase
{
	/**
	 * Options for the Http object.
	 *
	 * @var  array
	 */
	protected $options = [];

	/**
	 * Mock transport object.
	 *
	 * @var  TransportInterface|MockObject
	 */
	protected $transport;

	/**
	 * Object under test.
	 *
	 * @var  Http
	 */
	protected $object;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return  void
	 */
	protected function setUp(): void
	{
		parent::setUp();

		$this->transport = $this->createMock(TransportInterface::class);

		$this->object = new Http($this->options, $this->transport);
	}

	/**
	 * Tests the constructor to ensure only arrays or ArrayAccess objects are allowed
	 */
	public function testConstructorDisallowsNonArrayObjects()
	{
		$this->expectException(\InvalidArgumentException::class);

		new Http(new \stdClass);
	}

	/**
	 * Tests the getOption and setOption methods
	 */
	public function testOptionManagement()
	{
		$this->object->setOption('testKey', 'testValue');

		$this->assertSame(
			'testValue',
			$this->object->getOption('testKey')
		);
	}

	/**
	 * Tests the options method
	 */
	public function testOptions()
	{
		$response = new Response;

		$this->transport->expects($this->once())
			->method('request')
			->with('OPTIONS', new Uri('http://example.com'), null, ['test' => 'testHeader'])
			->willReturn($response);

		$this->assertSame(
			$response,
			$this->object->options('http://example.com', ['test' => 'testHeader'])
		);
	}

	/**
	 * Tests the head method
	 */
	public function testHead()
	{
		// Set header option
		$this->object->setOption('headers', ['option' => 'optionHeader']);

		$response = new Response;

		$this->transport->expects($this->once())
			->method('request')
			->with(
				'HEAD',
				new Uri('http://example.com'),
				null,
				[
					'test'   => 'testHeader',
					'option' => 'optionHeader',
				]
			)
			->willReturn($response);

		$this->assertSame(
			$response,
			$this->object->head('http://example.com', ['test' => 'testHeader'])
		);
	}

	/**
	 * Tests the get method
	 */
	public function testGet()
	{
		// Set timeout option
		$this->object->setOption('timeout', 100);

		$response = new Response;

		$this->transport->expects($this->once())
			->method('request')
			->with(
				'GET',
				new Uri('http://example.com'),
				null,
				[
					'test' => 'testHeader',
				],
				100
			)
			->willReturn($response);

		$this->assertSame(
			$response,
			$this->object->get('http://example.com', ['test' => 'testHeader'])
		);
	}

	/**
	 * Tests the get method with an injected Uri object
	 */
	public function testGetWithUri()
	{
		// Set timeout option
		$this->object->setOption('timeout', 100);

		$response = new Response;

		$this->transport->expects($this->once())
			->method('request')
			->with(
				'GET',
				new Uri('http://example.com'),
				null,
				[
					'test' => 'testHeader',
				],
				100
			)
			->willReturn($response);

		$this->assertSame(
			$response,
			$this->object->get(new Uri('http://example.com'), ['test' => 'testHeader'])
		);
	}

	/**
	 * Tests the get method with an invalid object for the URL
	 */
	public function testGetWithInvalidUrl()
	{
		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessage('A string or Joomla\Uri\UriInterface object must be provided, a "array" was provided.');

		$this->object->get([]);
	}

	/**
	 * Tests the post method
	 */
	public function testPost()
	{
		$response = new Response;

		$this->transport->expects($this->once())
			->method('request')
			->with(
				'POST',
				new Uri('http://example.com'),
				[
					'key' => 'value',
				],
				[
					'test' => 'testHeader',
				]
			)
			->willReturn($response);

		$this->assertSame(
			$response,
			$this->object->post('http://example.com', ['key' => 'value'], ['test' => 'testHeader'])
		);
	}

	/**
	 * Tests the put method
	 */
	public function testPut()
	{
		$response = new Response;

		$this->transport->expects($this->once())
			->method('request')
			->with(
				'PUT',
				new Uri('http://example.com'),
				[
					'key' => 'value',
				],
				[
					'test' => 'testHeader',
				]
			)
			->willReturn($response);

		$this->assertSame(
			$response,
			$this->object->put('http://example.com', ['key' => 'value'], ['test' => 'testHeader'])
		);
	}

	/**
	 * Tests the delete method
	 */
	public function testDelete()
	{
		$response = new Response;

		$this->transport->expects($this->once())
			->method('request')
			->with(
				'DELETE',
				new Uri('http://example.com'),
				null,
				[
					'test' => 'testHeader',
				]
			)
			->willReturn($response);

		$this->assertSame(
			$response,
			$this->object->delete('http://example.com', ['test' => 'testHeader'])
		);
	}

	/**
	 * Tests the trace method
	 */
	public function testTrace()
	{
		$response = new Response;

		$this->transport->expects($this->once())
			->method('request')
			->with(
				'TRACE',
				new Uri('http://example.com'),
				null,
				[
					'test' => 'testHeader',
				]
			)
			->willReturn($response);

		$this->assertSame(
			$response,
			$this->object->trace('http://example.com', ['test' => 'testHeader'])
		);
	}

	/**
	 * Tests the patch method
	 */
	public function testPatch()
	{
		$response = new Response;

		$this->transport->expects($this->once())
			->method('request')
			->with(
				'PATCH',
				new Uri('http://example.com'),
				[
					'key' => 'value',
				],
				[
					'test' => 'testHeader',
				]
			)
			->willReturn($response);

		$this->assertSame(
			$response,
			$this->object->patch('http://example.com', ['key' => 'value'], ['test' => 'testHeader'])
		);
	}

	/**
	 * Tests the sendRequest method
	 */
	public function testSendRequest()
	{
		$response = new Response;

		$this->transport->expects($this->once())
			->method('request')
			->with(
				'GET',
				new Uri('http://example.com'),
				'',
				[
					'Host'       => ['example.com'],
					'testHeader' => [''],
				]
			)
			->willReturn($response);

		$request = new Request('http://example.com', 'GET');
		$request = $request->withHeader('testHeader', '');

		$this->assertSame(
			$response,
			$this->object->sendRequest($request)
		);
	}
}
