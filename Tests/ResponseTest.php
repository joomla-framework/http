<?php
/**
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Http\Tests;

use Joomla\Http\Response;
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
		$this->assertSame(
			200,
			(new Response('php://memory', 200, []))->code
		);
	}

	/**
	 * @testdox  The response body can be accessed through the deprecated property access
	 *
	 * @covers   Joomla\Http\Response
	 */
	public function testReadResponseBody()
	{
		$this->assertSame(
			'',
			(new Response('php://memory', 200, []))->body
		);
	}

	/**
	 * @testdox  The response headers can be accessed through the deprecated property access
	 *
	 * @covers   Joomla\Http\Response
	 */
	public function testReadResponseHeaders()
	{
		$this->assertSame(
			['Location' => ['https://example.com']],
			(new Response('php://memory', 200, ['Location' => ['https://example.com']]))->headers
		);
	}

	/**
	 * @testdox  Reading an unknown property generates an error
	 *
	 * @covers   Joomla\Http\Response
	 */
	public function testReadUnknownProperty()
	{
		$this->expectNotice();

		(new Response('php://memory', 200, ['Location' => ['https://example.com']]))->foo;
	}
}
