<?php

/**
 * @copyright  Copyright (C) 2005 - 2026 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Http\Tests;

use Joomla\Http\Http;
use Joomla\Http\HttpFactory;
use Joomla\Http\Response;
use Joomla\Http\TransportInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Test class for the default user agent.
 */
#[CoversClass(Http::class)]
#[CoversClass(HttpFactory::class)]
class HttpUserAgentTest extends TestCase
{
    /**
     * Create a transport that expects the given user agent.
     *
     * @param   string  $userAgent  The expected user agent.
     *
     * @return  TransportInterface
     */
    private function createTransportExpectingUserAgent(string $userAgent): TransportInterface
    {
        $transport = $this->createMock(TransportInterface::class);
        $transport->expects($this->once())
            ->method('request')
            ->with('GET', $this->anything(), null, [], null, $userAgent)
            ->willReturn(new Response());

        return $transport;
    }

    public function testDefaultUserAgentIsSentByAClientCreatedDirectly(): void
    {
        (new Http([], $this->createTransportExpectingUserAgent('Mozilla/5.0 Joomla!/Framework')))->get('http://example.com');
    }

    public function testConfiguredUserAgentIsSent(): void
    {
        (new Http(['userAgent' => 'MyApp/1.0'], $this->createTransportExpectingUserAgent('MyApp/1.0')))->get('http://example.com');
    }

    public function testFactoryDoesNotChangeTheOptionsObject(): void
    {
        $options = new \ArrayObject(['timeout' => 5]);

        (new HttpFactory())->getHttp($options);

        $this->assertSame(['timeout' => 5], $options->getArrayCopy());
    }
}
