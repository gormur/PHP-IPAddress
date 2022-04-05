<?php
use Leth\IPAddress\IP;
use Leth\IPAddress\IPv4;
use Leth\IPAddress\IPv6;
use Leth\IPAddress\IP\NetworkAddress;
use PHPUnit\Framework\TestCase;

// class IP_Address_Tester extends IP\Address
// {
// 	public function __construct() {}

// 	public function add(Math_BigInteger|int $value) : IP\Address {}
// 	public function subtract($value) {}

// 	public function bitwise_and(IP\Address $other) {}
// 	public function bitwise_or(IP\Address $other) {}
// 	public function bitwise_xor(IP\Address $other) {}
// 	public function bitwise_not() {}

// 	public function format($mode) { return __CLASS__; }
// 	public function compare_to(IP\Address $other) {}
// }


class IPv4_NetworkAddress_Tester extends IPv4\NetworkAddress
{
	public static function factory($address, $cidr = NULL): NetworkAddress
	{
		$ip = IPv4\Address::factory($address);
		return new self($ip, $cidr);
	}
}

class IPv6_NetworkAddress_Tester extends IPv6\NetworkAddress
{
	public static function factory($address, $cidr = NULL): NetworkAddress
	{
		$ip = IPv6\Address::factory($address);
		return new self($ip, $cidr);
	}
}

/**
 * Tests for the IP\NetworkAddress Class
 *
 * @package default
 * @author Marcus Cobden
 */
class IP_NetworkAddress_Test extends TestCase
{
	public function providerFactory()
	{
		$data = [];
		$data[] = ['127.0.0.1/16', NULL, '127.0.0.1', 16, '127.0.0.0'];
		$data[] = ['127.0.0.1', 16, '127.0.0.1', 16, '127.0.0.0'];
		$data[] = ['127.0.0.1/32', NULL, '127.0.0.1', 32, '127.0.0.1'];
		$data[] = ['127.0.0.1', 32, '127.0.0.1', 32, '127.0.0.1'];
		$data[] = [IP\NetworkAddress::factory('127.0.0.1/16'), NULL, '127.0.0.1', 16, '127.0.0.0'];
		$data[] = [IP\NetworkAddress::factory('127.0.0.1/16'), 10, '127.0.0.1', 10, '127.0.0.0'];

		$data[] = ['::1/16', NULL, '::1', 16, '::0'];
		$data[] = ['::1', 16, '::1', 16, '::0'];
		$data[] = ['::1/128', NULL, '::1', 128, '::1'];
		$data[] = ['::1', 128, '::1', 128, '::1'];
		return $data;
	}

	/**
	 * @dataProvider providerFactory
	 */
	public function testFactory($address, $cidr, $expected_address, $expected_cidr, $expected_subnet)
	{
		$ip = IP\NetworkAddress::factory($address, $cidr);

		$this->assertEquals($expected_cidr, $ip->get_cidr());
		$this->assertEquals($expected_address, (string) $ip->get_address());
		$this->assertEquals($expected_subnet, (string) $ip->get_network_start());
	}

	/**
	 * What exactly does this test?
	 * The historical provider created a "IP_Address_Tester"
	 * that seemed just to be a mock, so we do that.
	 */
	public function testFactoryThrowsException1()
	{
		$this->expectException(InvalidArgumentException::class);
		IP\NetworkAddress::factory($this->createMock(IP\Address::class), 1);
	}

	/**
	 * See comment for testFactoryThrowsException1
	 */
	public function testFactoryThrowsException3()
	{
		$this->expectException(InvalidArgumentException::class);
		$b = $this->createMock(IP\Address::class);
		IP\NetworkAddress::factory($this->createMock(IP\Address::class), 3);
	}

	public function provideFactoryParseCIDR()
	{
		$data = [];
		$data[] = ['127.0.0.1/16', 24, 24];
		$data[] = ['127.0.0.1', NULL, 32];
		$data[] = ['127.0.0.1/24', NULL, 24];
		$data[] = ['::1', NULL, 128];
		$data[] = ['::1/58', 64, 64];
		$data[] = ['::1/58', NULL, 58];
		return $data;
	}

	/**
	 * @dataProvider provideFactoryParseCIDR
	 */
	public function testParseCIDR($address, $cidr, $expected)
	{
		$network = IP\NetworkAddress::factory($address, $cidr);
		$this->assertEquals($expected, $network->get_cidr());
	}

	/**
	 * Why is it bad to have this method?
	 */
	public function testUnimplementedException_generate_subnet_mask()
	{
		$this->assertFalse(method_exists(IP\NetworkAddress::class, 'generate_subnet_mask'));
	}

