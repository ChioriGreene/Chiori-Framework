<?php

namespace Penoaks\Queue\Jobs;

use Penoaks\Framework;
use Penoaks\Contracts\Queue\Job as JobContract;

class SyncJob extends Job implements JobContract
{
	/**
	 * The class name of the job.
	 *
	 * @var string
	 */
	protected $job;

	/**
	 * The queue message data.
	 *
	 * @var string
	 */
	protected $payload;

	/**
	 * Create a new job instance.
	 *
	 * @param  \Penoaks\Framework  $bindings
	 * @param  string  $payload
	 * @return void
	 */
	public function __construct(Bindings $bindings, $payload)
	{
		$this->payload = $payload;
		$this->bindings = $bindings;
	}

	/**
	 * Fire the job.
	 *
	 * @return void
	 */
	public function fire()
	{
		$this->resolveAndFire(json_decode($this->payload, true));
	}

	/**
	 * Get the raw body string for the job.
	 *
	 * @return string
	 */
	public function getRawBody()
	{
		return $this->payload;
	}

	/**
	 * Release the job back into the queue.
	 *
	 * @param  int   $delay
	 * @return void
	 */
	public function release($delay = 0)
	{
		parent::release($delay);
	}

	/**
	 * Get the number of times the job has been attempted.
	 *
	 * @return int
	 */
	public function attempts()
	{
		return 1;
	}

	/**
	 * Get the job identifier.
	 *
	 * @return string
	 */
	public function getJobId()
	{
		return '';
	}
}
