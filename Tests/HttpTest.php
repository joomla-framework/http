<?php

/**
 * @copyright  Copyright (C) 2005 - 2021 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Http\Tests;

use Joomla\Http\Http;
use Joomla\Http\Response;
use Joomla\Http\TransportInterface;
use Joomla\Uri\Uri;
use Laminas\Diactoros\Request;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test class for Joomla\Http\Http.
 */
#[CoversClass(Http::class)]
class HttpTest extends TestCase
{
    /**
     * Options for the Http object.
     *
     * @var  array
     */
    protected $options = [];

    /**
     * Mock transport object.
     *
     * @var  TransportInterface|MockObject
     */
    protected $transport;

    /**
     * Object under test.
     *
     * @var  Http
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     *
     * @return  void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->transport = $this->createMock(TransportInterface::class);

        $this->object = new Http($this->options, $this->transport);
    }

    #[AllowMockObjectsWithoutExpectations]
    #[TestDox('The constructor disallows invalid data objects')]
    public function testConstructorDisallowsNonArrayObjects()
    {
        $this->expectException(\InvalidArgumentException::class);

        new Http(new \stdClass());
    }

    #[AllowMockObjectsWithoutExpectations]
    #[TestDox("The driver's options can be managed")]
    public function testOptionManagement()
    {
        $this->object->setOption('testKey', 'testValue');

        $this->assertSame(
            'testValue',
            $this->object->getOption('testKey')
        );
    }

    #[TestDox('A OPTIONS request can be sent')]
    public function testOptions()
    {
        $response = new Response();

        $this->transport->expects($this->once())
            ->method('request')
            ->with('OPTIONS', new Uri('http://example.com'), null, ['test' => 'testHeader'])
            ->willReturn($response);

        $this->assertSame(
            $response,
            $this->object->options('http://example.com', ['test' => 'testHeader'])
        );
    }

    #[TestDox('A HEAD request can be sent')]
    public function testHead()
    {
        // Set header option
        $this->object->setOption('headers', ['option' => 'optionHeader']);

        $response = new Response();

        $this->transport->expects($this->once())
            ->method('request')
            ->with(
                'HEAD',
                new Uri('http://example.com'),
                null,
                [
                    'test'   => 'testHeader',
                    'option' => 'optionHeader',
                ]
            )
            ->willReturn($response);

        $this->assertSame(
            $response,
            $this->object->head('http://example.com', ['test' => 'testHeader'])
        );
    }

    #[TestDox('A GET request can be sent')]
    public function testGet()
    {
        // Set timeout option
        $this->object->setOption('timeout', 100);

        $response = new Response();

        $this->transport->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                new Uri('http://example.com'),
                null,
                [
                    'test' => 'testHeader',
                ],
                100
            )
            ->willReturn($response);

        $this->assertSame(
            $response,
            $this->object->get('http://example.com', ['test' => 'testHeader'])
        );
    }

    #[TestDox('A GET request can be sent when passing a URI object')]
    public function testGetWithUri()
    {
        // Set timeout option
        $this->object->setOption('timeout', 100);

        $response = new Response();

        $this->transport->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                new Uri('http://example.com'),
                null,
                [
                    'test' => 'testHeader',
                ],
                100
            )
            ->willReturn($response);

        $this->assertSame(
            $response,
            $this->object->get(new Uri('http://example.com'), ['test' => 'testHeader'])
        );
    }

    #[AllowMockObjectsWithoutExpectations]
    #[TestDox('Sending a GET request fails with an invalid data type for the URI')]
    public function testGetWithInvalidUrl()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('A string or Joomla\Uri\UriInterface object must be provided, a "array" was provided.');

        $this->object->get([]);
    }

    #[TestDox('A POST request can be sent')]
    public function testPost()
    {
        $response = new Response();

        $this->transport->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                new Uri('http://example.com'),
                [
                    'key' => 'value',
                ],
                [
                    'test' => 'testHeader',
                ]
            )
            ->willReturn($response);

        $this->assertSame(
            $response,
            $this->object->post('http://example.com', ['key' => 'value'], ['test' => 'testHeader'])
        );
    }

    #[TestDox('A PUT request can be sent')]
    public function testPut()
    {
        $response = new Response();

        $this->transport->expects($this->once())
            ->method('request')
            ->with(
                'PUT',
                new Uri('http://example.com'),
                [
                    'key' => 'value',
                ],
                [
                    'test' => 'testHeader',
                ]
            )
            ->willReturn($response);

        $this->assertSame(
            $response,
            $this->object->put('http://example.com', ['key' => 'value'], ['test' => 'testHeader'])
        );
    }

    #[TestDox('A DELETE request can be sent')]
    public function testDelete()
    {
        $response = new Response();

        $this->transport->expects($this->once())
            ->method('request')
            ->with(
                'DELETE',
                new Uri('http://example.com'),
                null,
                [
                    'test' => 'testHeader',
                ]
            )
            ->willReturn($response);

        $this->assertSame(
            $response,
            $this->object->delete('http://example.com', ['test' => 'testHeader'])
        );
    }

    #[TestDox('A TRACE request can be sent')]
    public function testTrace()
    {
        $response = new Response();

        $this->transport->expects($this->once())
            ->method('request')
            ->with(
                'TRACE',
                new Uri('http://example.com'),
                null,
                [
                    'test' => 'testHeader',
                ]
            )
            ->willReturn($response);

        $this->assertSame(
            $response,
            $this->object->trace('http://example.com', ['test' => 'testHeader'])
        );
    }

    #[TestDox('A PATCH request can be sent')]
    public function testPatch()
    {
        $response = new Response();

        $this->transport->expects($this->once())
            ->method('request')
            ->with(
                'PATCH',
                new Uri('http://example.com'),
                [
                    'key' => 'value',
                ],
                [
                    'test' => 'testHeader',
                ]
            )
            ->willReturn($response);

        $this->assertSame(
            $response,
            $this->object->patch('http://example.com', ['key' => 'value'], ['test' => 'testHeader'])
        );
    }

    #[TestDox('A request can be sent using a PSR-18 RequestInterface')]
    public function testSendRequest()
    {
        $response = new Response();

        $this->transport->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                new Uri('http://example.com'),
                '',
                [
                    'Host'       => ['example.com'],
                    'testHeader' => [''],
                ]
            )
            ->willReturn($response);

        $request = new Request('http://example.com', 'GET');
        $request = $request->withHeader('testHeader', '');

        $this->assertSame(
            $response,
            $this->object->sendRequest($request)
        );
    }
}
