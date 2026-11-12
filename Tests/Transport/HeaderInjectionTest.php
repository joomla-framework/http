<?php

/**
 * @copyright  Copyright (C) 2005 - 2026 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Http\Tests\Transport;

use Joomla\Http\AbstractTransport;
use Joomla\Http\Transport\Curl;
use Joomla\Http\Transport\Socket;
use Joomla\Http\Transport\Stream;
use Joomla\Uri\Uri;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Test class for the rejection of header injection by the transports.
 */
#[CoversClass(AbstractTransport::class)]
#[CoversClass(Curl::class)]
#[CoversClass(Socket::class)]
#[CoversClass(Stream::class)]
class HeaderInjectionTest extends TestCase
{
    public static function transportAndHeadersProvider(): \Generator
    {
        $headers = [
            'CRLF in a value'         => [['X-Test' => "a\r\nX-Injected: 1"], null],
            'LF in a value'           => [['X-Test' => "a\nX-Injected: 1"], null],
            'CR in a value'           => [['X-Test' => "a\rX-Injected: 1"], null],
            'NUL in a value'          => [['X-Test' => "a\0b"], null],
            'CRLF in a list value'    => [['X-Test' => ['ok', "a\r\nX-Injected: 1"]], null],
            'colon in a name'         => [['X-Test: 1' . "\r\n" . 'X-Injected' => '1'], null],
            'space in a name'         => [['X Test' => '1'], null],
            'empty name'              => [['' => '1'], null],
            'CRLF in the user agent'  => [[], "agent\r\nX-Injected: 1"],
        ];

        foreach ([Curl::class, Socket::class, Stream::class] as $transport) {
            foreach ($headers as $name => [$requestHeaders, $userAgent]) {
                yield substr(strrchr($transport, '\\'), 1) . ', ' . $name => [$transport, $requestHeaders, $userAgent];
            }
        }
    }

    #[DataProvider('transportAndHeadersProvider')]
    public function testHeadersThatWouldInjectFurtherHeadersAreRejected(string $transportClass, array $headers, ?string $userAgent): void
    {
        if (!$transportClass::isSupported()) {
            $this->markTestSkipped(sprintf('The "%s" adapter is not supported in this environment.', $transportClass));
        }

        // The socket transport connects before it builds the request, so it needs a server.
        $url = \defined('JTEST_HTTP_STUB') ? JTEST_HTTP_STUB : (string) getenv('JTEST_HTTP_STUB');

        if ($url === '') {
            if ($transportClass === Socket::class) {
                $this->markTestSkipped('The Transport test stub has not been configured');
            }

            $url = 'http://127.0.0.1:1/';
        }

        $this->expectException(\InvalidArgumentException::class);

        (new $transportClass([]))->request('GET', new Uri($url), null, $headers, null, $userAgent);
    }
}
