<?php
/**
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Http\Tests;

use Joomla\Http\Response;
use PHPUnit\Framework\Error\Notice;
use PHPUnit\Framework\TestCase;

/**
 * Test class for Joomla\Http\Response.
 */
class ResponseTest extends TestCase
{
	/**
	 * @testdox  The status code can be accessed through the deprecated property access
	 *
	 * @covers   Joomla\Http\Response
	 */
	public function testReadResponseCode()
	{
		$response = new Response('php://memory', 200, []);

		$this->assertSame(
			200,
			$response->code
		);
	}

	/**
	 * @testdox  The response body can be accessed through the deprecated property access
	 *
	 * @covers   Joomla\Http\Response
	 */
	public function testReadResponseBody()
	{
		$response = new Response('php://memory', 200, []);

		$this->assertSame(
			'',
			$response->body
		);
	}

	/**
	 * @testdox  The response headers can be accessed through the deprecated property access
	 *
	 * @covers   Joomla\Http\Response
	 */
	public function testReadResponseHeaders()
	{
		$response = new Response('php://memory', 200, ['Location' => ['https://example.com']]);

		$this->assertSame(
			['Location' => ['https://example.com']],
			$response->headers
		);
	}

	/**
	 * @testdox  Reading an unknown property generates an error
	 *
	 * @covers   Joomla\Http\Response
	 */
	public function testReadUnknownProperty()
	{
		$response = new Response('php://memory', 200, ['Location' => ['https://example.com']]);

		try
		{
			$response->foo;

			$this->fail('Reading an unknown property should generate an error');
		}
		catch (Notice $exception)
		{
			$this->assertSame(E_USER_NOTICE, $exception->getCode());
			$this->assertStringStartsWith('Undefined property via __get(): foo', $exception->getMessage());
		}
	}
}
