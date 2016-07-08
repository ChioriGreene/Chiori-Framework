<?php

namespace Penoaks\Support\Facades;

/**
 * @see \Penoaks\Filesystem\Filesystem
 */
class File extends Facade
{
	/**
	 * Get the registered name of the component.
	 *
	 * @return string
	 */
	protected static function getFacadeAccessor()
	{
		return 'files';
	}
}
