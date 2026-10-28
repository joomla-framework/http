<?php

/**
 * @copyright  Copyright (C) 2005 - 2021 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Http\Tests;

use Joomla\Http\AbstractTransport;
use Joomla\Http\Transport\Curl;
use Joomla\Http\Transport\Socket;
use Joomla\Http\Transport\Stream;
use Joomla\Http\TransportInterface;
use Joomla\Uri\Uri;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

use function array_merge;
use function json_decode;
use function sprintf;

/**
 * Test class for Joomla\Http\TransportInterface instances.
 *
 * @since  1.0
 */
#[CoversClass(Curl::class)]
#[CoversClass(Socket::class)]
#[CoversClass(Stream::class)]
#[UsesClass(AbstractTransport::class)]
class TransportTest extends TestCase
{
    /**
     * Options for the Transport object.
     *
     * @var  array
     */
    protected $options = [
        'transport.curl'   => [CURLOPT_SSL_VERIFYPEER => false],
        'transport.socket' => ['X-Joomla-Test: true'],
        'transport.stream' => ['ignore_errors' => true],
    ];

    /**
     * The URL string for the HTTP stub.
     *
     * @var  string
     */
    protected $stubUrl;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     *
     * @return  void
     */
    protected function setUp(): void
    {
        parent::setUp();

        if (!\defined('JTEST_HTTP_STUB') && getenv('JTEST_HTTP_STUB') == '') {
            $this->markTestSkipped('The Transport test stub has not been configured');
        }

        $this->stubUrl = \defined('JTEST_HTTP_STUB') ? JTEST_HTTP_STUB : getenv('JTEST_HTTP_STUB');
    }

    /**
     * Data provider for the request test methods.
     *
     * @return  array
     */
    public static function transportProvider(): array
    {
        return [
            'curl adapter' => [Curl::class],
            'socket adapter' => [Socket::class],
            'stream adapter' => [Stream::class],
        ];
    }

    /**
     * Data provider for the request test methods.
     *
     * @return  array
     */
    public static function relevantOnlyForStreamTransportProvider(): array
    {
        return [
            'stream adapter' => [Stream::class],
        ];
    }

    /**
     * @param   string  $transportClass  The transport class to test
     */
    #[DataProvider('transportProvider')]
    #[TestDox('A transport can only be created with an appropriate data type for the options')]
    public function testConstructorWithBadDataObject(string $transportClass)
    {
        if (!$transportClass::isSupported()) {
            $this->markTestSkipped(sprintf('The "%s" adapter is not supported in this environment.', $transportClass));
        }

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The options param must be an array or implement the ArrayAccess interface.');

        new $transportClass(new \stdClass());
    }

    /**
     * @param   string  $transportClass  The transport class to test
     */
    #[DataProvider('transportProvider')]
    #[TestDox('A transport can make a GET request')]
    public function testRequestGet(string $transportClass)
    {
        if (!$transportClass::isSupported()) {
            $this->markTestSkipped(sprintf('The "%s" adapter is not supported in this environment.', $transportClass));
        }

        /** @var TransportInterface $transport */
        $transport = new $transportClass($this->options);

        $response = $transport->request('GET', new Uri($this->stubUrl));

        $body = json_decode((string)$response->getBody());

        $this->assertSame(
            200,
            $response->getStatusCode()
        );

        $this->assertSame(
            'GET',
            $body->method
        );
    }

    /**
     * @param   string  $transportClass  The transport class to test
     *
     *  Blocking mode is only relevant for OPTION,HEAD,GET request since it only affects reading from the stream
     * @see           https://www.php.net/manual/en/function.stream-set-blocking.php
     */
    #[DataProvider('relevantOnlyForStreamTransportProvider')]
    #[TestDox('A stream transport can make a GET request when blocking mode is enabled')]
    public function testRequestGetWhenBlockingModeIsEnabled(string $transportClass)
    {
        if (!$transportClass::isSupported() || $transportClass != Stream::class) {
            $this->markTestSkipped(sprintf('The "%s" adapter is not supported in this environment.', $transportClass));
        }

        /** @var TransportInterface $transport */
        $transport = new $transportClass(array_merge($this->options['transport.stream'], ['set_blocking' => true]));

        $response = $transport->request('GET', new Uri($this->stubUrl));

        $body = json_decode((string)$response->getBody());

        $this->assertSame(
            200,
            $response->getStatusCode()
        );

        $this->assertSame(
            'GET',
            $body->method
        );
    }

    /**
     * @param   string  $transportClass  The transport class to test
     *
     *  Blocking mode is only relevant for OPTION,HEAD,GET request since it only affects reading from the stream
     * @see           https://www.php.net/manual/en/function.stream-set-blocking.php
     */
    #[DataProvider('relevantOnlyForStreamTransportProvider')]
    #[TestDox('A stream transport can make a GET request when blocking mode is disabled')]
    public function testRequestGetWhenBlockingModeIsDisabled(string $transportClass)
    {
        if (!$transportClass::isSupported() || $transportClass != Stream::class) {
            $this->markTestSkipped(sprintf('The "%s" adapter is not supported in this environment.', $transportClass));
        }

        /** @var TransportInterface $transport */
        $transport = new $transportClass(array_merge($this->options['transport.stream'], ['set_blocking' => false]));

        $response = $transport->request('GET', new Uri($this->stubUrl));

        $body = json_decode((string)$response->getBody());

        $this->assertSame(
            200,
            $response->getStatusCode()
        );

        $this->assertSame(
            'GET',
            $body->method
        );
    }


