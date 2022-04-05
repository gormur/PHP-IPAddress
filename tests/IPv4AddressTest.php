<?php

use Leth\IPAddress\IPv4, Leth\IPAddress\IPv6;
use Leth\IPAddress\IPv6\Address;
use PHPUnit\Framework\TestCase;

// class Testing_IPv4_Address extends IPv4\Address
// {
// 	public static function factory(mixed $address): IP\Address
// 	{
// 		return new Testing_IPv4\Address($address);
// 	}
// }

/**
 * Tests for the IPv4\Address Class
 *
 * @package default
 * @author Marcus Cobden
 */
class IPv4_Address_Test extends TestCase
{

	public function providerFactory()
	{
		$data = [];
		$data[] = ['127.0.0.1',  '127.0.0.1'];
		$data[] = ['127.0.0.0',  '127.0.0.0'];
		$data[] = ['127.0.0.2',  '127.0.0.2'];
		$data[] = ['192.168.1.1', '192.168.1.1'];
		$data[] = ['192.168.2.1', '192.168.2.1'];
		$data[] = ['192.168.1.2', '192.168.1.2'];
		$data[] = ['10.0.0.2',   '10.0.0.2'];
		$data[] = ['10.0.0.1',   '10.0.0.1'];
		$data[] = [new Math_BigInteger(1), '0.0.0.1'];
		$data[] = [new Math_BigInteger(2), '0.0.0.2'];
		$data[] = [new Math_BigInteger(3), '0.0.0.3'];
		$data[] = [new Math_BigInteger(256), '0.0.1.0'];
		return $data;
	}

	/**
	 * @dataProvider providerFactory
	 */
	public function testFactory($input, $expected)
	{
		$instance = IPv4\Address::factory($input);
		$this->assertNotNull($instance);
		$this->assertEquals($expected, (string) $instance);
	}

	public function testFormatInteger()
	{
		$ip = IPv4\Address::factory('127.0.0.1');
		$this->assertEquals(2130706433, $ip->format(IPv4\Address::FORMAT_INTEGER));
	}

	public function providerFormatException()
	{
		$bad_mode = -1;
		$data = static::providerFactory();
		foreach ($data as $i => $entry) {
			$data[$i] = [$entry[0], $bad_mode];
		}

		return $data;
	}

	/**
	 * @dataProvider providerFormatException
	 */
	public function testFormatException($input, $mode)
	{
		$this->expectException(InvalidArgumentException::class);
		$instance = IPv4\Address::factory($input);
		echo $instance->format($mode);
	}

	public function providerFactoryException()
	{
		$data = [];
		$data[] = ['256.0.0.1'];
		$data[] = ['127.-1.0.1'];
		$data[] = ['127.128.256.1'];
		$data[] = [new Math_BigInteger('99999999999999999')];
		$data[] = [123.45];
		$data[] = [-123.45];
		$data[] = ['cake'];
		$data[] = ['12345'];
		$data[] = ['-12345'];
		$data[] = ['0000:0000:0000:ffff:0127:0000:0000:0001'];
		return $data;
	}

	/**
	 * @dataProvider providerFactoryException
	 */
	public function testFactoryException($input)
	{
		$this->expectException(InvalidArgumentException::class);
		IPv4\Address::factory($input);
	}

	public function providerBitwise()
	{
		$data = [];
			//       OP1        OP2        AND        OR         XOR        NOT
		$data[] = ['0.0.0.1', '0.0.0.1', '0.0.0.1', '0.0.0.1', '0.0.0.0', '255.255.255.254'];
		$data[] = ['0.0.0.0', '0.0.0.1', '0.0.0.0', '0.0.0.1', '0.0.0.1', '255.255.255.255'];
		$data[] = ['0.0.0.1', '0.0.0.0', '0.0.0.0', '0.0.0.1', '0.0.0.1', '255.255.255.254'];
		$data[] = ['0.0.0.0', '0.0.0.0', '0.0.0.0', '0.0.0.0', '0.0.0.0', '255.255.255.255'];
		return $data;
	}

	/**
	 * @dataProvider providerBitwise
	 */
	public function testBitwise($ip1, $ip2, $ex_and, $ex_or, $ex_xor, $ex_not)
	{
		$ip1 = IPv4\Address::factory($ip1);
		$ip2 = IPv4\Address::factory($ip2);

		$this->assertEquals($ex_and, (string) $ip1->bitwise_and($ip2));
		$this->assertEquals($ex_or , (string) $ip1->bitwise_or($ip2));
		$this->assertEquals($ex_xor, (string) $ip1->bitwise_xor($ip2));
		$this->assertEquals($ex_not, (string) $ip1->bitwise_not());
	}

	// TODO Check this
	// public function providerAsIPv6Address()
	// {
	// 	return [
	// 		['127.0.0.1', '0000:0000:0000:ffff:0127:0000:0000:0001'],
	// 	];
	// }
	//
	// /**
	//  * @dataProvider providerAsIPv6Address
	//  */
	// public function testAsIPv6Address($v4, $v6)
	// {
	// 	$ip = IPv4\Address::factory($v4);
	//
	// 	$this->assertEquals($v6, (string) $ip->asIPv6Address());
	// }

