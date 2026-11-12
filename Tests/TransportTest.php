<?php

/**
 * @copyright  Copyright (C) 2005 - 2021 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Http\Tests;

use Joomla\Http\Transport\Curl;
use Joomla\Http\Transport\Socket;
use Joomla\Http\Transport\Stream;
use Joomla\Http\TransportInterface;
use Joomla\Uri\Uri;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function array_merge;
use function json_decode;
use function sprintf;

/**
 * Test class for Joomla\Http\TransportInterface instances.
 *
 * @since  1.0
 */
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

        /*
         * These are integration tests: they need a web server serving Tests/stubs/jhttp_stub.php
         * at the configured URL. phpunit.xml.dist points at a default location, so a fresh
         * checkout would otherwise report a wall of assertion failures rather than the actual
         * problem. Skip instead when the stub cannot be reached.
         */
        if (!self::stubIsReachable($this->stubUrl)) {
            $this->markTestSkipped(
                sprintf(
                    'The Transport test stub at "%s" is not reachable. Serve Tests/stubs/ from a web'
                    . ' server and point JTEST_HTTP_STUB at jhttp_stub.php to run these tests.',
                    $this->stubUrl
                )
            );
        }
    }

    /**
     * Check whether the configured stub answers, so the suite can skip rather than fail.
     *
     * The result is cached for the process; the stub either runs for the whole suite or not at all.
     *
     * @param   string  $url  The stub URL.
     *
     * @return  boolean
     */
    private static function stubIsReachable(string $url): bool
    {
        static $reachable = null;

        if ($reachable !== null) {
            return $reachable;
        }

        $context = stream_context_create([
            'http' => ['method' => 'HEAD', 'timeout' => 2, 'ignore_errors' => true],
        ]);

        $headers = @get_headers($url, false, $context);

        return $reachable = \is_array($headers)
            && preg_match('#\s(2|3)\d\d\s#', $headers[0] ?? '') === 1;
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
     * @testdox       A transport can only be created with an appropriate data type for the options
     *
     * @param   string  $transportClass  The transport class to test
     *
     * @covers        \Joomla\Http\Transport\Curl
     * @covers        \Joomla\Http\Transport\Socket
     * @covers        \Joomla\Http\Transport\Stream
     * @uses          \Joomla\Http\AbstractTransport
     */
    #[DataProvider('transportProvider')]
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
     * @testdox       A transport can make a GET request
     *
     * @param   string  $transportClass  The transport class to test
     *
     * @covers        \Joomla\Http\Transport\Curl
     * @covers        \Joomla\Http\Transport\Socket
     * @covers        \Joomla\Http\Transport\Stream
     * @uses          \Joomla\Http\AbstractTransport
     */
    #[DataProvider('transportProvider')]
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
     * @testdox       A stream transport can make a GET request when blocking mode is enabled
     *
     * @param   string  $transportClass  The transport class to test
     *
     * @covers        \Joomla\Http\Transport\Stream
     * @uses          \Joomla\Http\AbstractTransport
     *
     *  Blocking mode is only relevant for OPTION,HEAD,GET request since it only affects reading from the stream
     * @see           https://www.php.net/manual/en/function.stream-set-blocking.php
     */
    #[DataProvider('relevantOnlyForStreamTransportProvider')]
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
     * @testdox       A stream transport can make a GET request when blocking mode is disabled
     *
     * @param   string  $transportClass  The transport class to test
     *
     * @covers        \Joomla\Http\Transport\Stream
     * @uses          \Joomla\Http\AbstractTransport
     *
     *  Blocking mode is only relevant for OPTION,HEAD,GET request since it only affects reading from the stream
     * @see           https://www.php.net/manual/en/function.stream-set-blocking.php
     */
    #[DataProvider('relevantOnlyForStreamTransportProvider')]
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
     * @testdox       A transport fails to make a GET request to an invalid domain
     *
     * @param   string  $transportClass  The transport class to test
     *
     * @covers        \Joomla\Http\Transport\Curl
     * @covers        \Joomla\Http\Transport\Socket
     * @covers        \Joomla\Http\Transport\Stream
     * @uses          \Joomla\Http\AbstractTransport
     */
    #[DataProvider('transportProvider')]
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
     * @testdox       A transport fails to make a GET request to an invalid URL
     *
     * @param   string  $transportClass  The transport class to test
     *
     * @covers        \Joomla\Http\Transport\Curl
     * @covers        \Joomla\Http\Transport\Socket
     * @covers        \Joomla\Http\Transport\Stream
     * @uses          \Joomla\Http\AbstractTransport
     */
    #[DataProvider('transportProvider')]
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
     * @testdox       A transport can make a GET request
     *
     * @param   string  $transportClass  The transport class to test
     *
     * @covers        \Joomla\Http\Transport\Curl
     * @covers        \Joomla\Http\Transport\Socket
     * @covers        \Joomla\Http\Transport\Stream
     * @uses          \Joomla\Http\AbstractTransport
     */
    #[DataProvider('transportProvider')]
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
     * @testdox       A transport can make a GET request with basic authentication
     *
     * @param   string  $transportClass  The transport class to test
     *
     * @covers        \Joomla\Http\Transport\Curl
     * @covers        \Joomla\Http\Transport\Socket
     * @covers        \Joomla\Http\Transport\Stream
     * @uses          \Joomla\Http\AbstractTransport
     */
    #[DataProvider('transportProvider')]
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
     * @testdox       A transport can make a POST request with an array as the request data
     *
     * @param   string  $transportClass  The transport class to test
     *
     * @covers        \Joomla\Http\Transport\Curl
     * @covers        \Joomla\Http\Transport\Socket
     * @covers        \Joomla\Http\Transport\Stream
     * @uses          \Joomla\Http\AbstractTransport
     */
    #[DataProvider('transportProvider')]
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
     * @testdox       A transport can make a POST request with a scalar value as the request data
     *
     * @param   string  $transportClass  The transport class to test
     *
     * @covers        \Joomla\Http\Transport\Curl
     * @covers        \Joomla\Http\Transport\Socket
     * @covers        \Joomla\Http\Transport\Stream
     * @uses          \Joomla\Http\AbstractTransport
     */
    #[DataProvider('transportProvider')]
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
