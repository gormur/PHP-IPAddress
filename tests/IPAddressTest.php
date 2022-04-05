<?php
use Leth\IPAddress\IP, Leth\IPAddress\IPv4, Leth\IPAddress\IPv6;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the IP\Address Class
 *
 * @package default
 * @author Marcus Cobden
 */
class IP_Address_Test extends TestCase
{

	public function providerFactory()
	{
		return [
			['IPv4\\Address', '127.0.0.1',   '127.000.000.001',   '127.0.0.1'],
			['IPv4\\Address', IPv4\Address::factory('127.0.0.1'),   '127.000.000.001',   '127.0.0.1'],
			['IPv4\\Address', '127.0.0.0',   '127.000.000.000',   '127.0.0.0'],
			['IPv4\\Address', '127.0.0.2',   '127.000.000.002',   '127.0.0.2'],
			['IPv4\\Address', '192.168.1.1', '192.168.001.001', '192.168.1.1'],
			['IPv4\\Address', '192.168.2.1', '192.168.002.001', '192.168.2.1'],
			['IPv4\\Address', '192.168.1.2', '192.168.001.002', '192.168.1.2'],
			['IPv4\\Address', '10.0.0.2',    '010.000.000.002',    '10.0.0.2'],
			['IPv4\\Address', '10.0.0.1',    '010.000.000.001',    '10.0.0.1'],
			['IPv4\\Address', 257,           '000.000.001.001',     '0.0.1.1'],

			['IPv6\\Address', new Math_BigInteger(257),                  '0000:0000:0000:0000:0000:0000:0000:0101', '::101'],
			['IPv6\\Address',                                     '::1', '0000:0000:0000:0000:0000:0000:0000:0001', '::1'],
			['IPv6\\Address',              IPv6\Address::factory('::1'), '0000:0000:0000:0000:0000:0000:0000:0001', '::1'],
			['IPv6\\Address', '0000:0000:0000:0000:0000:0000:0000:0001', '0000:0000:0000:0000:0000:0000:0000:0001', '::1'],
			['IPv6\\Address',               'fe80::62fb:42ff:feeb:727c', 'fe80:0000:0000:0000:62fb:42ff:feeb:727c', 'fe80::62fb:42ff:feeb:727c'],
			['IPv6\\Address',               'fc00:1234::e0',             'fc00:1234:0000:0000:0000:0000:0000:00e0', 'fc00:1234::e0'],
		];
	}

	/**
	 * @dataProvider providerFactory
	 */
	public function testFactory($expected_class, $input, $full, $compact)
	{
		$expected_class = 'Leth\\IPAddress\\'.$expected_class;
		$instances = [IP\Address::factory($input), $expected_class::factory($input)];

		foreach ($instances as $instance) {
			$this->assertNotNull($instance);
			$this->assertEquals($full,    $instance->format(IP\Address::FORMAT_FULL));
			$this->assertEquals($compact, $instance->format(IP\Address::FORMAT_COMPACT));
			$this->assertEquals($expected_class, get_class($instance));
		}
	}

	public function providerFactoryException()
	{
		$data = [];
		$data[] = ['cake'];
		$data[] = ['12345'];
		$data[] = ['-12345'];
		return $data;
	}

	/**
	 * @dataProvider providerFactoryException
	 */
	public function testFactoryException($input)
	{
		$this->expectException(InvalidArgumentException::class);
		IP\Address::factory($input);
	}

	public function providerCompare()
	{
		$data = [];
		$data[] = ['127.0.0.1', '127.0.0.1', 0];
		$data[] = ['127.0.0.0', '127.0.0.1', -1];
		$data[] = ['127.0.0.0', '127.0.0.2', -1];
		$data[] = ['127.0.0.1', '127.0.0.2', -1];
		$data[] = ['127.0.0.2', '127.0.0.1', 1];
		$data[] = ['10.0.0.1',  '127.0.0.2', -1];
		return $data;
	}

	/**
	 * @dataProvider providerCompare
	 */
	public function testCompare($a, $b, $expected)
	{
		$result = IP\Address::compare(IP\Address::factory($a), IP\Address::factory($b));

		// Division is to ensure things are either -1, 0 or 1. abs() is to preseve sign.
		$this->assertEquals($expected, $result == 0 ? 0: $result / abs($result));
	}
}
