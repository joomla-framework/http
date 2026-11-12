<?php

/**
 * @copyright  Copyright (C) 2005 - 2026 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Http\Tests;

use Joomla\Http\Http;
use Joomla\Http\Response;
use Joomla\Http\Transport\Curl;
use Joomla\Http\TransportInterface;
use Joomla\Uri\Uri;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Test class for the restriction of URI schemes.
 */
#[CoversClass(Http::class)]
#[CoversClass(Curl::class)]
class AllowedSchemesTest extends TestCase
{
    public static function rejectedUrlProvider(): array
    {
        return [
            'file'   => ['file:///etc/passwd'],
            'php'    => ['php://filter/resource=index.php'],
            'phar'   => ['phar://archive.phar/file.php'],
            'gopher' => ['gopher://127.0.0.1:6379/_INFO'],
            'dict'   => ['dict://127.0.0.1:11211/stats'],
            'data'   => ['data://text/plain;base64,SGVsbG8='],
        ];
    }

    #[DataProvider('rejectedUrlProvider')]
    public function testOnlyHttpAndHttpsAreAllowedByDefault(string $url): void
    {
        $transport = $this->createMock(TransportInterface::class);
        $transport->expects($this->never())->method('request');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('scheme is not allowed');

        (new Http([], $transport))->get($url);
    }

    public static function allowedUrlProvider(): array
    {
        return [
            'http'            => ['http://example.com/'],
            'https'           => ['https://example.com/'],
            'upper case HTTP' => ['HTTP://example.com/'],
        ];
    }

    #[DataProvider('allowedUrlProvider')]
    public function testHttpAndHttpsAreAllowed(string $url): void
    {
        $transport = $this->createMock(TransportInterface::class);
        $transport->expects($this->once())->method('request')->willReturn(new Response());

        (new Http([], $transport))->get($url);
    }

    public function testFurtherSchemesCanBeAllowed(): void
    {
        $transport = $this->createMock(TransportInterface::class);
        $transport->expects($this->once())->method('request')->willReturn(new Response());

        (new Http(['allowedSchemes' => ['http', 'https', 'ftp']], $transport))->get('ftp://example.com/file.txt');
    }

    public function testCurlTransportRefusesOtherProtocols(): void
    {
        if (!Curl::isSupported() || !\defined('CURLOPT_PROTOCOLS_STR')) {
            $this->markTestSkipped('cURL with CURLOPT_PROTOCOLS_STR is not available in this environment.');
        }

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/Protocol "file" (not supported or )?disabled/');

        (new Curl([]))->request('GET', new Uri('file:///etc/hostname'));
    }
}
