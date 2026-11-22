<?php

/**
 * @copyright  Copyright (C) 2005 - 2026 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Http\Tests;

use Joomla\Http\Http;
use Joomla\Http\HttpFactory;
use Joomla\Http\HttpFactoryAwareInterface;
use Joomla\Http\HttpFactoryAwareTrait;
use Joomla\Http\HttpFactoryInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversTrait;
use PHPUnit\Framework\TestCase;

/**
 * Test class for HttpFactoryInterface, HttpFactoryAwareInterface and HttpFactoryAwareTrait.
 */
#[CoversClass(HttpFactory::class)]
#[CoversTrait(HttpFactoryAwareTrait::class)]
class HttpFactoryAwareTest extends TestCase
{
    /**
     * Create an object using the aware trait.
     *
     * @return  HttpFactoryAwareInterface
     */
    private function createAwareObject(): HttpFactoryAwareInterface
    {
        return new class () implements HttpFactoryAwareInterface {
            use HttpFactoryAwareTrait;

            public function factory(): HttpFactoryInterface
            {
                return $this->getHttpFactory();
            }
        };
    }

    public function testHttpFactoryImplementsTheInterface(): void
    {
        $this->assertInstanceOf(HttpFactoryInterface::class, new HttpFactory());
    }

    public function testCreateHttpReturnsAClientWithTheRequestedTransport(): void
    {
        $http = (new HttpFactory())->createHttp(['timeout' => 5], 'Curl');

        $this->assertInstanceOf(Http::class, $http);
        $this->assertSame(5, $http->getOption('timeout'));
    }

    public function testCreateHttpThrowsWhenNoTransportIsAvailable(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('No transport driver available.');

        (new HttpFactory())->createHttp([], 'DoesNotExist');
    }

    public function testFactoryCanBeSetAndRetrieved(): void
    {
        $factory = new HttpFactory();
        $object  = $this->createAwareObject();

        $object->setHttpFactory($factory);

        $this->assertSame($factory, $object->factory());
    }

    public function testRetrievingAFactoryThatWasNotSetThrows(): void
    {
        $this->expectException(\UnexpectedValueException::class);

        $this->createAwareObject()->factory();
    }
}
