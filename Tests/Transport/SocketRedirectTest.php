<?php

/**
 * @copyright  Copyright (C) 2005 - 2026 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Http\Tests\Transport;

use Joomla\Http\Transport\Socket;
use Joomla\Uri\Uri;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Test class for redirects followed by Joomla\Http\Transport\Socket.
 */
#[CoversClass(Socket::class)]
class SocketRedirectTest extends TestCase
{
    /**
     * The URL of the directory holding the HTTP stubs.
     *
     * @var  string
     */
    private $stubDirectory = '';

    /**
     * Sets up the fixture.
     *
     * @return  void
     */
    protected function setUp(): void
    {
        parent::setUp();

        if (!Socket::isSupported()) {
            $this->markTestSkipped('The socket transport is not supported in this environment.');
        }

        if (!\defined('JTEST_HTTP_STUB') && getenv('JTEST_HTTP_STUB') == '') {
            return;
        }

        $stubUrl             = \defined('JTEST_HTTP_STUB') ? JTEST_HTTP_STUB : getenv('JTEST_HTTP_STUB');
        $this->stubDirectory = substr($stubUrl, 0, strrpos($stubUrl, '/') + 1);
    }

    /**
     * Skip the test when no stub is configured.
     *
     * @return  void
     */
    private function requireStub(): void
    {
        if ($this->stubDirectory === '') {
            $this->markTestSkipped('The Transport test stub has not been configured');
        }
    }

    public static function locationProvider(): \Generator
    {
        $base = 'http://user:pass@example.com:8080/a/b/c.php?x=1';

        yield 'relative path' => [$base, 'd.php', 'http://user:pass@example.com:8080/a/b/d.php'];
        yield 'relative path with query' => [$base, 'd.php?y=2', 'http://user:pass@example.com:8080/a/b/d.php?y=2'];
        yield 'parent directory' => [$base, '../d.php', 'http://user:pass@example.com:8080/a/d.php'];
        yield 'current directory' => [$base, '.', 'http://user:pass@example.com:8080/a/b/'];
        yield 'absolute path' => [$base, '/e/f', 'http://user:pass@example.com:8080/e/f'];
        yield 'query only' => [$base, '?y=2', 'http://user:pass@example.com:8080/a/b/c.php?y=2'];
        yield 'fragment is dropped' => [$base, 'd.php#top', 'http://user:pass@example.com:8080/a/b/d.php'];
        yield 'network path' => [$base, '//other.example.org/p', 'http://other.example.org/p'];
        yield 'absolute URI' => [$base, 'https://other.example.org/p?q=1', 'https://other.example.org/p?q=1'];
        yield 'base without path' => ['http://example.com', 'd.php', 'http://example.com/d.php'];
    }

    #[DataProvider('locationProvider')]
    public function testLocationIsResolvedAgainstTheRequestUri(string $base, string $location, string $expected): void
    {
        $method = new \ReflectionMethod(Socket::class, 'resolveLocation');

        $this->assertSame($expected, (string) $method->invoke(new Socket([]), new Uri($base), $location));
    }

    public function testRedirectsWithRelativeLocationAreFollowed(): void
    {
        $this->requireStub();

        $response = (new Socket([]))->request('GET', new Uri($this->stubDirectory . 'redirect.php?hops=2'));

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('GET', json_decode((string) $response->getBody())->method);
    }

    public function testRedirectIsReturnedWhenFollowingIsDisabled(): void
    {
        $this->requireStub();

        $response = (new Socket(['follow_location' => false]))->request('GET', new Uri($this->stubDirectory . 'redirect.php'));

        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame('jhttp_stub.php', $response->getHeaderLine('Location'));
    }

    public function testRedirectLoopIsAborted(): void
    {
        $this->requireStub();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Maximum (20) redirects followed');

        (new Socket([]))->request('GET', new Uri($this->stubDirectory . 'redirect.php?loop=1'));
    }

    public function testPostIsRepeatedAsGetAfter303(): void
    {
        $this->requireStub();

        $response = (new Socket([]))->request('POST', new Uri($this->stubDirectory . 'redirect.php?code=303'), ['key' => 'value']);
        $body     = json_decode((string) $response->getBody());

        $this->assertSame('GET', $body->method);
        $this->assertEmpty((array) $body->post);
    }

    public function testPostIsRepeatedWithItsBodyAfter307(): void
    {
        $this->requireStub();

        $response = (new Socket([]))->request('POST', new Uri($this->stubDirectory . 'redirect.php?code=307'), ['key' => 'value']);
        $body     = json_decode((string) $response->getBody());

        $this->assertSame('POST', $body->method);
        $this->assertSame('value', $body->post->key);
    }

    public function testCredentialsAreKeptForTheSameOrigin(): void
    {
        $this->requireStub();

        $uri = new Uri($this->stubDirectory . 'redirect.php');
        $uri->setUser('username');
        $uri->setPass('password');

        $body = json_decode((string) (new Socket([]))->request('GET', $uri)->getBody());

        $this->assertSame('username', $body->username);
        $this->assertSame('password', $body->password);
    }

    public function testCredentialsAreNotSentToAnotherOrigin(): void
    {
        $this->requireStub();

        // The other origin is the same server under another name.
        $stub    = new Uri($this->stubDirectory);
        $aliases = ['localhost' => '127.0.0.1', '127.0.0.1' => 'localhost'];
        $host    = $aliases[$stub->getHost()] ?? null;
        $port    = $stub->getPort() ?: 80;

        if ($host === null || !($socket = @fsockopen($host, $port, $errno, $error, 2))) {
            $this->markTestSkipped('The stub is not reachable under a second host name.');
        }

        fclose($socket);

        $other = new Uri($this->stubDirectory);
        $other->setHost($host);

        $uri = new Uri($this->stubDirectory . 'redirect.php?' . http_build_query(['to' => (string) $other]));
        $uri->setUser('username');
        $uri->setPass('password');

        $transport = new Socket(['userauth' => 'username', 'passwordauth' => 'password']);
        $body      = json_decode((string) $transport->request('GET', $uri, null, ['Cookie' => 'session=secret'])->getBody());

        $this->assertSame($host, parse_url('http://' . $body->headers->Host, PHP_URL_HOST), 'The redirect was not followed to the other host.');
        $this->assertSame('', $body->username);
        $this->assertSame('', $body->password);
        $this->assertEmpty((array) $body->cookies);
    }
}
