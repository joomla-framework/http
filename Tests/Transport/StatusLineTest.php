<?php

/**
 * @copyright  Copyright (C) 2005 - 2026 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Http\Tests\Transport;

use Joomla\Http\AbstractTransport;
use Joomla\Http\Exception\InvalidResponseCodeException;
use Joomla\Http\Transport\Curl;
use Joomla\Http\Transport\Socket;
use Joomla\Http\Transport\Stream;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Test class for the parsing of the status line by the transports.
 */
#[CoversClass(AbstractTransport::class)]
#[CoversClass(Curl::class)]
#[CoversClass(Socket::class)]
#[CoversClass(Stream::class)]
class StatusLineTest extends TestCase
{
    /**
     * Build a response through the protected getResponse() method of a transport.
     *
     * @param   string    $transportClass  The transport class.
     * @param   string[]  $headerLines     The header lines, starting with the status line.
     *
     * @return  mixed
     */
    private function getResponse(string $transportClass, array $headerLines)
    {
        if (!$transportClass::isSupported()) {
            $this->markTestSkipped(sprintf('The "%s" adapter is not supported in this environment.', $transportClass));
        }

        $transport = new $transportClass([]);
        $method    = new \ReflectionMethod($transport, 'getResponse');

        switch ($transportClass) {
            case Stream::class:
                return $method->invoke($transport, $headerLines, 'body');

            case Socket::class:
                return $method->invoke($transport, implode("\r\n", $headerLines) . "\r\n\r\nbody");

            default:
                $head = implode("\r\n", $headerLines) . "\r\n\r\n";

                return $method->invoke($transport, $head . 'body', ['header_size' => \strlen($head)]);
        }
    }

    public static function validStatusLineProvider(): \Generator
    {
        $lines = [
            'HTTP/1.1' => ['HTTP/1.1 404 Not Found', 404],
            'HTTP/1.0' => ['HTTP/1.0 200 OK', 200],
            'HTTP/2'   => ['HTTP/2 201', 201],
        ];

        foreach ([Curl::class, Socket::class, Stream::class] as $transport) {
            foreach ($lines as $name => [$line, $code]) {
                yield substr(strrchr($transport, '\\'), 1) . ', ' . $name => [$transport, $line, $code];
            }
        }
    }

    #[DataProvider('validStatusLineProvider')]
    public function testStatusCodeIsReadFromTheStatusLine(string $transportClass, string $statusLine, int $expected): void
    {
        $response = $this->getResponse($transportClass, [$statusLine, 'Content-Length: 12345']);

        $this->assertSame($expected, $response->getStatusCode());
    }

    public static function invalidStatusLineProvider(): \Generator
    {
        $lines = [
            'a header instead of the status line' => ['Content-Length: 12345'],
            'digits after the reason phrase'      => ['HTTP/1.1 OK 200'],
            'a four-digit code'                   => ['HTTP/1.1 2000 OK'],
        ];

        foreach ([Curl::class, Socket::class, Stream::class] as $transport) {
            foreach ($lines as $name => [$line]) {
                yield substr(strrchr($transport, '\\'), 1) . ', ' . $name => [$transport, [$line]];
            }
        }

        yield 'Stream, no headers at all' => [Stream::class, []];
    }

    #[DataProvider('invalidStatusLineProvider')]
    public function testResponseWithoutAValidStatusLineIsRejectedWithoutPhpErrors(string $transportClass, array $headerLines): void
    {
        $errors = [];

        set_error_handler(
            static function (int $severity, string $message) use (&$errors): bool {
                $errors[] = $message;

                return true;
            }
        );

        try {
            $this->getResponse($transportClass, $headerLines);
            $this->fail('An InvalidResponseCodeException was expected.');
        } catch (InvalidResponseCodeException $e) {
            $this->assertSame('No HTTP response code found.', $e->getMessage());
        } finally {
            restore_error_handler();
        }

        $this->assertSame([], $errors);
    }
}
