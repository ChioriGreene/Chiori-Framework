<?php

namespace Penoaks\Queue\Capsule;

use Penoaks\Queue\QueueManager;
use Penoaks\Framework;
use Penoaks\Queue\QueueServiceProvider;
use Penoaks\Support\Traits\CapsuleManagerTrait;

class Manager
{
	use CapsuleManagerTrait;

	/**
	 * The queue manager instance.
	 *
	 * @var \Penoaks\Queue\QueueManager
	 */
	protected $manager;

	/**
	 * Create a new queue capsule manager.
	 *
	 * @param  \Penoaks\Framework  $bindings
	 * @return void
	 */
	public function __construct(Bindings $bindings = null)
	{
		$this->setupBindings($bindings ?: new Bindings);

		// Once we have the bindings setup, we will setup the default configuration
		// options in the bindings "config" bindings. This just makes this queue
		// manager behave correctly since all the correct binding are in place.
		$this->setupDefaultConfiguration();

		$this->setupManager();

		$this->registerConnectors();
	}

	/**
	 * Setup the default queue configuration options.
	 *
	 * @return void
	 */
	protected function setupDefaultConfiguration()
	{
		$this->bindings['config']['queue.default'] = 'default';
	}

	/**
	 * Build the queue manager instance.
	 *
	 * @return void
	 */
	protected function setupManager()
	{
		$this->manager = new QueueManager($this->bindings);
	}

	/**
	 * Register the default connectors that the component ships with.
	 *
	 * @return void
	 */
	protected function registerConnectors()
	{
		$provider = new QueueServiceProvider($this->bindings);

		$provider->registerConnectors($this->manager);
	}

	/**
	 * Get a connection instance from the global manager.
	 *
	 * @param  string  $connection
	 * @return \Penoaks\Contracts\Queue\Queue
	 */
	public static function connection($connection = null)
	{
		return static::$instance->getConnection($connection);
	}

	/**
	 * Push a new job onto the queue.
	 *
	 * @param  string  $job
	 * @param  mixed   $data
	 * @param  string  $queue
	 * @param  string  $connection
	 * @return mixed
	 */
	public static function push($job, $data = '', $queue = null, $connection = null)
	{
		return static::$instance->connection($connection)->push($job, $data, $queue);
	}

	/**
	 * Push a new an array of jobs onto the queue.
	 *
	 * @param  array   $jobs
	 * @param  mixed   $data
	 * @param  string  $queue
	 * @param  string  $connection
	 * @return mixed
	 */
	public static function bulk($jobs, $data = '', $queue = null, $connection = null)
	{
		return static::$instance->connection($connection)->bulk($jobs, $data, $queue);
	}

	/**
	 * Push a new job onto the queue after a delay.
	 *
	 * @param  \DateTime|int  $delay
	 * @param  string  $job
	 * @param  mixed   $data
	 * @param  string  $queue
	 * @param  string  $connection
	 * @return mixed
	 */
	public static function later($delay, $job, $data = '', $queue = null, $connection = null)
	{
		return static::$instance->connection($connection)->later($delay, $job, $data, $queue);
	}

	/**
	 * Get a registered connection instance.
	 *
	 * @param  string  $name
	 * @return \Penoaks\Contracts\Queue\Queue
	 */
	public function getConnection($name = null)
	{
		return $this->manager->connection($name);
	}

	/**
	 * Register a connection with the manager.
	 *
	 * @param  array   $config
	 * @param  string  $name
	 * @return void
	 */
	public function addConnection(array $config, $name = 'default')
	{
		$this->bindings['config']["queue.connections.{$name}"] = $config;
	}

	/**
	 * Get the queue manager instance.
	 *
	 * @return \Penoaks\Queue\QueueManager
	 */
	public function getQueueManager()
	{
		return $this->manager;
	}

	/**
	 * Pass dynamic instance methods to the manager.
	 *
	 * @param  string  $method
	 * @param  array  $parameters
	 * @return mixed
	 */
	public function __call($method, $parameters)
	{
		return call_user_func_array([$this->manager, $method], $parameters);
	}

	/**
	 * Dynamically pass methods to the default connection.
	 *
	 * @param  string  $method
	 * @param  array   $parameters
	 * @return mixed
	 */
	public static function __callStatic($method, $parameters)
	{
		return call_user_func_array([static::connection(), $method], $parameters);
	}
}