	/**
	 * Why is it bad to have this method?
	 */
	public function testUnimplementedException_get_global_netmask()
	{
		$this->assertFalse(method_exists(IP\NetworkAddress::class, 'get_global_netmask'));
	}

	/**
	 * @TODO add more addresses and v6 addresses
	 */
	public function providerCompare()
	{
		$data = [];
		$data[] = [IP\NetworkAddress::factory('0.0.0.0/16'), IP\NetworkAddress::factory('0.0.0.0/16'), 0];
		$data[] = [IP\NetworkAddress::factory('0.0.0.0/16'), IP\NetworkAddress::factory('0.0.0.1/16'), -1];
		$data[] = [IP\NetworkAddress::factory('0.0.0.1/16'), IP\NetworkAddress::factory('0.0.0.0/16'), 1];
		$data[] = [IP\NetworkAddress::factory('127.0.0.1/16') ,IP\NetworkAddress::factory( '127.0.0.1/16'), 0];
		$data[] = [IP\NetworkAddress::factory('127.0.10.1/16'), IP\NetworkAddress::factory('127.0.2.1/16'), 1];
		$data[] = [IP\NetworkAddress::factory('127.0.2.1/16') ,IP\NetworkAddress::factory( '127.0.10.1/16'), -1];
		return $data;
	}

	/**
	 * @dataProvider providerCompare
	 */
	public function testCompare($left, $right, $expected)
	{
		$cmp = IP\NetworkAddress::compare($left, $right);

		if ($cmp != 0)
			$cmp /= abs($cmp);

		$this->assertEquals($expected, $cmp);
	}

	public function providerAddressInNetwork()
	{
		$data = [];
		//                                    network           index, from, expected
		$data[] = [IP\NetworkAddress::factory('192.168.1.1/24'),  0, NULL, '192.168.1.0'];
		$data[] = [IP\NetworkAddress::factory('192.168.1.1/24'),  1, NULL, '192.168.1.1'];
		$data[] = [IP\NetworkAddress::factory('192.168.1.1/24'),  2, NULL, '192.168.1.2'];
		$data[] = [IP\NetworkAddress::factory('192.168.1.1/24'),  0, FALSE, '192.168.1.255'];
		$data[] = [IP\NetworkAddress::factory('192.168.1.1/24'), -1, NULL, '192.168.1.254'];
		$data[] = [IP\NetworkAddress::factory('192.168.1.1/24'), -2, NULL, '192.168.1.253'];
		$data[] = [IP\NetworkAddress::factory('192.168.1.1/24'), -3, NULL, '192.168.1.252'];

		$data[] = [IP\NetworkAddress::factory('192.168.1.1/24'),  0, NULL, '192.168.1.0'];
		$data[] = [IP\NetworkAddress::factory('192.168.1.1/24'),  1, NULL, '192.168.1.1'];
		$data[] = [IP\NetworkAddress::factory('192.168.1.1/24'),  0, FALSE, '192.168.1.255'];
		$data[] = [IP\NetworkAddress::factory('192.168.1.1/24'), -1, NULL, '192.168.1.254'];
		$data[] = [IP\NetworkAddress::factory('192.168.1.1/24'), -2, NULL, '192.168.1.253'];

		$data[] = [IP\NetworkAddress::factory('10.13.1.254/24'), 0, NULL, '10.13.1.0'];
		$data[] = [IP\NetworkAddress::factory('10.13.1.254/24'), 1, NULL, '10.13.1.1'];
		$data[] = [IP\NetworkAddress::factory('10.13.1.254/24'), 0, FALSE, '10.13.1.255'];
		$data[] = [IP\NetworkAddress::factory('10.13.1.254/24'), -1, NULL, '10.13.1.254'];

		$data[] = [IP\NetworkAddress::factory('10.13.1.254/24'), new Math_BigInteger( 0), NULL, '10.13.1.0'];
		$data[] = [IP\NetworkAddress::factory('10.13.1.254/24'), new Math_BigInteger( 1), NULL, '10.13.1.1'];
		$data[] = [IP\NetworkAddress::factory('10.13.1.254/24'), new Math_BigInteger( 0), FALSE, '10.13.1.255'];
		$data[] = [IP\NetworkAddress::factory('10.13.1.254/24'), new Math_BigInteger(-1), NULL, '10.13.1.254'];

		return $data;
	}

	/**
	 * @dataProvider providerAddressInNetwork
	 */
	public function testAddressInNetwork(
		IP\NetworkAddress $network,
		int|Math_BigInteger $index,
		?bool $from_start,
		string $expected
	)
	{
		$address = $network->get_address_in_network($index, $from_start);
		$this->assertEquals($expected, (string) $address);
	}

	public function providerCheck_IP_version()
	{
		return [
		];
	}

