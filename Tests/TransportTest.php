<?php
/**
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Http\Tests;

use Joomla\Http\Transport\Curl;
use Joomla\Http\Transport\Socket;
use Joomla\Http\Transport\Stream;
use Joomla\Http\TransportInterface;
use Joomla\Uri\Uri;
use PHPUnit\Framework\TestCase;

/**
 * Test class for Joomla\Http\TransportInterface instances.
 *
 * @since  1.0
 */
class TransportTest extends TestCase
{
	/**
	 * Options for the Transport object.
	 *
	 * @var  array
	 */
	protected $options = [
		'transport.curl'   => [CURLOPT_SSL_VERIFYPEER => false],
		'transport.socket' => ['X-Joomla-Test: true'],
		'transport.stream' => ['ignore_errors' => true],
	];

	/**
	 * The URL string for the HTTP stub.
	 *
	 * @var  string
	 */
	protected $stubUrl;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return  void
	 */
	protected function setUp(): void
	{
		parent::setUp();

		if (!\defined('JTEST_HTTP_STUB') && getenv('JTEST_HTTP_STUB') == '')
		{
			$this->markTestSkipped('The Transport test stub has not been configured');
		}

		$this->stubUrl = \defined('JTEST_HTTP_STUB') ? JTEST_HTTP_STUB : getenv('JTEST_HTTP_STUB');
	}

	/**
	 * Data provider for the request test methods.
	 *
	 * @return  \Generator
	 */
	public function transportProvider(): \Generator
	{
		yield 'curl adapter'   => [Curl::class];
		yield 'socket adapter' => [Socket::class];
		yield 'stream adapter' => [Stream::class];
	}

	/**
	 * Tests the transport constructor to ensure only arrays and ArrayAccess objects are allowed
	 *
	 * @param   string  $transportClass  The transport class to test
	 *
	 * @dataProvider  transportProvider
	 */
	public function testConstructorWithBadDataObject(string $transportClass)
	{
		if (!$transportClass::isSupported())
		{
			$this->markTestSkipped(sprintf('The "%s" adapter is not supported in this environment.', $transportClass));
		}

		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessage('The options param must be an array or implement the ArrayAccess interface.');

		new $transportClass(new \stdClass);
	}

	/**
	 * Tests the request method with a get request
	 *
	 * @param   string  $transportClass  The transport class to test
	 *
	 * @dataProvider  transportProvider
	 */
	public function testRequestGet(string $transportClass)
	{
		if (!$transportClass::isSupported())
		{
			$this->markTestSkipped(sprintf('The "%s" adapter is not supported in this environment.', $transportClass));
		}

		/** @var TransportInterface $transport */
		$transport = new $transportClass($this->options);

		$response = $transport->request('GET', new Uri($this->stubUrl));

		$body = json_decode((string) $response->getBody());

		$this->assertSame(
			200,
			$response->getStatusCode()
		);

		$this->assertSame(
			'GET',
			$body->method
		);
	}

	/**
	 * Tests the request method with a get request with a bad domain
	 *
	 * @param   string  $transportClass  The transport class to test
	 *
	 * @dataProvider  transportProvider
	 */
	public function testBadDomainRequestGet(string $transportClass)
	{
		if (!$transportClass::isSupported())
		{
			$this->markTestSkipped(sprintf('The "%s" adapter is not supported in this environment.', $transportClass));
		}

		$this->expectException(\RuntimeException::class);

		/** @var TransportInterface $transport */
		$transport = new $transportClass($this->options);

		$response = $transport->request('GET', new Uri('https://xommunity.joomla.org'));
	}

	/**
	 * Tests the request method with a get request for non existant url
	 *
	 * @param   string  $transportClass  The transport class to test
	 *
	 * @dataProvider  transportProvider
	 */
	public function testRequestGet404(string $transportClass)
	{
		if (!$transportClass::isSupported())
		{
			$this->markTestSkipped(sprintf('The "%s" adapter is not supported in this environment.', $transportClass));
		}

		/** @var TransportInterface $transport */
		$transport = new $transportClass($this->options);

		$response = $transport->request('GET', new Uri(str_replace('.php', '.html', $this->stubUrl)));

		$this->assertSame(
			404,
			$response->getStatusCode()
		);
	}

	/**
	 * Tests the request method with a put request
	 *
	 * @param   string  $transportClass  The transport class to test
	 *
	 * @dataProvider  transportProvider
	 */
	public function testRequestPut(string $transportClass)
	{
		if (!$transportClass::isSupported())
		{
			$this->markTestSkipped(sprintf('The "%s" adapter is not supported in this environment.', $transportClass));
		}

		/** @var TransportInterface $transport */
		$transport = new $transportClass($this->options);

		$response = $transport->request('PUT', new Uri($this->stubUrl));

		$body = json_decode((string) $response->getBody());

		$this->assertSame(
			200,
			$response->getStatusCode()
		);

		$this->assertSame(
			'PUT',
			$body->method
		);
	}

	/**
	 * Tests the request method with credentials supplied
	 *
	 * @param   string  $transportClass  The transport class to test
	 *
	 * @dataProvider  transportProvider
	 */
	public function testRequestCredentials(string $transportClass)
	{
		if (!$transportClass::isSupported())
		{
			$this->markTestSkipped(sprintf('The "%s" adapter is not supported in this environment.', $transportClass));
		}

		/** @var TransportInterface $transport */
		$transport = new $transportClass($this->options);

		$uri = new Uri($this->stubUrl);
		$uri->setUser('username');
		$uri->setPass('password');

		$response = $transport->request('GET', $uri);

		$body = json_decode((string) $response->getBody());

		$this->assertSame(
			200,
			$response->getStatusCode()
		);

		$this->assertSame(
			'username',
			$body->username
		);

		$this->assertSame(
			'password',
			$body->password
		);
	}

	/**
	 * Tests the request method with a post request and array data
	 *
	 * @param   string  $transportClass  The transport class to test
	 *
	 * @dataProvider  transportProvider
	 */
	public function testRequestPost(string $transportClass)
	{
		if (!$transportClass::isSupported())
		{
			$this->markTestSkipped(sprintf('The "%s" adapter is not supported in this environment.', $transportClass));
		}

		/** @var TransportInterface $transport */
		$transport = new $transportClass($this->options);

		$response = $transport->request('POST', new Uri($this->stubUrl . '?test=okay'), ['key' => 'value']);

		$body = json_decode((string) $response->getBody());

		$this->assertSame(
			200,
			$response->getStatusCode()
		);

		$this->assertSame(
			'POST',
			$body->method
		);

		$this->assertSame(
			'value',
			$body->post->key
		);
	}

	/**
	 * Tests the request method with a post request and scalar data
	 *
	 * @param   string  $transportClass  The transport class to test
	 *
	 * @dataProvider  transportProvider
	 */
	public function testRequestPostScalar(string $transportClass)
	{
		if (!$transportClass::isSupported())
		{
			$this->markTestSkipped(sprintf('The "%s" adapter is not supported in this environment.', $transportClass));
		}

		/** @var TransportInterface $transport */
		$transport = new $transportClass($this->options);

		$response = $transport->request('post', new Uri($this->stubUrl . '?test=okay'), 'key=value');

		$body = json_decode((string) $response->getBody());

		$this->assertSame(
			200,
			$response->getStatusCode()
		);

		$this->assertSame(
			'POST',
			$body->method
		);

		$this->assertSame(
			'value',
			$body->post->key
		);
	}
}
