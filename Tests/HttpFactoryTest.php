<?php

/**
 * @copyright  Copyright (C) 2005 - 2021 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Http\Tests;

use Joomla\Http\Http;
use Joomla\Http\HttpFactory;
use Joomla\Http\TransportInterface;
use PHPUnit\Framework\TestCase;

/**
 * Test class for Joomla\Http\HttpFactory.
 */
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

    /**
     * @testdox  A HTTP client can be created
     *
     * @covers   Joomla\Http\HttpFactory
     * @uses     Joomla\Http\AbstractTransport
     * @uses     Joomla\Http\Http
     * @uses     Joomla\Http\Transport\Curl
     */
    public function testGetHttp()
    {
        $this->assertInstanceOf(
            Http::class,
            $this->instance->getHttp()
        );
    }

    /**
     * @testdox  A HTTP client can only be created with an appropriate options data type
     *
     * @covers   Joomla\Http\HttpFactory
     */
    public function testGetHttpDisallowsNonArrayObjects()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->instance->getHttp(new \stdClass());
    }

    /**
     * @testdox  A HTTP client cannot be created when no transport driver is available
     *
     * @covers  Joomla\Http\HttpFactory
     */
    public function testGetHttpException()
    {
        $this->expectException(\RuntimeException::class);

        $this->assertInstanceOf(
            Http::class,
            $this->instance->getHttp([], [])
        );
    }

    /**
     * @testdox  A transport driver can be created
     *
     * @covers   Joomla\Http\HttpFactory
     * @uses     Joomla\Http\AbstractTransport
     * @uses     Joomla\Http\Transport\Curl
     */
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

    /**
     * @testdox  A driver can only be created with an appropriate options data type
     *
     * @covers   Joomla\Http\HttpFactory
     */
    public function testGetAvailableDriverDisallowsNonArrayObjects()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->instance->getAvailableDriver(new \stdClass());
    }

    /**
     * @testdox  The list of transport drivers is returned
     *
     * @covers   Joomla\Http\HttpFactory
     */
    public function testGetHttpTransports()
    {
        $transports = ['Stream', 'Socket', 'Curl'];
        sort($transports);

        $this->assertEquals(
            $transports,
            $this->instance->getHttpTransports()
        );
    }
}
