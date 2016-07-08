<?php

namespace Penoaks\Auth\Access;

use Penoaks\Contracts\Auth\Access\Gate;

trait AuthorizesRequests
{
	/**
	 * Authorize a given action against a set of arguments.
	 *
	 * @param  mixed  $ability
	 * @param  mixed|array  $arguments
	 * @return \Penoaks\Auth\Access\Response
	 *
	 * @throws \Penoaks\Auth\Access\AuthorizationException
	 */
	public function authorize($ability, $arguments = [])
	{
		list($ability, $arguments) = $this->parseAbilityAndArguments($ability, $arguments);

		return fw(Gate::class)->authorize($ability, $arguments);
	}

	/**
	 * Authorize a given action for a user.
	 *
	 * @param  \Penoaks\Contracts\Auth\Authenticatable|mixed  $user
	 * @param  mixed  $ability
	 * @param  mixed|array  $arguments
	 * @return \Penoaks\Auth\Access\Response
	 *
	 * @throws \Penoaks\Auth\Access\AuthorizationException
	 */
	public function authorizeForUser($user, $ability, $arguments = [])
	{
		list($ability, $arguments) = $this->parseAbilityAndArguments($ability, $arguments);

		return fw(Gate::class)->forUser($user)->authorize($ability, $arguments);
	}

	/**
	 * Guesses the ability's name if it wasn't provided.
	 *
	 * @param  mixed  $ability
	 * @param  mixed|array  $arguments
	 * @return array
	 */
	protected function parseAbilityAndArguments($ability, $arguments)
	{
		if (is_string($ability))
{
			return [$ability, $arguments];
		}

		return [debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3)[2]['function'], $ability];
	}
}
