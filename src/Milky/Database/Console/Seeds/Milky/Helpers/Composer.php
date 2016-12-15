<?php namespace Milky\Helpers;

use Milky\Filesystem\Filesystem;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessUtils;
use Symfony\Component\Process\PhpExecutableFinder;

/**
 * The MIT License (MIT)
 * Copyright 2017 Penoaks Publishing Ltd. <development@penoaks.org>
 *
 * This Source Code is subject to the terms of the MIT License.
 * If a copy of the license was not distributed with this file,
 * You can obtain one at https://opensource.org/licenses/MIT.
 */
class Composer
{
	/**
	 * The filesystem instance.
	 *
	 * @var Filesystem
	 */
	protected $files;
	/**
	 * The working path to regenerate from.
	 *
	 * @var string
	 */
	protected $workingPath;

	/**
	 * Create a new Composer manager instance.
	 *
	 * @param  Filesystem $files
	 * @param  string|null $workingPath
	 * @return void
	 */
	public function __construct( Filesystem $files, $workingPath = null )
	{
		$this->files = $files;
		$this->workingPath = $workingPath;
	}

	/**
	 * Regenerate the Composer autoloader files.
	 *
	 * @param  string $extra
	 * @return void
	 */
	public function dumpAutoloads( $extra = '' )
	{
		$process = $this->getProcess();
		$process->setCommandLine( trim( $this->findComposer() . ' dump-autoload ' . $extra ) );
		$process->run();
	}

	/**
	 * Regenerate the optimized Composer autoloader files.
	 *
	 * @return void
	 */
	public function dumpOptimized()
	{
		$this->dumpAutoloads( '--optimize' );
	}

	/**
	 * Get the composer command for the environment.
	 *
	 * @return string
	 */
	protected function findComposer()
	{
		if ( !$this->files->exists( $this->workingPath . '/composer.phar' ) )
		{
			return 'composer';
		}
		$binary = ProcessUtils::escapeArgument( ( new PhpExecutableFinder )->find( false ) );
		if ( defined( 'HHVM_VERSION' ) )
		{
			$binary .= ' --php';
		}

		return "{$binary} composer.phar";
	}

	/**
	 * Get a new Symfony process instance.
	 *
	 * @return Process
	 */
	protected function getProcess()
	{
		return ( new Process( '', $this->workingPath ) )->setTimeout( null );
	}

	/**
	 * Set the working path used by the class.
	 *
	 * @param  string $path
	 * @return $this
	 */
	public function setWorkingPath( $path )
	{
		$this->workingPath = realpath( $path );

		return $this;
	}
}
