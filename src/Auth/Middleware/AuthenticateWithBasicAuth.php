<?php

namespace Penoaks\Auth\Middleware;

use Closure;
use Penoaks\Contracts\Auth\Factory as AuthFactory;

class AuthenticateWithBasicAuth
{
	/**
	 * The guard factory instance.
	 *
	 * @var \Penoaks\Contracts\Auth\Factory
	 */
	protected $auth;

	/**
	 * Create a new middleware instance.
	 *
	 * @param  \Penoaks\Contracts\Auth\Factory  $auth
	 * @return void
	 */
	public function __construct(AuthFactory $auth)
	{
		$this->auth = $auth;
	}

	/**
	 * Handle an incoming request.
	 *
	 * @param  \Penoaks\Http\Request  $request
	 * @param  \Closure  $next
	 * @param  string|null  $guard
	 * @return mixed
	 */
	public function handle($request, Closure $next, $guard = null)
	{
		return $this->auth->guard($guard)->basic() ?: $next($request);
	}
}
