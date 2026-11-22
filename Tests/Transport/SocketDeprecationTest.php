<?php

/**
 * @copyright  Copyright (C) 2005 - 2026 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Http\Tests\Transport;

use Joomla\Http\Transport\Socket;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Test class for the deprecation of Joomla\Http\Transport\Socket.
 */
#[CoversClass(Socket::class)]
class SocketDeprecationTest extends TestCase
{
    public function testCreatingTheSocketTransportTriggersADeprecation(): void
    {
        if (!Socket::isSupported()) {
            $this->markTestSkipped('The socket transport is not supported in this environment.');
        }

        $deprecations = [];

        set_error_handler(
            static function (int $severity, string $message) use (&$deprecations): bool {
                if ($severity === E_USER_DEPRECATED) {
                    $deprecations[] = $message;
                }

                return true;
            }
        );

        try {
            new Socket([]);
        } finally {
            restore_error_handler();
        }

        $this->assertCount(1, $deprecations);
        $this->assertStringContainsString('transport is deprecated and will be removed in 5.0', $deprecations[0]);
    }
}
