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
use \Leth\IPAddress\IP;

class NetworkAddressIterator implements \Iterator
{
	/**
	 * The network of iterator
	 *
	 * @var IP\NetworkAddress
	 */
	protected $network;

	/**
	 * The position of iterator
	 *
	 * @var IP\Address
	 */
	protected $position;

	public function __construct(NetworkAddress $network)
	{
		$this->network = $network;
		$this->rewind();
	}

	/**
	 * Set the pointer of iterator to a first network address
	 */
	public function rewind() : void
	{
		$this->position = $this->network->get_network_start();
	}

	/**
	 * Get the value from iterator
	 */
	public function current(): IP\Address
	{
		return $this->position;
	}

	/**
	 * Get the key from iterator
	 */
	public function key(): string
	{
		return $this->position->__toString();
	}

	/**
	 * Move the pointer of iterator to a next network address
	 */
	public function next(): void
	{
		$this->position = $this->position->add(1);
	}

	/**
	 * Next network address is valid
	 */
	public function valid(): bool
	{
		return ($this->position->compare_to($this->network->get_network_end()) <= 0);
	}
}
