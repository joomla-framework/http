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
	 * Tests deprecated access to the response code.
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
	 * Tests deprecated access to the response body.
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
	 * Tests deprecated access to the response headers.
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
	 * Tests reading an unknown property creates an error.
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
			$this->assertStringStartsWith('Undefined property via __get(): foo', $exception->getMessage());
		}
	}
}