    /**
     * @param   string  $transportClass  The transport class to test
     */
    #[DataProvider('transportProvider')]
    #[TestDox('A transport fails to make a GET request to an invalid domain')]
    public function testBadDomainRequestGet(string $transportClass)
    {
        if (!$transportClass::isSupported()) {
            $this->markTestSkipped(sprintf('The "%s" adapter is not supported in this environment.', $transportClass));
        }

        $this->expectException(\RuntimeException::class);

        /** @var TransportInterface $transport */
        $transport = new $transportClass($this->options);

        $response = $transport->request('GET', new Uri('https://xommunity.joomla.org'));
    }

    /**
     * @param   string  $transportClass  The transport class to test
     */
    #[DataProvider('transportProvider')]
    #[TestDox('A transport fails to make a GET request to an invalid URL')]
    public function testRequestGet404(string $transportClass)
    {
        if (!$transportClass::isSupported()) {
            $this->markTestSkipped(sprintf('The "%s" adapter is not supported in this environment.', $transportClass));
        }

        /** @var TransportInterface $transport */
        $transport = new $transportClass($this->options);

        $response = $transport->request('GET', new Uri(str_replace('.php', '.html', $this->stubUrl)));

        $this->assertSame(
            404,
            $response->getStatusCode()
        );
    }

    /**
     * @param   string  $transportClass  The transport class to test
     */
    #[DataProvider('transportProvider')]
    #[TestDox('A transport can make a GET request')]
    public function testRequestPut(string $transportClass)
    {
        if (!$transportClass::isSupported()) {
            $this->markTestSkipped(sprintf('The "%s" adapter is not supported in this environment.', $transportClass));
        }

        /** @var TransportInterface $transport */
        $transport = new $transportClass($this->options);

        $response = $transport->request('PUT', new Uri($this->stubUrl));

        $body = json_decode((string)$response->getBody());

        $this->assertSame(
            200,
            $response->getStatusCode()
        );

        $this->assertSame(
            'PUT',
            $body->method
        );
    }

    /**
     * @param   string  $transportClass  The transport class to test
     */
    #[DataProvider('transportProvider')]
    #[TestDox('A transport can make a GET request with basic authentication')]
    public function testRequestCredentials(string $transportClass)
    {
        if (!$transportClass::isSupported()) {
            $this->markTestSkipped(sprintf('The "%s" adapter is not supported in this environment.', $transportClass));
        }

        /** @var TransportInterface $transport */
        $transport = new $transportClass($this->options);

        $uri = new Uri($this->stubUrl);
        $uri->setUser('username');
        $uri->setPass('password');

        $response = $transport->request('GET', $uri);

        $body = json_decode((string)$response->getBody());

        $this->assertSame(
            200,
            $response->getStatusCode()
        );

        $this->assertSame(
            'username',
            $body->username
        );

        $this->assertSame(
            'password',
            $body->password
        );
    }

    /**
     * @param   string  $transportClass  The transport class to test
     */
    #[DataProvider('transportProvider')]
    #[TestDox('A transport can make a POST request with an array as the request data')]
    public function testRequestPost(string $transportClass)
    {
        if (!$transportClass::isSupported()) {
            $this->markTestSkipped(sprintf('The "%s" adapter is not supported in this environment.', $transportClass));
        }

        /** @var TransportInterface $transport */
        $transport = new $transportClass($this->options);

        $response = $transport->request('POST', new Uri($this->stubUrl . '?test=okay'), ['key' => 'value']);

        $body = json_decode((string)$response->getBody());

        $this->assertSame(
            200,
            $response->getStatusCode()
        );

        $this->assertSame(
            'POST',
            $body->method
        );

        $this->assertSame(
            'value',
            $body->post->key
        );
    }

    /**
     * @param   string  $transportClass  The transport class to test
     */
    #[DataProvider('transportProvider')]
    #[TestDox('A transport can make a POST request with a scalar value as the request data')]
    public function testRequestPostScalar(string $transportClass)
    {
        if (!$transportClass::isSupported()) {
            $this->markTestSkipped(sprintf('The "%s" adapter is not supported in this environment.', $transportClass));
        }

        /** @var TransportInterface $transport */
        $transport = new $transportClass($this->options);

        $response = $transport->request('post', new Uri($this->stubUrl . '?test=okay'), 'key=value');

        $body = json_decode((string)$response->getBody());

        $this->assertSame(
            200,
            $response->getStatusCode()
        );

        $this->assertSame(
            'POST',
            $body->method
        );

        $this->assertSame(
            'value',
            $body->post->key
        );
    }
}
