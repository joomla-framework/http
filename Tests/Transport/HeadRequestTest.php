<?php

/**
 * @copyright  Copyright (C) 2005 - 2026 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Http\Tests\Transport;

use Joomla\Http\Transport\Curl;
use Joomla\Http\Transport\Socket;
use Joomla\Http\Transport\Stream;
use Joomla\Uri\Uri;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Test class for HEAD requests, whose response announces a body that is not sent.
 */
#[CoversClass(Curl::class)]
#[CoversClass(Socket::class)]
#[CoversClass(Stream::class)]
class HeadRequestTest extends TestCase
{
    public static function transportAndMethodProvider(): \Generator
    {
        foreach ([Curl::class, Socket::class, Stream::class] as $transport) {
            foreach (['HEAD', 'head'] as $method) {
                yield substr(strrchr($transport, '\\'), 1) . ', ' . $method => [$transport, $method];
            }
        }
    }

    #[DataProvider('transportAndMethodProvider')]
    public function testHeadRequestDoesNotWaitForABody(string $transportClass, string $method): void
    {
        if (!$transportClass::isSupported()) {
            $this->markTestSkipped(sprintf('The "%s" adapter is not supported in this environment.', $transportClass));
        }

        if (!\defined('JTEST_HTTP_STUB') && getenv('JTEST_HTTP_STUB') == '') {
            $this->markTestSkipped('The Transport test stub has not been configured');
        }

        $stubUrl = \defined('JTEST_HTTP_STUB') ? JTEST_HTTP_STUB : getenv('JTEST_HTTP_STUB');
        $url     = substr($stubUrl, 0, strrpos($stubUrl, '/') + 1) . 'content_length.php';

        $response = (new $transportClass([]))->request($method, new Uri($url), null, [], 5);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('100', $response->getHeaderLine('Content-Length'));
        $this->assertSame('', (string) $response->getBody());
    }
}