	public function providerAddSubtract()
	{
		$data = [];
		$data[] = [IPv4\Address::factory('0.0.0.0')  , 0, IPv4\Address::factory('0.0.0.0')];
		$data[] = [IPv4\Address::factory('0.0.0.0')  , 1, IPv4\Address::factory('0.0.0.1')];
		$data[] = [IPv4\Address::factory('0.0.0.1')  , 0, IPv4\Address::factory('0.0.0.1')];
		$data[] = [IPv4\Address::factory('0.0.0.1')  , 1, IPv4\Address::factory('0.0.0.2')];
		$data[] = [IPv4\Address::factory('0.0.0.10') , 1, IPv4\Address::factory('0.0.0.11')];
		$data[] = [IPv4\Address::factory('0.0.0.255'), 1, IPv4\Address::factory('0.0.1.0')];
		$data[] = [IPv4\Address::factory('0.0.255.0'), 257, IPv4\Address::factory('0.1.0.1')];
		$data[] = [IPv4\Address::factory('255.255.0.0')  , 0, IPv4\Address::factory('255.255.0.0')];
		$data[] = [IPv4\Address::factory('255.255.0.0')  , 1, IPv4\Address::factory('255.255.0.1')];
		$data[] = [IPv4\Address::factory('255.255.0.1')  , 0, IPv4\Address::factory('255.255.0.1')];
		$data[] = [IPv4\Address::factory('255.255.0.1')  , 1, IPv4\Address::factory('255.255.0.2')];
		$data[] = [IPv4\Address::factory('255.255.0.10') , 1, IPv4\Address::factory('255.255.0.11')];
		$data[] = [IPv4\Address::factory('255.255.0.255'), 1, IPv4\Address::factory('255.255.1.0')];
		$data[] = [IPv4\Address::factory('255.0.255.0'), 257, IPv4\Address::factory('255.1.0.1')];
		$data[] = [IPv4\Address::factory('192.168.0.0'), 4, IPv4\Address::factory('192.168.0.4')];
		return $data;
	}

	/**
	 * @dataProvider providerAddSubtract
	 */
	public function testAddSubtract($left, $right, $expected)
	{
		$result = $left->add($right);
		$this->assertEquals(0, $result->compare_to($expected));
		$result = $result->subtract($right);
		$this->assertEquals(0, $result->compare_to($left));
	}

	public function providerAsIPv6Address()
	{
		$data = [];
		//                               input                               expected
		$data[] = [IPv4\Address::factory('0.0.0.0'  ), IPv6\Address::factory('::ffff:0:0'   )];
		$data[] = [IPv4\Address::factory('0.0.0.1'  ), IPv6\Address::factory('::ffff:0:1'   )];
		$data[] = [IPv4\Address::factory('0.0.0.255'), IPv6\Address::factory('::ffff:0:ff'  )];
		$data[] = [IPv4\Address::factory('0.0.255.0'), IPv6\Address::factory('::ffff:0:ff00')];
		$data[] = [IPv4\Address::factory('0.255.0.0'), IPv6\Address::factory('::ffff:ff:0'  )];
		$data[] = [IPv4\Address::factory('255.0.0.0'), IPv6\Address::factory('::ffff:ff00:0')];
		return $data;
	}

	/**
	 * @dataProvider providerAsIPv6Address
	 */
	public function testAsIPv6Address(IPv4\Address $input, IPv6\Address $expected_equal)
	{
		$converted = $input->as_IPv6_address();

		$this->assertInstanceOf(Address::class, $converted);
		$this->assertEquals(0, $converted->compare_to($expected_equal));
	}

	public function testGetOctet()
	{
		$ip = IPv4\Address::factory('10.250.30.40');

		$this->assertEquals(10, $ip->get_octet(-4));
		$this->assertEquals(250, $ip->get_octet(-3));
		$this->assertEquals(30, $ip->get_octet(-2));
		$this->assertEquals(40, $ip->get_octet(-1));

		$this->assertEquals(10, $ip->get_octet(0));
		$this->assertEquals(250, $ip->get_octet(1));
		$this->assertEquals(30, $ip->get_octet(2));
		$this->assertEquals(40, $ip->get_octet(3));
	}

	public function testGetOctetWithOutOfRangeOctet()
	{
		$ip = IPv4\Address::factory('10.250.30.40');
		$this->expectException(InvalidArgumentException::class);
		$ip->get_octet(4);
	}

	public function testArrayAccess()
	{
		$ip = IPv4\Address::factory('10.250.30.40');
		$this->assertEquals(10, $ip[-4]);
		$this->assertEquals(250, $ip[1]);
	}

	public function testArrayAccessWithOutOfRangeOctet()
	{
		$sut = IPv4\Address::factory('10.250.30.40');
		$this->expectException(InvalidArgumentException::class);
		$sut[4];
	}

	public function testArrayAccessThatOutOfRangeOctetIsNotSet()
	{
		$sut = IPv4\Address::factory('10.250.30.40');
		$this->assertFalse(isset($sut[4]));
	}

	public function testArrayAccessSet()
	{
		$this->expectException(\LogicException::class);
		$ip = IPv4\Address::factory('10.250.30.40');
		$ip[0] = 0;
	}

	public function testArrayAccessUnset()
	{
		$this->expectException(\LogicException::class);
		$ip = IPv4\Address::factory('10.250.30.40');
		unset($ip[0]);
	}
}