	public function providerCheck_IP_version_fail()
	{
		$a4 = IPv4_NetworkAddress_Tester::factory('10.1.0.0', 24);
		$b4 = IPv4_NetworkAddress_Tester::factory('10.2.0.0', 24);

		$a6 = IPv6_NetworkAddress_Tester::factory('::1', 24);
		$b6 = IPv6_NetworkAddress_Tester::factory('1::1', 24);

		$data = [];

		$data[] = [$a4, $a6];
		$data[] = [$a4, $b6];
		$data[] = [$a6, $a4];
		$data[] = [$a6, $b4];

		$data[] = [$b4, $a6];
		$data[] = [$b4, $b6];
		$data[] = [$b6, $a4];
		$data[] = [$b6, $b4];

		return $data;
	}

	/**
	 * @dataProvider providerCheck_IP_version_fail
	 */
	public function test_check_IP_version_fail($left, $right)
	{
		$this->assertFalse($left->is_same_version($right));
	}

	public function test_is_same_version4()
	{
		$sut = IPv4\NetworkAddress::factory('10.1.0.0', 24);
		$other = IPv4\NetworkAddress::factory('10.2.0.0', 24);
		$this->assertTrue($sut->is_same_version($other));
		$this->assertTrue($other->is_same_version($sut));
	}

	public function test_is_same_version6()
	{
		$sut = IPv4\NetworkAddress::factory('10.1.0.0', 24);
		$other = IPv4\NetworkAddress::factory('10.2.0.0', 24);
		$this->assertTrue($sut->is_same_version($other));
		$this->assertTrue($other->is_same_version($sut));
	}

	public function providerSubnets()
	{
		$data = [
			['2000::/3','2001:630:d0:f104::80a/128', true, true],
			['2000::/3','2001:630:d0:f104::80a/96', true, true],
			['2000::/3','2001:630:d0:f104::80a/48', true, true],

			['2001:630:d0:f104::80a/96', '2000::/3', true, false],
			['2001:630:d0:f104::80a/48', '2000::/3', true, false],

			['2000::/3','4000::/3', false, false],
			['2000::/3','1000::/3', false, false],
		];

		foreach ($data as &$d)
		{
			$d[0] = IP\NetworkAddress::factory($d[0]);
			$d[1] = IP\NetworkAddress::factory($d[1]);
		}

		return $data;
	}

	/**
	 * @dataProvider providerSubnets
	 */
	public function testSubnets($sub1, $sub2, $shares, $encloses)
	{
		$this->assertEquals($shares, $sub1->shares_subnet_space($sub2));
		$this->assertEquals($encloses, $sub1->encloses_subnet($sub2));
	}

	public function providerEnclosesAddress()
	{
		$data = [
			['2000::/3','2001:630:d0:f104::80a', true],
			['2000::/3','2001:630:d0:f104::80a', true],
			['2000::/3','2001:630:d0:f104::80a', true],

			['2001:630:d0:f104::80a/96', '2000::', false],
			['2001:630:d0:f104::80a/48', '2000::', false],

			['2000::/3','4000::', false],
			['2000::/3','1000::', false],
		];

		foreach ($data as &$d)
		{
			$d[0] = IP\NetworkAddress::factory($d[0]);
			$d[1] = IP\Address::factory($d[1]);
		}

		return $data;
	}

	/**
	 * @dataProvider providerEnclosesAddress
	 */
	public function testEnclosesAddress($subnet, $address, $expected)
	{
		$this->assertEquals($expected, $subnet->encloses_address($address));
	}

	public function provideNetworkIdentifiers()
	{
		return [
			[IP\NetworkAddress::factory('2000::/3'), true],
			[IP\NetworkAddress::factory('2000::1/3'), false],
		];
	}

	/**
	 * @dataProvider provideNetworkIdentifiers
	 */
	public function testNetworkIdentifiers($subnet, $expected)
	{
		$this->assertEquals($expected, $subnet->is_network_identifier());
		$this->assertTrue($subnet->get_network_identifier()->is_network_identifier());
	}

	public function test__toString()
	{
		$ip = '192.128.1.1/24';
		$this->assertEquals($ip, (string) IP\NetworkAddress::factory($ip));

		$ip = '::1/24';
		$this->assertEquals($ip, (string) IP\NetworkAddress::factory($ip));
	}

