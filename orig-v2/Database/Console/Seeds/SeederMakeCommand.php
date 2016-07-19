<?php

namespace Penoaks\Database\Console\Seeds;

use Penoaks\Support\Composer;
use Penoaks\Filesystem\Filesystem;
use Penoaks\Console\GeneratorCommand;

class SeederMakeCommand extends GeneratorCommand
{
	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'make:seeder';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Create a new seeder class';

	/**
	 * The type of class being generated.
	 *
	 * @var string
	 */
	protected $type = 'Seeder';

	/**
	 * The Composer instance.
	 *
	 * @var \Penoaks\Support\Composer
	 */
	protected $composer;

	/**
	 * Create a new command instance.
	 *
	 * @param  \Penoaks\Filesystem\Filesystem  $files
	 * @param  \Penoaks\Support\Composer  $composer
	 * @return void
	 */
	public function __construct(Filesystem $files, Composer $composer)
	{
		parent::__construct($files);

		$this->composer = $composer;
	}

	/**
	 * Execute the console command.
	 *
	 * @return void
	 */
	public function fire()
	{
		parent::fire();

		$this->composer->dumpAutoloads();
	}

	/**
	 * Get the stub file for the generator.
	 *
	 * @return string
	 */
	protected function getStub()
	{
		return __DIR__ . '/stubs/seeder.stub';
	}

	/**
	 * Get the destination class path.
	 *
	 * @param  string  $name
	 * @return string
	 */
	protected function getPath($name)
	{
		return $this->framework->databasePath().'/seeds/'.$name.'.php';
	}

	/**
	 * Parse the name and format according to the root namespace.
	 *
	 * @param  string  $name
	 * @return string
	 */
	protected function parseName($name)
	{
		return $name;
	}
}