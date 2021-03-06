<?php namespace Milky;

use Milky\Binding\UniversalBuilder;
use Milky\Config\Configuration;
use Milky\Config\ConfigurationLoader;
use Milky\Console\ConsoleServiceResolver;
use Milky\Exceptions\FrameworkException;
use Milky\Facades\Log;
use Milky\Helpers\Arr;
use Milky\Helpers\Str;
use Milky\Hooks\HookDispatcher;
use Milky\Http\HttpFactory;
use Milky\Logging\LogBuilder;
use Milky\Logging\Logger;
use Milky\Providers\ProviderRepository;

/**
 * @Product: Milky Framework
 * @Version 6.0.0 (Polkadot)
 * @Last Updated: December 2016
 * @PHP Version: 5.5.9 or Newer
 *
 * @Author: Penoaks Publishing Co.
 * @E-Mail: development@penoaks.com
 * @Website: http://penoaks.com
 *
 * The MIT License (MIT)
 * Copyright 2017 Penoaks Publishing Ltd. <development@penoaks.org>
 *
 * This Source Code is subject to the terms of the MIT License.
 * If a copy of the license was not distributed with this file,
 * You can obtain one at https://opensource.org/licenses/MIT.
 */
class Framework
{
	const PRODUCT = "Milky Framework";
	const VERSION = "v6.0 (Polkadot)";
	const COPYRIGHT = "Copyright © 2017 Penoaks Publishing Ltd.";

	/**
	 * @var bool
	 */
	private $isBooted = false;

	/**
	 * @var string
	 */
	private $namespace;

	/**
	 * @var Framework
	 */
	private static $fw;

	/**
	 * The Hook Dispatcher
	 *
	 * @var HookDispatcher
	 */
	public $hooks;

	/**
	 * The Configuration
	 *
	 * @var Configuration
	 */
	public $config;

	/**
	 * The Logger
	 *
	 * @var Logger
	 */
	public $log;

	/**
	 * The Provider Repository
	 *
	 * @var ProviderRepository
	 */
	public $providers;

	/**
	 * The application base path
	 *
	 * @var string
	 */
	public $basePath;

	/**
	 * @var array
	 */
	private $paths = [];

	/**
	 * A custom callback used to configure Monolog.
	 *
	 * @var callable|null
	 */
	protected $monologConfigurator;

	/**
	 * Framework Constructor
	 *
	 * @param string $basePath
	 *
	 * @throws FrameworkException
	 */
	public function __construct( $basePath )
	{
		if ( !is_null( static::$fw ) )
			throw new FrameworkException( "Framework is already running." );
		static::$fw = $this;

		$this->basePath = $basePath;

		$this->hooks = new HookDispatcher();

		$this->paths = [
			'public' => ['__base'],
			'fw' => ['__base', 'fw'],
			'src' => ['__fw', 'src'],
			'lang' => ['__fw', 'lang'],
			'views' => ['__fw', 'views'],
			'config' => ['__fw', 'config'],
			'database' => ['__fw', 'database'],
			'storage' => ['__fw', 'storage'],
			'sessions' => ['__storage', 'sessions'],
			'cache' => ['__storage', 'cache'],
			'logs' => ['__storage', 'logs'],
		];

		$this->config = ConfigurationLoader::load( $this );

		$this->paths = array_merge( $this->paths, $this->config->get( 'app.paths', [] ) );

		$this->log = LogBuilder::build( $this );

		$this->log->info( "Milky Framework Loading" );

		$this->providers = new ProviderRepository();

		foreach ( $this->config->get( 'app.providers', [] ) as $provider )
			$this->providers->register( $provider );

		new UniversalBuilder( $this );

		$this->hooks->trigger( 'fw.loaded', $this );
		$this->log->info( "Milky Framework Loaded" );
	}

	/**
	 * @return Framework
	 */
	public static function fw()
	{
		return static::$fw;
	}

	/**
	 * @return HookDispatcher
	 */
	public static function hooks()
	{
		return static::fw()->hooks;
	}

	/**
	 * @return Logger
	 */
	public static function log()
	{
		return static::fw()->log;
	}

	/**
	 * @return bool
	 */
	public static function isRunning()
	{
		return !is_null( static::$fw );
	}

	public function boot()
	{
		if ( $this->isBooted )
			throw new FrameworkException( "This framework has already been booted." );
		$this->isBooted = true;

		$this->hooks->trigger( 'fw.booting', $this );
		Log::info( "Milky Framework Booted" );

		$this->providers->boot();

		$this->hooks->trigger( 'fw.booted', $this );
		Log::info( "Milky Framework Booted" );
	}

