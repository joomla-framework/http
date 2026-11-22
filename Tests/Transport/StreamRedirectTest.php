<?php

/**
 * @copyright  Copyright (C) 2005 - 2026 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Http\Tests\Transport;

use Joomla\Http\Transport\Stream;
use Joomla\Uri\Uri;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Test class for redirects followed by Joomla\Http\Transport\Stream.
 */
#[CoversClass(Stream::class)]
class StreamRedirectTest extends TestCase
{
    /**
     * The URL of the directory holding the HTTP stubs.
     *
     * @var  string
     */
    private $stubDirectory;

    /**
     * Sets up the fixture.
     *
     * @return  void
     */
    protected function setUp(): void
    {
        parent::setUp();

        if (!Stream::isSupported()) {
            $this->markTestSkipped('The stream transport is not supported in this environment.');
        }

        if (!\defined('JTEST_HTTP_STUB') && getenv('JTEST_HTTP_STUB') == '') {
            $this->markTestSkipped('The Transport test stub has not been configured');
        }

        $stubUrl             = \defined('JTEST_HTTP_STUB') ? JTEST_HTTP_STUB : getenv('JTEST_HTTP_STUB');
        $this->stubDirectory = substr($stubUrl, 0, strrpos($stubUrl, '/') + 1);
    }

    public function testRedirectsAreFollowedAndTheLastResponseIsReturned(): void
    {
        $transport = new Stream([]);

        $response = $transport->request('GET', new Uri($this->stubDirectory . 'redirect.php?hops=2'));

        $this->assertSame(200, $response->getStatusCode());
        $this->assertFalse($response->hasHeader('Location'), 'The headers of the redirect responses must not be merged in.');
        $this->assertSame('GET', json_decode((string) $response->getBody())->method);
    }

    public function testRedirectIsReturnedWhenFollowingIsDisabled(): void
    {
        $transport = new Stream(['follow_location' => false]);

        $response = $transport->request('GET', new Uri($this->stubDirectory . 'redirect.php'));

        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame('jhttp_stub.php', $response->getHeaderLine('Location'));
    }
}