	public function providerExcluding()
	{
		$data = [];
		$data[] = [
			IP\NetworkAddress::factory('192.168.0.0/24'),
			[],
			[IP\NetworkAddress::factory('192.168.0.0/24'),]
		];
		$data[] = [
			IP\NetworkAddress::factory('192.168.0.0/24'),
			[IP\NetworkAddress::factory('192.168.0.0/25'),],
			[IP\NetworkAddress::factory('192.168.0.128/25'),],
		];
		$data[] = [
			IP\NetworkAddress::factory('192.168.0.0/24'),
			[
				IP\NetworkAddress::factory('192.168.0.64/26'),
				IP\NetworkAddress::factory('192.168.0.128/26'),
			],
			[
				IP\NetworkAddress::factory('192.168.0.0/26'),
				IP\NetworkAddress::factory('192.168.0.192/26'),
			]
		];
		$data[] = [
			IP\NetworkAddress::factory('192.168.0.0/24'),
			[IP\NetworkAddress::factory('192.168.0.0/26'),],
			[
				IP\NetworkAddress::factory('192.168.0.64/26'),
				IP\NetworkAddress::factory('192.168.0.128/25'),
			]
		];
		$data[] = [
			IP\NetworkAddress::factory('192.168.0.0/24'),
			[IP\NetworkAddress::factory('192.168.0.0/27'),],
			[
				IP\NetworkAddress::factory('192.168.0.32/27'),
				IP\NetworkAddress::factory('192.168.0.64/26'),
				IP\NetworkAddress::factory('192.168.0.128/25'),
			]
		];

		// Test out of range exclusions
		$data[] = [
			IP\NetworkAddress::factory('192.168.0.0/24'),
			[IP\NetworkAddress::factory('10.0.0.0/24'),],
			[IP\NetworkAddress::factory('192.168.0.0/24'),]
		];
		$data[] = [
			IP\NetworkAddress::factory('192.168.0.0/24'),
			[
				IP\NetworkAddress::factory('10.0.0.0/24'),
				IP\NetworkAddress::factory('192.168.0.0/25'),
			],
			[IP\NetworkAddress::factory('192.168.0.128/25'),]
		];

		// Test an encompassing subnet
		$data[] = [
			IP\NetworkAddress::factory('192.168.0.0/24'),
			[IP\NetworkAddress::factory('192.168.0.0/23'),],
			[]
		];

		return $data;
	}

	/**
	 * @dataProvider providerExcluding
	 */
	public function testExcluding($block, $excluded, $expected)
	{
		$this->assertEquals($expected, $block->excluding($excluded));
	}

	public function provideMerge()
	{
		$data = [];
			// Simple merge
		$data[] = [
			['0.0.0.0/32', '0.0.0.1/32'],
			['0.0.0.0/31']
		];
		// No merge
		$data[] = [
			['0.0.0.1/32'],
			['0.0.0.1/32']
		];
		$data[] = [
			['0.0.0.0/32', '0.0.0.2/32'],
			['0.0.0.0/32', '0.0.0.2/32']
		];
		// Duplicate entries
		$data[] = [
			['0.0.0.0/32', '0.0.0.1/32', '0.0.0.1/32'],
			['0.0.0.0/31']
		];
		$data[] = [
			['0.0.0.0/32', '0.0.0.0/32', '0.0.0.1/32'],
			['0.0.0.0/31']
		];
		$data[] = [
			['0.0.0.0/32', '0.0.0.0/32', '0.0.0.1/32', '0.0.0.1/32'],
			['0.0.0.0/31']
		];
		// Single merge with remainder
		$data[] = [
			['0.0.0.0/32', '0.0.0.1/32', '0.0.0.2/32'],
			['0.0.0.2/32', '0.0.0.0/31']
		];
		// Double merge
		$data[] = [
			['0.0.0.0/32', '0.0.0.1/32', '0.0.0.2/31'],
			['0.0.0.0/30']
		];
		// Non-network identifier
		$data[] = [
			['0.0.0.0/31', '0.0.0.3/31'],
			['0.0.0.0/30']
		];
		// IPv6 merges
		$data[] = [
			['::0/128', '::1/128'],
			['::0/127']
		];
		[
			['::0/128', '::1/128', '::2/127'],
			['::0/126']
		];
		// Mixed subnets
		$data[] = [
			['0.0.0.0/32', '0.0.0.1/32', '::0/128', '::1/128'],
			['0.0.0.0/31', '::0/127']
		];
		// Merge with duplicate resultant entry
		$data[] = [
			['0.0.0.0/22', '0.0.0.0/24', '0.0.1.0/24', '0.0.2.0/24', '0.0.3.0/24'],
			['0.0.0.0/22']
		];

		foreach ($data as &$x)
		{
			//Int addrs
			foreach ($x[0] as &$addr)
			{
				$addr = IP\NetworkAddress::factory($addr);
			}
			//Expected
			foreach ($x[1] as &$addr)
			{
				$addr = IP\NetworkAddress::factory($addr);
			}
		}
		return $data;
	}

	/**
	 * @dataProvider provideMerge
	 */
	public function testMerge($net_addrs, $expected)
	{
		$this->assertEquals($expected, IP\NetworkAddress::merge($net_addrs));
	}
}