	public function isBooted()
	{
		return $this->isBooted;
	}

	public function newConsoleFactory()
	{
		UniversalBuilder::registerResolver( new ConsoleServiceResolver() );
		return UniversalBuilder::resolve( 'console.factory' );
	}

	public function newHttpFactory( $request = null )
	{
		return HttpFactory::build( compact( 'request' ) );
	}

	public function getProduct()
	{
		return static::PRODUCT;
	}

	public function getVersion()
	{
		return static::VERSION;
	}

	public function getCopyright()
	{
		return static::COPYRIGHT;
	}

	/**
	 * Append args to the base path
	 *
	 * @return string
	 *
	 * @throws FrameworkException
	 */
	public function buildPath()
	{
		$slugs = func_get_args();

		if ( is_array( $slugs[0] ) )
			$slugs = $slugs[0];

		if ( count( $slugs ) == 0 )
			return $this->basePath;

		if ( Str::startsWith( $slugs[0], '__' ) )
		{
			$key = substr( $slugs[0], 2 );
			if ( $key == 'base' )
				$slugs[0] = $this->basePath;
			else if ( array_key_exists( $key, $this->paths ) )
			{
				$paths = $this->paths[$key];
				if ( is_array( $paths ) )
				{
					unset( $slugs[0] );
					foreach ( array_reverse( $paths ) as $slug )
						$slugs = Arr::prepend( $slugs, $slug );
				}
				else
					$slugs[0] = $paths;
			}
			else
				throw new FrameworkException( "Path [" . $key . "] is not set" );

			return $this->buildPath( $slugs );
		}
		else if ( !Str::startsWith( $slugs[0], '/' ) )
			$slugs = Arr::prepend( $slugs, $this->basePath );

		$dir = strpos( end( $slugs ), '.' ) !== false ? implode( DIRECTORY_SEPARATOR, array_slice( $slugs, 0, count( $slugs ) - 1 ) ) : implode( DIRECTORY_SEPARATOR, $slugs );

		if ( !file_exists( $dir ) )
			if ( !mkdir( $dir, 0755, true ) )
				throw new FrameworkException( "The directory [" . $dir . "] does not exist and we failed to create it" );

		return implode( DIRECTORY_SEPARATOR, $slugs );
	}

	/**
	 * Define a callback to be used to configure Monolog.
	 *
	 * @param  callable $callback
	 * @return $this
	 */
	public function configureMonologUsing( callable $callback )
	{
		$this->monologConfigurator = $callback;

		return $this;
	}

	/**
	 * Determine if the application has a custom Monolog configurator.
	 *
	 * @return bool
	 */
	public function hasMonologConfigurator()
	{
		return !is_null( $this->monologConfigurator );
	}

	/**
	 * Get the custom Monolog configurator for the application.
	 *
	 * @return callable
	 */
	public function getMonologConfigurator()
	{
		return $this->monologConfigurator;
	}

	public static function config()
	{
		return static::fw()->config;
	}

	/**
	 * Determine if the application is currently down for maintenance.
	 *
	 * @return bool
	 */
	public function isDownForMaintenance()
	{
		return false;
	}

	/**
	 * Get or check the current application environment.
	 *
	 * @param  mixed
	 * @return string
	 */
	public static function environment()
	{
		$env = static::fw()->config->get( 'app.env', 'production' );

		if ( func_num_args() > 0 )
		{
			$patterns = is_array( func_get_arg( 0 ) ) ? func_get_arg( 0 ) : func_get_args();

			foreach ( $patterns as $pattern )
				if ( Str::is( $pattern, $env ) )
					return true;

			return false;
		}

		return $env;
	}

	/**
	 * Get the application namespace.
	 *
	 * @return string
	 *
	 * @throws \RuntimeException
	 */
	public function getNamespace()
	{
		if ( !is_null( $this->namespace ) )
			return $this->namespace;

		$composer = json_decode( file_get_contents( $this->buildPath( '__fw', 'composer.json' ) ), true );
		foreach ( (array) data_get( $composer, 'autoload.psr-4' ) as $namespace => $path )
			foreach ( (array) $path as $pathChoice )
				if ( realpath( $this->buildPath( '__fw' ) ) == realpath( $this->buildPath( '__base' ) . '/' . $pathChoice ) )
					return $this->namespace = $namespace;

		throw new \RuntimeException( 'Unable to detect application namespace.' );
	}

	/**
	 * Unused... for now
	 */
	public function terminate()
	{

	}
}
