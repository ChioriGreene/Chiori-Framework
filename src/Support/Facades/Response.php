<?php

namespace Penoaks\Support\Facades;

/**
 * @see \Penoaks\Contracts\Routing\ResponseFactory
 */
class Response extends Facade
{
	/**
	 * Get the registered name of the component.
	 *
	 * @return string
	 */
	protected static function getFacadeAccessor()
	{
		return 'Penoaks\Contracts\Routing\ResponseFactory';
	}
}
