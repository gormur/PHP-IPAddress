<?php
use Leth\IPAddress\IP, Leth\IPAddress\IPv6;
use PHPUnit\Framework\TestCase;

class TestingIPv6_Address extends IPv6\Address
{
	public static function factory(mixed $address): IPv6\Address
	{
		return new TestingIPv6_Address($address);
	}

	public function call_bitwise_operation($flag, IP\Address $other = NULL)
	{
		$this->bitwise_operation($flag, $other);
	}
}

/**
 * Tests for the IPv6\Address Class
 *
 * @package default
 * @author Marcus Cobden
 */
class IPv6_Address_Test extends TestCase
{

	public function providerFactory()
	{
		$data = [];
		$data[] = [
			'::1',
			'::1',
			'0:0:0:0:0:0:0:1',
			'0000:0000:0000:0000:0000:0000:0000:0001'
		];
		$data[] = [
			1,
			'::1',
			'0:0:0:0:0:0:0:1',
			'0000:0000:0000:0000:0000:0000:0000:0001'
		];
		$data[] = [
			'fe80::226:bbff:fe14:7372',
			'fe80::226:bbff:fe14:7372',
			'fe80:0:0:0:226:bbff:fe14:7372',
			'fe80:0000:0000:0000:0226:bbff:fe14:7372'
		];
		$data[] = [
			'::ffff:127:0:0:1',
			'::ffff:127:0:0:1',
			'0:0:0:ffff:127:0:0:1',
			'0000:0000:0000:ffff:0127:0000:0000:0001'
		];

		return $data;
	}

	/**
	 * @dataProvider providerFactory
	 */
	public function testFactory($input, $compact, $abbr, $full)
	{
		$instance = IPv6\Address::factory($input);

		$this->assertNotNull($instance);
		$this->assertEquals($compact, $instance->format(IP\Address::FORMAT_COMPACT));
		$this->assertEquals($abbr, $instance->format(IPv6\Address::FORMAT_ABBREVIATED));
		$this->assertEquals($full, $instance->format(IP\Address::FORMAT_FULL));
	}

	public function providerFormatException()
	{
		$data = static::providerFactory();
		array_map(
			function ($entry) {
				return [$entry[0], $bad_mode = -1];
			},
			$data
		);
		return $data;
	}

	/**
	 * @dataProvider providerFormatException
	 */
	public function testFormatException($input, $mode)
	{
		$this->expectException(InvalidArgumentException::class);
		$instance = IPv6\Address::factory($input);
		echo $instance->format($mode);
	}

	/**
	 * Provide strings that may look like an ip but
	 * is invalid in some way.
	 */
	public function providerFactoryException()
	{
		$data = [];
		$data[] = ['256.0.0.1'];
		$data[] = ['127.-1.0.1'];
		$data[] = ['127.128.256.1'];
		$data[] = ['cake'];
		$data[] = ['12345'];
		$data[] = ['-12345'];
		$data[] = ['0000:0000:0000:ffff:0127:0000:0000:000g'];
		$data[] = ['000000000000ffff0127000000000001'];
		$data[] = [[]];
		return $data;
	}

	/**
	 * @dataProvider providerFactoryException
	 */
	public function testFactoryException($input)
	{
		$this->expectException(InvalidArgumentException::class);
		IPv6\Address::factory($input);
	}

	public function providerAddSubtract()
	{
		$data = [];
		$data[] = [IPv6\Address::factory('::')  , 0, IPv6\Address::factory('::' )];
		$data[] = [IPv6\Address::factory('::1') , 0, IPv6\Address::factory('::1' )];
		$data[] = [IPv6\Address::factory('::1') , 1, IPv6\Address::factory('::2' )];
		$data[] = [IPv6\Address::factory('::1') , 2, IPv6\Address::factory('::3' )];
		$data[] = [IPv6\Address::factory('::5') , 6, IPv6\Address::factory('::b' )];
		$data[] = [IPv6\Address::factory('::10'), 1, IPv6\Address::factory('::11' )];
		$data[] = [IPv6\Address::factory('::10'), new Math_BigInteger(1), IPv6\Address::factory('::11' )];
		$data[] = [IPv6\Address::factory('::10'), new Math_BigInteger(2), IPv6\Address::factory('::12' )];
		return $data;
	}

