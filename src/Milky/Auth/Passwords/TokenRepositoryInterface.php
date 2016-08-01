<?php namespace Milky\Auth\Passwords;

use Milky\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

interface TokenRepositoryInterface
{
	/**
	 * Create a new token.
	 *
	 * @param  CanResetPassword $user
	 * @return string
	 */
	public function create( CanResetPasswordContract $user );

	/**
	 * Determine if a token record exists and is valid.
	 *
	 * @param  CanResetPassword $user
	 * @param  string $token
	 * @return bool
	 */
	public function exists( CanResetPasswordContract $user, $token );

	/**
	 * Delete a token record.
	 *
	 * @param  string $token
	 * @return void
	 */
	public function delete( $token );

	/**
	 * Delete expired tokens.
	 *
	 * @return void
	 */
	public function deleteExpired();
}