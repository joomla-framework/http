<?php

/**
 * @copyright  Copyright (C) 2005 - 2021 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Http\Tests;

use Joomla\Http\Response;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

/**
 * Test class for Joomla\Http\Response.
 */
#[CoversClass(Response::class)]
class ResponseTest extends TestCase
{
    #[TestDox('The status code can be accessed through the deprecated property access')]
    public function testReadResponseCode()
    {
        $this->assertSame(
            200,
            (new Response('php://memory', 200, []))->getStatusCode()
        );
    }

    #[TestDox('The response body can be accessed through the deprecated property access')]
    public function testReadResponseBody()
    {
        $this->assertSame(
            '',
            (new Response('php://memory', 200, []))->getBody()->getContents()
        );
    }

    #[TestDox('The response headers can be accessed through the deprecated property access')]
    public function testReadResponseHeaders()
    {
        $this->assertSame(
            ['Location' => ['https://example.com']],
            (new Response('php://memory', 200, ['Location' => ['https://example.com']]))->getHeaders()
        );
    }
}