	/**
	 * @dataProvider providerAddSubtract
	 */
	public function testAddSubtract($left, $right, $expected)
	{
		$result = $left->add($right);
		$this->assertEquals(0, $result->compare_to($expected));
		$again = $result->subtract($right);
		$this->assertEquals(0, $again->compare_to($left));
	}

	public function providerCompareTo()
	{
		$data = [];
		$data[] = [IPv6\Address::factory('::' ), IPv6\Address::factory('::'),  0];
		$data[] = [IPv6\Address::factory('::1'), IPv6\Address::factory('::1'), 0];
		$data[] = [IPv6\Address::factory('::1'), IPv6\Address::factory('::2'), -1];
		$data[] = [IPv6\Address::factory('::2'), IPv6\Address::factory('::1'), 1];
		$data[] = [IPv6\Address::factory('::f'), IPv6\Address::factory('::1'), 1];
		$data[] = [IPv6\Address::factory('::a'), IPv6\Address::factory('::b'), -1];
	}

	/**
	 * @dataProvider providerCompareTo
	 */
	public function testCompareTo($left, $right, $expected)
	{
		$this->assertEquals($expected, $left->compare_to($right));
	}

	public function providerBitwise()
	{
		$data = [];
			//     OP1    OP2    AND    OR     XOR     NOT
		$data[] = ['::1', '::1', '::1', '::1', '::0', 'ffff:ffff:ffff:ffff:ffff:ffff:ffff:fffe'];
		$data[] = ['::' , '::1', '::0', '::1', '::1', 'ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff'];
		$data[] = ['::1', '::' , '::0', '::1', '::1', 'ffff:ffff:ffff:ffff:ffff:ffff:ffff:fffe'];
		$data[] = ['::' , '::' , '::0', '::0', '::0', 'ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff'];

		for ($i=0; $i < count($data); $i++) {
			for ($j=0; $j < 6; $j++) {
				$data[$i][$j] = IPv6\Address::factory($data[$i][$j]);
			}
		}

		return $data;
	}

	/**
	 * @dataProvider providerBitwise
	 */
	public function testBitwise($ip1, $ip2, $ex_and, $ex_or, $ex_xor, $ex_not)
	{
		$this->assertEquals((string) $ex_and, (string) $ip1->bitwise_and($ip2));
		$this->assertEquals((string) $ex_or , (string) $ip1->bitwise_or($ip2));
		$this->assertEquals((string) $ex_xor, (string) $ip1->bitwise_xor($ip2));
		$this->assertEquals((string) $ex_not, (string) $ip1->bitwise_not());
	}

	public function testBitwiseException()
	{

		$ip = TestingIPv6_Address::factory('::1');

		try
		{
			$ip->call_bitwise_operation('!', $ip);
			$this->fail('An expected exception has not been raised.');
		}
		catch (InvalidArgumentException $e)
		{
			$exception_message = $e->getMessage();
			$expected_exception_message = "Unknown Operation type '!'.";
			$this->assertEquals($expected_exception_message, $exception_message);
		}

		$ip->call_bitwise_operation('&', $ip);
		$ip->call_bitwise_operation('|', $ip);
		$ip->call_bitwise_operation('^', $ip);
		$ip->call_bitwise_operation('~');
	}

	//
	// public function provider_as_IPv4\Address()
	// {
	// 	$data = [];
	// 	$data[] = ['0000:0000:0000:ffff:0127:0000:0000:0001', '127.0.0.1'];
	// return $data;
	// 	);
	// }
	//
	// /**
	//  * @dataProvider provider_as_IPv4\Address
	//  */
	// public function test_as_IPv4\Address($v6, $v4 = NULL)
	// {
	// 	$ip = new IPv6\Address($v6);
	//
	// 	if ($v4 === NULL)
	// 		$this->assertFalse($ip->isEncodedIPv4Address());
	// 	else
	// 		$this->assertEquals($v4, (string) $ip->asIPv4Address());
	//
	// }

