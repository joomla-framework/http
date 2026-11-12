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
 * Test class for header names in any case and header values given as lists.
 */
#[CoversClass(AbstractTransport::class)]
#[CoversClass(Curl::class)]
#[CoversClass(Socket::class)]
#[CoversClass(Stream::class)]
class HeaderNormalisationTest extends TestCase
{
    /**
     * Create a transport that exposes the protected header helpers.
     *
     * @return  AbstractTransport
     */
    private function createTransport(): AbstractTransport
    {
        return new class () extends AbstractTransport {
            public function request($method, $uri, $data = null, array $headers = [], $timeout = null, $userAgent = null)
            {
            }

            public static function isSupported()
            {
                return true;
            }

            public function value(array $headers, string $name): ?string
            {
                return $this->getHeaderValue($headers, $name);
            }

            public function set(array $headers, string $name, $value): array
            {
                return $this->setHeader($headers, $name, $value);
            }
        };
    }

    public function testHeaderValueIsFoundInAnyCase(): void
    {
        $transport = $this->createTransport();

        $this->assertSame('application/json', $transport->value(['content-type' => 'application/json'], 'Content-Type'));
        $this->assertSame('close', $transport->value(['CONNECTION' => 'close'], 'Connection'));
        $this->assertNull($transport->value(['Accept' => 'text/html'], 'Content-Type'));
    }

    public function testListValuesAreJoined(): void
    {
        $this->assertSame('gzip, deflate', $this->createTransport()->value(['Accept-Encoding' => ['gzip', 'deflate']], 'accept-encoding'));
    }

    public function testSetHeaderReplacesTheHeaderInAnyCase(): void
    {
        $this->assertSame(
            ['Accept' => 'text/html', 'Content-Length' => 7],
            $this->createTransport()->set(['content-length' => 3, 'Accept' => 'text/html'], 'Content-Length', 7)
        );
    }

    public static function transportProvider(): array
    {
        return [
            'curl adapter'   => [Curl::class],
            'socket adapter' => [Socket::class],
            'stream adapter' => [Stream::class],
        ];
    }

    /**
     * Get the URL of the stub, or skip the test.
     *
     * @param   string  $transportClass  The transport class to test
     *
     * @return  string
     */
    private function getStubUrl(string $transportClass): string
    {
        if (!$transportClass::isSupported()) {
            $this->markTestSkipped(sprintf('The "%s" adapter is not supported in this environment.', $transportClass));
        }

        if (!\defined('JTEST_HTTP_STUB') && getenv('JTEST_HTTP_STUB') == '') {
            $this->markTestSkipped('The Transport test stub has not been configured');
        }

        return \defined('JTEST_HTTP_STUB') ? JTEST_HTTP_STUB : getenv('JTEST_HTTP_STUB');
    }

    #[DataProvider('transportProvider')]
    public function testLowerCaseContentTypeIsNotOverridden(string $transportClass): void
    {
        $url = $this->getStubUrl($transportClass);

        $response = (new $transportClass([]))->request('POST', new Uri($url), '{"key":"value"}', ['content-type' => 'application/json']);
        $headers  = array_change_key_case((array) json_decode((string) $response->getBody())->headers);

        $this->assertSame('application/json', $headers['content-type']);
    }

    /**
     * Send a request and fail on any PHP warning or notice raised meanwhile.
     *
     * @param   callable  $request  The request to send.
     *
     * @return  mixed  The response.
     */
    private function sendWithoutErrors(callable $request)
    {
        $errors = [];

        set_error_handler(
            static function (int $severity, string $message) use (&$errors): bool {
                $errors[] = $message;

                return true;
            }
        );

        try {
            $response = $request();
        } finally {
            restore_error_handler();
        }

        $this->assertSame([], $errors);

        return $response;
    }

    #[DataProvider('transportProvider')]
    public function testListValuedAcceptEncodingIsSent(string $transportClass): void
    {
        $url = $this->getStubUrl($transportClass);

        $response = $this->sendWithoutErrors(
            static fn () => (new $transportClass([]))->request('GET', new Uri($url), null, ['Accept-Encoding' => ['identity']])
        );

        $this->assertSame('identity', json_decode((string) $response->getBody())->http_accept_encoding);
    }

    public function testListValuedMultipartContentTypeIsUsedByCurl(): void
    {
        $url = $this->getStubUrl(Curl::class);

        $response = $this->sendWithoutErrors(
            static fn () => (new Curl([]))->request('POST', new Uri($url), ['key' => 'value'], ['Content-Type' => ['multipart/form-data']])
        );

        $this->assertSame('value', json_decode((string) $response->getBody())->post->key);
    }
}
