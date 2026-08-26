<?php

/**
 * @copyright  Copyright (C) 2005 - 2021 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Http\Tests;

use Joomla\Http\AbstractTransport;
use Joomla\Http\Http;
use Joomla\Http\HttpFactory;
use Joomla\Http\Transport\Curl;
use Joomla\Http\TransportInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * Test class for Joomla\Http\HttpFactory.
 */
#[CoversClass(HttpFactory::class)]
#[UsesClass(AbstractTransport::class)]
#[UsesClass(Http::class)]
#[UsesClass(Curl::class)]
class HttpFactoryTest extends TestCase
{
    /**
     * Object being tested
     *
     * @var  HttpFactory
     */
    private $instance;

    /**
     * Sets up the fixture, for example, open a network connection.
     *
     * @return  void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = new HttpFactory();
    }

    #[TestDox('A HTTP client can be created')]
    public function testGetHttp()
    {
        $this->assertInstanceOf(
            Http::class,
            $this->instance->getHttp()
        );
    }

    #[TestDox('A HTTP client can only be created with an appropriate options data type')]
    public function testGetHttpDisallowsNonArrayObjects()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->instance->getHttp(new \stdClass());
    }

    #[TestDox('A HTTP client cannot be created when no transport driver is available')]
    public function testGetHttpException()
    {
        $this->expectException(\RuntimeException::class);

        $this->assertInstanceOf(
            Http::class,
            $this->instance->getHttp([], [])
        );
    }

    #[TestDox('A transport driver can be created')]
    public function testGetAvailableDriver()
    {
        $this->assertInstanceOf(
            TransportInterface::class,
            $this->instance->getAvailableDriver([], null)
        );

        $this->assertFalse(
            $this->instance->getAvailableDriver([], []),
            'Passing an empty array should return false due to there being no adapters to test'
        );

        $this->assertFalse(
            $this->instance->getAvailableDriver([], ['fopen']),
            'A false should be returned if a class is not present or supported'
        );

        include_once __DIR__ . '/stubs/DummyTransport.php';

        $this->assertFalse(
            $this->instance->getAvailableDriver([], ['DummyTransport']),
            'Passing an empty array should return false due to there being no adapters to test'
        );
    }

    #[TestDox('A driver can only be created with an appropriate options data type')]
    public function testGetAvailableDriverDisallowsNonArrayObjects()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->instance->getAvailableDriver(new \stdClass());
    }

    #[TestDox('The list of transport drivers is returned')]
    public function testGetHttpTransports()
    {
        $transports = ['Stream', 'Socket', 'Curl'];
        sort($transports);

        $this->assertSame(
            $transports,
            $this->instance->getHttpTransports()
        );
    }
}
