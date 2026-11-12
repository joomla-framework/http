<?php

/**
 * @copyright  Copyright (C) 2005 - 2026 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Http\Tests;

use Joomla\Http\Exception\InvalidResponseCodeException;
use Joomla\Http\Exception\NetworkException;
use Joomla\Http\Http;
use Joomla\Http\Response;
use Joomla\Http\TransportInterface;
use Laminas\Diactoros\Request;
use Laminas\Diactoros\Stream;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\NetworkExceptionInterface;

/**
 * Test class for Joomla\Http\Http::sendRequest().
 */
#[CoversClass(Http::class)]
#[CoversClass(NetworkException::class)]
class HttpSendRequestTest extends TestCase
{
    /**
     * Create a POST request with the given body.
     *
     * @param   string  $body  The request body.
     *
     * @return  Request
     */
    private function createRequest(string $body): Request
    {
        $stream = new Stream('php://memory', 'rw');
        $stream->write($body);

        return new Request('http://example.com', 'POST', $stream);
    }

    /**
     * Create a client whose transport expects the given request data.
     *
     * @param   mixed  $data  The data the transport must receive.
     *
     * @return  Http
     */
    private function createClientExpectingData($data): Http
    {
        $transport = $this->createMock(TransportInterface::class);
        $transport->expects($this->once())
            ->method('request')
            ->with('POST', $this->anything(), $this->identicalTo($data))
            ->willReturn(new Response());

        return new Http([], $transport);
    }

    public function testBodyIsSentFromTheStartOfTheStream(): void
    {
        // Writing leaves the stream at its end, as does reading it.
        $request = $this->createRequest('key=value');

        $this->createClientExpectingData('key=value')->sendRequest($request);
    }

    public function testBodyThatWasAlreadyReadIsSentCompletely(): void
    {
        $request = $this->createRequest('key=value');
        $request->getBody()->rewind();
        $request->getBody()->getContents();

        $this->createClientExpectingData('key=value')->sendRequest($request);
    }

    public function testBodyZeroIsSent(): void
    {
        $this->createClientExpectingData('0')->sendRequest($this->createRequest('0'));
    }

    public function testEmptyBodyIsSentAsNoData(): void
    {
        $this->createClientExpectingData(null)->sendRequest($this->createRequest(''));
    }

    public function testTransportFailureIsThrownAsNetworkException(): void
    {
        $failure   = new \RuntimeException('Could not connect', 7);
        $transport = $this->createMock(TransportInterface::class);
        $transport->expects($this->once())
            ->method('request')
            ->willThrowException($failure);

        $request = $this->createRequest('');

        try {
            (new Http([], $transport))->sendRequest($request);
            $this->fail('A NetworkException was expected.');
        } catch (NetworkException $e) {
            $this->assertInstanceOf(NetworkExceptionInterface::class, $e);
            $this->assertInstanceOf(\RuntimeException::class, $e, 'The exception must stay a RuntimeException for existing callers.');
            $this->assertSame($request, $e->getRequest());
            $this->assertSame('Could not connect', $e->getMessage());
            $this->assertSame(7, $e->getCode());
            $this->assertSame($failure, $e->getPrevious());
        }
    }

    public function testClientExceptionsAreNotWrapped(): void
    {
        $failure   = new InvalidResponseCodeException('No HTTP response code found.');
        $transport = $this->createMock(TransportInterface::class);
        $transport->expects($this->once())
            ->method('request')
            ->willThrowException($failure);

        try {
            (new Http([], $transport))->sendRequest($this->createRequest(''));
            $this->fail('An exception was expected.');
        } catch (\Exception $e) {
            $this->assertSame($failure, $e);
        }
    }
}
