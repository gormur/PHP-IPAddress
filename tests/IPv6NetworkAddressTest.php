<?php
use Leth\IPAddress\IPv6;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the IP\NetworkAddress Class
 *
 * @package default
 * @author Marcus Cobden
 */
class IPv6_NetworkAddress_Test extends TestCase
{
	public function test_global_netmask()
	{
		$this->assertEquals('ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff', (string) IPv6\NetworkAddress::get_global_netmask());
	}

	public function providerSplit()
	{
		$data = [];
		$data[] = [IPv6\NetworkAddress::factory('::0/126'), 0, [
			IPv6\NetworkAddress::factory('::0/126'),
		]];
		$data[] = [IPv6\NetworkAddress::factory('::0/126'), 1, [
			IPv6\NetworkAddress::factory('::0/127'),
			IPv6\NetworkAddress::factory('::2/127'),
		]];
		$data[] = [IPv6\NetworkAddress::factory('::0/126'), 2, [
			IPv6\NetworkAddress::factory('::0/128'),
			IPv6\NetworkAddress::factory('::1/128'),
			IPv6\NetworkAddress::factory('::2/128'),
			IPv6\NetworkAddress::factory('::3/128'),
		]];
		return $data;
	}

	/**
	 * @dataProvider providerSplit
	 */
	public function testSplit($block, $degree, $expected)
	{
		$this->assertEquals($expected, $block->split($degree));
	}

	/**
	 */
	public function testSplitBeyondRange()
	{
		$this->expectException(InvalidArgumentException::class);
		$block = IPv6\NetworkAddress::factory('::0/128');
		$block->split();
	}

	public function testIterationInterface()
	{
		$block = IPv6\NetworkAddress::factory('::0/126');
		$expected = ['::0', '::1', '::2', '::3'];
		$actual = [];
		foreach ($block as $ip)
		{
			$actual[] = (string)$ip;
		}
		$this->assertEquals($expected, $actual);
	}

	public function testCountableInterface()
	{
		$block = IPv6\NetworkAddress::factory('::0/126');
		$this->assertEquals(4, count($block));
		$block = IPv6\NetworkAddress::factory('::0/120');
		$this->assertEquals(pow(2, 8), count($block));
	}
}