	public function testGetOctet()
	{
		$ip = IPv6\Address::factory('0001:0002:aaaa:1234:abcd:1000:2020:fffe');
		$this->assertEquals(0, $ip->get_octet(-16));
		$this->assertEquals(1, $ip->get_octet(-15));
		$this->assertEquals(0xAA, $ip->get_octet(-11));
		$this->assertEquals(0x12, $ip->get_octet(-10));
		$this->assertEquals(0x20, $ip->get_octet(-4));
		$this->assertEquals(0x20, $ip->get_octet(-3));
		$this->assertEquals(0xFF, $ip->get_octet(-2));
		$this->assertEquals(0xFE, $ip->get_octet(-1));
		$this->assertEquals(0, $ip->get_octet(0));
		$this->assertEquals(1, $ip->get_octet(1));
		$this->assertEquals(0x10, $ip->get_octet(10));
		$this->assertEquals(0xFE, $ip->get_octet(15));
	}

	public function testGetOctetWithTooLargeOctetNumber16()
	{
		$ip = IPv6\Address::factory('0001:0002:aaaa:1234:abcd:1000:2020:fffe');
		$this->expectException(InvalidArgumentException::class);
		$ip->get_octet(16);
	}

	public function testMappedIPv4()
	{
		$ip = IP\Address::factory('::ffff:141.44.23.50');
		$this->assertEquals(1, $ip->is_encoded_IPv4_address());
		$ipv4 = $ip->as_IPv4_address();
		$this->assertEquals($ipv4->format(IP\Address::FORMAT_COMPACT), '141.44.23.50');
	}

	public function testMayMappedIPv4Format() {
		$mappedIPv4String = '::ffff:141.44.23.50';
		$ordinaryIPv6String = '1:2:aaaa:1234:abcd:1000:2020:fffe';
		$mappedIPv4Address = IP\Address::factory($mappedIPv4String);
		$ordinaryIPv6Address = IP\Address::factory($ordinaryIPv6String);
		$this->assertEquals($mappedIPv4Address->format(IPv6\Address::FORMAT_MAY_MAPPED_COMPACT), $mappedIPv4String);
		$this->assertEquals($ordinaryIPv6Address->format(IPv6\Address::FORMAT_MAY_MAPPED_COMPACT), $ordinaryIPv6String);
	}

	public function testArrayAccess()
	{
		$ip = IPv6\Address::factory('0001:0002:aaaa:1234:abcd:1000:2020:fffe');
		$this->assertEquals(0x12, $ip[-10]);
		$this->assertEquals(0x10, $ip[10]);
	}

	public function testArrayAccessWithOctetOutOfRange16()
	{
		$ip = IPv6\Address::factory('0001:0002:aaaa:1234:abcd:1000:2020:fffe');
		$this->expectException(InvalidArgumentException::class);
		$ip[16];
	}

	/**
	 */
	public function providerPadIps(): array
	{
		$data = [];
		$data[] = ['::', '0000:0000:0000:0000:0000:0000:0000:0000'];
		$data[] = ['::fff', '0000:0000:0000:0000:0000:0000:0000:0fff'];
		$data[] = ['::ff:fff', '0000:0000:0000:0000:0000:0000:00ff:0fff'];
		$data[] = ['::f:ff:fff', '0000:0000:0000:0000:0000:000f:00ff:0fff'];
		$data[] = ['fff::', '0fff:0000:0000:0000:0000:0000:0000:0000'];
		$data[] = ['fff:ff::', '0fff:00ff:0000:0000:0000:0000:0000:0000'];
		$data[] = ['fff:ff:f::', '0fff:00ff:000f:0000:0000:0000:0000:0000'];
		$data[] = ['2001:630:d0::', '2001:0630:00d0:0000:0000:0000:0000:0000'];
		$data[] = ['f:f:f:f:f:f:f:f', '000f:000f:000f:000f:000f:000f:000f:000f'];
		$data[] = ['fff::fff', '0fff:0000:0000:0000:0000:0000:0000:0fff'];
		$data[] = ['fff:0000:bb::aa:0000:fff', '0fff:0000:00bb:0000:0000:00aa:0000:0fff'];
		// not need pad
		$data[] = ['ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff', 'ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff'];
		$data[] = ['0000:0000:0000:0000:0000:0000:0000:0000', '0000:0000:0000:0000:0000:0000:0000:0000'];
		return $data;
	}

	/**
	 * @dataProvider providerPadIps
	 */
	public function testPad(string $actual, string $expected)
	{
		$this->assertEquals($expected, IPv6\Address::pad($actual));
	}
}
