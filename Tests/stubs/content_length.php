<?php

/**
 * @copyright  Copyright (C) 2005 - 2026 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Stub for the transport tests that announces a 100 byte body with Content-Length. The web server
 * omits the body for a HEAD request, so a client that waits for it after a HEAD request fails.
 */

header('Content-Type: text/plain');
header('Content-Length: 100');

echo str_repeat('x', 100);
