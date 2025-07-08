## Updating from v3 to v4

### Minimum supported PHP version raised

All Framework packages now require PHP 8.3 or newer.

### Dependency `laminas/laminas-diactoros` updated to major version 3

[`laminas-diactoros`](https://github.com/laminas/laminas-diactoros) is a PSR-7 HTTP Message implementation and the former v2 series is not PHP 8.4 compliant. The newer v3 series is now fully PHP 8.4+ compliant and thus an update was inevitable. For differences please read the documentation of [`laminas/laminas-diactoros`](https://github.com/laminas/laminas-diactoros).

### Magic getter for internal properties of Response class removed
For backwards compatibilty purposes from v1 of the framework, `Joomla\HTTP\Response` contained a magic getter to access headers, body and code of the request. This was deprecated in v2 of the framework and has now been removed with v4. Please use the proper PSR-7 getters like `getHeaders()`, `getStatusCode()` and `getContent()` instead.
