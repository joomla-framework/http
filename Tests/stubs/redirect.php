<?php

/**
 * @copyright  Copyright (C) 2005 - 2026 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Redirect stub for the transport tests. It redirects, with a relative Location unless `to` is
 * given, until jhttp_stub.php is reached.
 *
 * Query parameters:
 *   hops  Number of redirects before jhttp_stub.php is reached (default 1).
 *   code  The redirect status code (default 302).
 *   to    Base URL (ending in a slash) to redirect to, e.g. the stub directory on another host.
 *   loop  When set, redirect to this script again, forever.
 */

$hops = (int) ($_GET['hops'] ?? 1);
$code = (int) ($_GET['code'] ?? 302);
$base = (string) ($_GET['to'] ?? '');

if (isset($_GET['loop'])) {
    $location = 'redirect.php?' . http_build_query(['loop' => 1, 'code' => $code]);
} elseif ($hops > 1) {
    $location = 'redirect.php?' . http_build_query(['hops' => $hops - 1, 'code' => $code, 'to' => $base]);
} else {
    $location = 'jhttp_stub.php';
}

header('Location: ' . $base . $location, true, $code);
