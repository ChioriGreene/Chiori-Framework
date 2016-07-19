<?php
namespace Penoaks\Events;

/**
 * The MIT License (MIT)
 * Copyright 2016 Penoaks Publishing Co. <development@penoaks.org>
 *
 * This Source Code is subject to the terms of the MIT License.
 * If a copy of the license was not distributed with this file,
 * You can obtain one at https://opensource.org/licenses/MIT.
 */

use Penoaks\Barebones\Bootstrap;
use Penoaks\Barebones\Event;

class BootstrapPreEvent implements Event
{
	use Traits\Cancellable;

	/**
	 * @var Bootstrap
	 */
	private $bootstrap;

	/**
	 * BootstrapPreEvent constructor.
	 *
	 * @param Bootstrap $bootstrap
	 */
	public function __construct( $bootstrap )
	{
		$this->bootstrap = $bootstrap;
	}

	/**
	 * Gets the bootstrap instance
	 *
	 * @return Bootstrap
	 */
	public function getBootstrap()
	{
		return $this->bootstrap;
	}

	/**
	 * Checks the $bootstrap class
	 *
	 * @param Bootstrap $bootstrap
	 * @return bool
	 */
	public function is( Bootstrap $bootstrap )
	{
		return $this->bootstrap == $bootstrap || get_class( $this->bootstrap ) == get_class( $bootstrap );
	}
}