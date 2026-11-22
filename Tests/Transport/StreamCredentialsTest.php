<?php

/**
 * @copyright  Copyright (C) 2005 - 2026 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Http\Tests\Transport;

use Joomla\Http\Transport\Stream;
use Joomla\Uri\Uri;
use Joomla\Uri\UriImmutable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Test class for the userauth/passwordauth options of Joomla\Http\Transport\Stream.
 */
#[CoversClass(Stream::class)]
class StreamCredentialsTest extends TestCase
{
    /**
     * The URL of the HTTP stub.
     *
     * @var  string
     */
    private $stubUrl;

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

        $this->stubUrl = \defined('JTEST_HTTP_STUB') ? JTEST_HTTP_STUB : getenv('JTEST_HTTP_STUB');
    }

    public function testCredentialsAreSentWithoutChangingTheUri(): void
    {
        $uri       = new Uri($this->stubUrl);
        $transport = new Stream(['userauth' => 'username', 'passwordauth' => 'secret']);

        $body = json_decode((string) $transport->request('GET', $uri)->getBody());

        $this->assertSame('username', $body->username);
        $this->assertSame('secret', $body->password);
        $this->assertSame($this->stubUrl, (string) $uri, 'The credentials must not be written into the URI object.');
    }

    public function testCredentialsAreSentForAnImmutableUri(): void
    {
        $transport = new Stream(['userauth' => 'username', 'passwordauth' => 'secret']);

        $body = json_decode((string) $transport->request('GET', new UriImmutable($this->stubUrl))->getBody());

        $this->assertSame('username', $body->username);
        $this->assertSame('secret', $body->password);
    }
}
