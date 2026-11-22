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
 * Test class for the proxy.* options of the transports.
 *
 * The stub server acts as the proxy: PHP's built-in server answers a request with an absolute URI in the
 * request line, so the stub reports the full URI and the Proxy-Authorization header it received.
 */
#[CoversClass(Curl::class)]
#[CoversClass(Socket::class)]
#[CoversClass(Stream::class)]
class ProxyOptionsTest extends TestCase
{
    /**
     * Get the URL of the stub, or skip the test.
     *
     * @return  Uri
     */
    private function getStubUri(): Uri
    {
        if (!\defined('JTEST_HTTP_STUB') && getenv('JTEST_HTTP_STUB') == '') {
            $this->markTestSkipped('The Transport test stub has not been configured');
        }

        return new Uri(\defined('JTEST_HTTP_STUB') ? JTEST_HTTP_STUB : getenv('JTEST_HTTP_STUB'));
    }

    public static function proxyCredentialsProvider(): \Generator
    {
        foreach ([Curl::class, Stream::class] as $transport) {
            foreach (['proxy.pass', 'proxy.password'] as $passwordOption) {
                yield substr(strrchr($transport, '\\'), 1) . ' with ' . $passwordOption => [$transport, $passwordOption];
            }
        }
    }

    #[DataProvider('proxyCredentialsProvider')]
    public function testRequestIsSentThroughTheProxyWithCredentials(string $transportClass, string $passwordOption): void
    {
        if (!$transportClass::isSupported()) {
            $this->markTestSkipped(sprintf('The "%s" adapter is not supported in this environment.', $transportClass));
        }

        $stub      = $this->getStubUri();
        $transport = new $transportClass([
            'proxy.enabled' => true,
            'proxy.host'    => $stub->getHost(),
            'proxy.port'    => $stub->getPort() ?: 80,
            'proxy.user'    => 'proxyuser',
            $passwordOption => 'proxysecret',
        ]);

        $target   = 'http://proxied.example.invalid' . $stub->getPath();
        $response = $transport->request('GET', new Uri($target));
        $body     = json_decode((string) $response->getBody());
        $headers  = array_change_key_case((array) $body->headers);

        $this->assertSame($target, $body->request_uri, 'The request did not go through the proxy.');
        $this->assertSame('Basic ' . base64_encode('proxyuser:proxysecret'), $headers['proxy-authorization'] ?? null);
    }

    public static function incompleteProxyProvider(): \Generator
    {
        foreach ([Curl::class, Stream::class] as $transport) {
            $name = substr(strrchr($transport, '\\'), 1);

            yield $name . ' without port' => [$transport, ['proxy.enabled' => true, 'proxy.host' => 'proxy.example.invalid']];
            yield $name . ' without host' => [$transport, ['proxy.enabled' => true, 'proxy.port' => 3128]];
        }
    }

    #[DataProvider('incompleteProxyProvider')]
    public function testIncompleteProxyConfigurationIsRejected(string $transportClass, array $options): void
    {
        if (!$transportClass::isSupported()) {
            $this->markTestSkipped(sprintf('The "%s" adapter is not supported in this environment.', $transportClass));
        }

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The proxy.host and proxy.port options are required when proxy.enabled is set.');

        (new $transportClass($options))->request('GET', new Uri('http://127.0.0.1:1/'));
    }

    public function testSocketTransportWarnsThatItBypassesTheProxy(): void
    {
        if (!Socket::isSupported()) {
            $this->markTestSkipped('The socket transport is not supported in this environment.');
        }

        $stub     = $this->getStubUri();
        $warnings = [];

        set_error_handler(
            static function (int $severity, string $message) use (&$warnings): bool {
                $warnings[] = [$severity, $message];

                return true;
            }
        );

        try {
            $response = (new Socket(['proxy.enabled' => true, 'proxy.host' => 'proxy.example.invalid', 'proxy.port' => 3128]))
                ->request('GET', $stub);
        } finally {
            restore_error_handler();
        }

        $this->assertSame(200, $response->getStatusCode());
        $this->assertCount(1, $warnings);
        $this->assertSame(E_USER_WARNING, $warnings[0][0]);
        $this->assertStringContainsString('does not support proxies', $warnings[0][1]);
    }
}
