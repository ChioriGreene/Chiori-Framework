<?php

namespace Penoaks\Testing\Concerns;

use Penoaks\Contracts\Console\Kernel;

trait InteractsWithConsole
{
	/**
	 * The last code returned by Artisan CLI.
	 *
	 * @var int
	 */
	protected $code;

	/**
	 * Call artisan command and return code.
	 *
	 * @param string  $command
	 * @param array   $parameters
	 * @return int
	 */
	public function artisan($command, $parameters = [])
	{
		return $this->code = $this->fw->bindings[Kernel::class]->call($command, $parameters);
	}
}
