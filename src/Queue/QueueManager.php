<?php

namespace Penoaks\Queue;

use Closure;
use InvalidArgumentException;
use Penoaks\Contracts\Queue\Factory as FactoryContract;
use Penoaks\Contracts\Queue\Monitor as MonitorContract;

class QueueManager implements FactoryContract, MonitorContract
{
	/**
	 * The application instance.
	 *
	 * @var \Penoaks\Framework
	 */
	protected $fw;

	/**
	 * The array of resolved queue connections.
	 *
	 * @var array
	 */
	protected $connections = [];

	/**
	 * The array of resolved queue connectors.
	 *
	 * @var array
	 */
	protected $connectors = [];

	/**
	 * Create a new queue manager instance.
	 *
	 * @param  \Penoaks\Framework  $fw
	 * @return void
	 */
	public function __construct($fw)
	{
		$this->fw = $fw;
	}

	/**
	 * Register an event listener for the before job event.
	 *
	 * @param  mixed  $callback
	 * @return void
	 */
	public function before($callback)
	{
		$this->fw->bindings['events']->listen(Events\JobProcessing::class, $callback);
	}

	/**
	 * Register an event listener for the after job event.
	 *
	 * @param  mixed  $callback
	 * @return void
	 */
	public function after($callback)
	{
		$this->fw->bindings['events']->listen(Events\JobProcessed::class, $callback);
	}

	/**
	 * Register an event listener for the exception occurred job event.
	 *
	 * @param  mixed  $callback
	 * @return void
	 */
	public function exceptionOccurred($callback)
	{
		$this->fw->bindings['events']->listen(Events\JobExceptionOccurred::class, $callback);
	}

	/**
	 * Register an event listener for the daemon queue loop.
	 *
	 * @param  mixed  $callback
	 * @return void
	 */
	public function looping($callback)
	{
		$this->fw->bindings['events']->listen('illuminate.queue.looping', $callback);
	}

	/**
	 * Register an event listener for the failed job event.
	 *
	 * @param  mixed  $callback
	 * @return void
	 */
	public function failing($callback)
	{
		$this->fw->bindings['events']->listen(Events\JobFailed::class, $callback);
	}

	/**
	 * Register an event listener for the daemon queue stopping.
	 *
	 * @param  mixed  $callback
	 * @return void
	 */
	public function stopping($callback)
	{
		$this->fw->bindings['events']->listen(Events\WorkerStopping::class, $callback);
	}

	/**
	 * Determine if the driver is connected.
	 *
	 * @param  string  $name
	 * @return bool
	 */
	public function connected($name = null)
	{
		return isset($this->connections[$name ?: $this->getDefaultDriver()]);
	}

	/**
	 * Resolve a queue connection instance.
	 *
	 * @param  string  $name
	 * @return Queue
	 */
	public function connection($name = null)
	{
		$name = $name ?: $this->getDefaultDriver();

		// If the connection has not been resolved yet we will resolve it now as all
		// of the connections are resolved when they are actually needed so we do
		// not make any unnecessary connection to the various queue end-points.
		if (! isset($this->connections[$name]))
{
			$this->connections[$name] = $this->resolve($name);

			$this->connections[$name]->setBindings($this->fw);

			$this->connections[$name]->setEncrypter($this->fw->bindings['encrypter']);
		}

		return $this->connections[$name];
	}

	/**
	 * Resolve a queue connection.
	 *
	 * @param  string  $name
	 * @return Queue
	 */
	protected function resolve($name)
	{
		$config = $this->getConfig($name);

		return $this->getConnector($config['driver'])->connect($config);
	}

	/**
	 * Get the connector for a given driver.
	 *
	 * @param  string  $driver
	 * @return \Penoaks\Queue\Connectors\ConnectorInterface
	 *
	 * @throws \InvalidArgumentException
	 */
	protected function getConnector($driver)
	{
		if (isset($this->connectors[$driver]))
{
			return call_user_func($this->connectors[$driver]);
		}

		throw new InvalidArgumentException("No connector for [$driver]");
	}

	/**
	 * Add a queue connection resolver.
	 *
	 * @param  string	$driver
	 * @param  \Closure  $resolver
	 * @return void
	 */
	public function extend($driver, Closure $resolver)
	{
		return $this->addConnector($driver, $resolver);
	}

	/**
	 * Add a queue connection resolver.
	 *
	 * @param  string	$driver
	 * @param  \Closure  $resolver
	 * @return void
	 */
	public function addConnector($driver, Closure $resolver)
	{
		$this->connectors[$driver] = $resolver;
	}

	/**
	 * Get the queue connection configuration.
	 *
	 * @param  string  $name
	 * @return array
	 */
	protected function getConfig($name)
	{
		if ($name === null || $name === 'null')
{
			return ['driver' => 'null'];
		}

		return $this->fw->bindings['config']["queue.connections.{$name}"];
	}

	/**
	 * Get the name of the default queue connection.
	 *
	 * @return string
	 */
	public function getDefaultDriver()
	{
		return $this->fw->bindings['config']['queue.default'];
	}

	/**
	 * Set the name of the default queue connection.
	 *
	 * @param  string  $name
	 * @return void
	 */
	public function setDefaultDriver($name)
	{
		$this->fw->bindings['config']['queue.default'] = $name;
	}

	/**
	 * Get the full name for the given connection.
	 *
	 * @param  string  $connection
	 * @return string
	 */
	public function getName($connection = null)
	{
		return $connection ?: $this->getDefaultDriver();
	}

	/**
	 * Determine if the application is in maintenance mode.
	 *
	 * @return bool
	 */
	public function isDownForMaintenance()
	{
		return $this->fw->isDownForMaintenance();
	}

	/**
	 * Dynamically pass calls to the default connection.
	 *
	 * @param  string  $method
	 * @param  array   $parameters
	 * @return mixed
	 */
	public function __call($method, $parameters)
	{
		$callable = [$this->connection(), $method];

		return call_user_func_array($callable, $parameters);
	}
}
