<?php
/*
 * This file is part of the PHP-IPAddress library.
 *
 * The PHP-IPAddress library is free software: you can redistribute it
 * and/or modify it under the terms of the GNU Lesser General Public License
 * as published by the Free Software Foundation, either version 3 of
 * the License, or (at your option) any later version.
 *
 * The PHP-IPAddress library is distributed in the hope that it will
 * be useful, but WITHOUT ANY WARRANTY; without even the implied
 * warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with the PHP-IPAddress library.
 * If not, see <http://www.gnu.org/licenses/>.
 */
namespace Leth\IPAddress\IP;

use InvalidArgumentException;
use \Leth\IPAddress\IP, \Leth\IPAddress\IPv4, \Leth\IPAddress\IPv6;
use Math_BigInteger;

/**
 * An abstract representation of an IP Address.
 *
 * @author Marcus Cobden
 */
abstract class Address implements \ArrayAccess
{
	const IP_VERSION = -1;
	const FORMAT_FULL = 0;
	const FORMAT_COMPACT = 1;

	/**
	 * Internal representation of the address. Format may vary.
	 */
	protected mixed $address;

	/**
	 * Create an IP address object from the supplied address either IPv4\Address or IPv6\Address
	 */
	public static function factory(mixed $address): IP\Address
	{
		if ($address instanceof IP\Address)
		{
			return $address;
		}
		elseif (is_int($address) OR (is_string($address) AND filter_var($address, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)))
		{
			return IPv4\Address::factory($address);
		}
		elseif ($address instanceof Math_BigInteger OR (is_string($address) AND filter_var($address, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)))
		{
			return IPv6\Address::factory($address);
		}
		else
		{
			throw new \InvalidArgumentException('Unable to guess IP address type from \''.$address.'\'.');
		}
	}

	/**
	 * Compare two IP Address objects.
	 *
	 * This method is a wrapper for the compare_to method and is useful in callback situations, e.g.
	 * usort($addresses, ['IP\Address', 'compare']);
	 */
	public static function compare(IP\Address $a, IP\Address $b): int
	{
		return $a->compare_to($b);
	}

	/**
	 * Create a new IP Address object.
	 */
	protected function __construct($address)
	{
		$this->address = $address;
	}

	/**
	 * Add the given value to this address.
	 */
	public abstract function add(int|Math_BigInteger $value): IP\Address;

	/**
	 * Subtract the given value from this address.
	 */
	public abstract function subtract(int|Math_BigInteger $value): IP\Address;

	/**
	 * Compute the bitwise AND of this address and another.
	 */
	public abstract function bitwise_and(IP\Address $other): IP\Address;

	/**
	 * Compute the bitwise OR of this address and another.
	 */
	public abstract function bitwise_or(IP\Address $other): IP\Address;

	/**
	 * Compute the bitwise XOR (Exclusive OR) of this address and another.
	 */
	public abstract function bitwise_xor(IP\Address $other): IP\Address;

	/**
	 * Compute the bitwise NOT of this address.
	 */
	public abstract function bitwise_not(): IP\Address;

	/**
	 * Compare this IP Address with another.
	 * Suitable to use as callback for sorting functions like usort.
	 * Returns -1, 0 or 1
	 */
	public abstract function compare_to(IP\Address $other): int;

	/**
	 * Convert this object to a string representation
	 */
	public function __toString()
	{
		return $this->format(IP\Address::FORMAT_COMPACT);
	}

	/**
	 * Return the string representation of the address
	 */
	public abstract function format($mode): string;

	/**
	 * Check that this instance and the supplied instance are of the same class.
	 *
	 * @throws \InvalidArgumentException if objects are of the same class.
	 */
	protected function check_types(IP\Address $other)
	{
		if (get_class($this) != get_class($other)) {
			throw new InvalidArgumentException('Incompatible types.');
		}
	}

	/**
	 * Get the specified octet from this address.
	 */
	public function get_octet(int $number): int
	{
		$address = unpack("C*", $this->address);
		$index = (($number >= 0) ? $number : count($address) + $number);
		$index++;
		if (!isset($address[$index])) {
			throw new InvalidArgumentException("The specified octet ({$number})out of range");
		}

		return $address[$index];
	}

	/**
	 * Whether octet index in allowed range
	 */
	public function offsetExists(mixed $offset): bool
	{
		try {
			$this->get_octet($offset);
		} catch (InvalidArgumentException $e) {
			return false;
		}
		return true;
	}

	/**
	 * Get the octet value from index
	 */
	public function offsetGet(mixed $offset): mixed
	{
		return $this->get_octet($offset);
	}

	/**
	 * Operation unsupported
	 *
	 * @throws \LogicException
	 */
	public function offsetSet(mixed $offset, mixed $value): void
	{
		throw new \LogicException('Operation unsupported');
	}

	/**
	 * Operation unsupported
	 *
	 * @throws \LogicException
	 */
	public function offsetUnset(mixed $offset): void
	{
		throw new \LogicException('Operation unsupported');
	}
}
