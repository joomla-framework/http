<?php
/**
 * @copyright  Copyright (C) 2005 - 2021 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

/**
 * This is a stub file to aid in testing the JHttp transports. The idea is to echo
 * back data that is sent from the transports so that assertions can be made and
 * it can be ensured that proper data is being sent in the request.
 *
 * This file must be placed on a webserver in a location that can be accessed
 * by the system that the test is running.
 */

$response = new stdClass();

$response->method               = getVar($_SERVER, 'REQUEST_METHOD');
$response->http_user_agent      = getVar($_SERVER, 'HTTP_USER_AGENT');
$response->request_uri          = getVar($_SERVER, 'REQUEST_URI');
$response->query_string         = getVar($_SERVER, 'QUERY_STRING');
$response->http_accept          = getVar($_SERVER, 'HTTP_ACCEPT');
$response->http_accept_charset  = getVar($_SERVER, 'HTTP_ACCEPT_CHARSET');
$response->http_accept_encoding = getVar($_SERVER, 'HTTP_ACCEPT_ENCODING');

list($response->username, $response->password) = basicCredentials();

$response->http_referer = getVar($_SERVER, 'HTTP_REFERER');

$response->get     = $_GET;
$response->post    = $_POST;
$response->files   = $_FILES;
$response->cookies = $_COOKIE;

echo json_encode($response);

/**
 * Retrieves a value from an array, returning a default value if not present
 *
 * @param   array   $array    The array from which to retrieve a value.
 * @param   string  $key      The value to retrieve.
 * @param   mixed   $default  The value to return if the key isn't present.
 *
 * @return  mixed
 */
function getVar($array, $key, $default = '')
{
    return $array[$key] ?? $default;
}

/**
 * Reads the HTTP basic auth credentials.
 *
 * PHP only fills PHP_AUTH_USER/PHP_AUTH_PW when it runs as an Apache module. Under FastCGI,
 * CGI or php-fpm the Authorization header arrives verbatim instead - if the server forwards it
 * at all - so fall back to decoding it by hand.
 *
 * @return  array  A [username, password] pair; both empty when no credentials were sent.
 */
function basicCredentials()
{
    if (isset($_SERVER['PHP_AUTH_USER'])) {
        return [$_SERVER['PHP_AUTH_USER'], getVar($_SERVER, 'PHP_AUTH_PW')];
    }

    $header = getVar($_SERVER, 'HTTP_AUTHORIZATION', getVar($_SERVER, 'REDIRECT_HTTP_AUTHORIZATION'));

    if ($header === '' && function_exists('apache_request_headers')) {
        $headers = array_change_key_case(apache_request_headers(), CASE_LOWER);
        $header  = $headers['authorization'] ?? '';
    }

    if (stripos($header, 'basic ') !== 0) {
        return ['', ''];
    }

    $decoded = base64_decode(substr($header, 6), true);

    if ($decoded === false || strpos($decoded, ':') === false) {
        return ['', ''];
    }

    return explode(':', $decoded, 2);
}
