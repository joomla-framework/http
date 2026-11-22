<?php

/**
 * @copyright  Copyright (C) 2005 - 2026 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Http\Tests;

use Joomla\Http\Http;
use Joomla\Http\Response;
use Joomla\Http\TransportInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Test class for the headers option of Joomla\Http\Http.
 */
#[CoversClass(Http::class)]
class HttpOptionHeadersTest extends TestCase
{
    public function testRequestHeaderOverridesOptionHeaderInAnyCase(): void
    {
        $transport = $this->createMock(TransportInterface::class);
        $transport->expects($this->once())
            ->method('request')
            ->with('GET', $this->anything(), null, ['accept' => 'application/json', 'X-Option' => 'set'])
            ->willReturn(new Response());

        $http = new Http(['headers' => ['Accept' => 'text/html', 'X-Option' => 'set']], $transport);

        $http->get('http://example.com', ['accept' => 'application/json']);
    }
}
