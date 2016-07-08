<?php

namespace Penoaks\Auth;

use Penoaks\Support\Str;
use Penoaks\Contracts\Auth\UserProvider;
use Penoaks\Database\ConnectionInterface;
use Penoaks\Contracts\Hashing\Hasher as HasherContract;
use Penoaks\Contracts\Auth\Authenticatable as UserContract;

class DatabaseUserProvider implements UserProvider
{
	/**
	 * The active database connection.
	 *
	 * @var \Penoaks\Database\ConnectionInterface
	 */
	protected $conn;

	/**
	 * The hasher implementation.
	 *
	 * @var \Penoaks\Contracts\Hashing\Hasher
	 */
	protected $hasher;

	/**
	 * The table containing the users.
	 *
	 * @var string
	 */
	protected $table;

	/**
	 * Create a new database user provider.
	 *
	 * @param  \Penoaks\Database\ConnectionInterface  $conn
	 * @param  \Penoaks\Contracts\Hashing\Hasher  $hasher
	 * @param  string  $table
	 * @return void
	 */
	public function __construct(ConnectionInterface $conn, HasherContract $hasher, $table)
	{
		$this->conn = $conn;
		$this->table = $table;
		$this->hasher = $hasher;
	}

	/**
	 * Retrieve a user by their unique identifier.
	 *
	 * @param  mixed  $identifier
	 * @return \Penoaks\Contracts\Auth\Authenticatable|null
	 */
	public function retrieveById($identifier)
	{
		$user = $this->conn->table($this->table)->find($identifier);

		return $this->getGenericUser($user);
	}

	/**
	 * Retrieve a user by their unique identifier and "remember me" token.
	 *
	 * @param  mixed  $identifier
	 * @param  string  $token
	 * @return \Penoaks\Contracts\Auth\Authenticatable|null
	 */
	public function retrieveByToken($identifier, $token)
	{
		$user = $this->conn->table($this->table)
			->where('id', $identifier)
			->where('remember_token', $token)
			->first();

		return $this->getGenericUser($user);
	}

	/**
	 * Update the "remember me" token for the given user in storage.
	 *
	 * @param  \Penoaks\Contracts\Auth\Authenticatable  $user
	 * @param  string  $token
	 * @return void
	 */
	public function updateRememberToken(UserContract $user, $token)
	{
		$this->conn->table($this->table)
				->where('id', $user->getAuthIdentifier())
				->update(['remember_token' => $token]);
	}

	/**
	 * Retrieve a user by the given credentials.
	 *
	 * @param  array  $credentials
	 * @return \Penoaks\Contracts\Auth\Authenticatable|null
	 */
	public function retrieveByCredentials(array $credentials)
	{
		// First we will add each credential element to the query as a where clause.
		// Then we can execute the query and, if we found a user, return it in a
		// generic "user" object that will be utilized by the Guard instances.
		$query = $this->conn->table($this->table);

		foreach ($credentials as $key => $value)
{
			if (! Str::contains($key, 'password'))
{
				$query->where($key, $value);
			}
		}

		// Now we are ready to execute the query to see if we have an user matching
		// the given credentials. If not, we will just return nulls and indicate
		// that there are no matching users for these given credential arrays.
		$user = $query->first();

		return $this->getGenericUser($user);
	}

	/**
	 * Get the generic user.
	 *
	 * @param  mixed  $user
	 * @return \Penoaks\Auth\GenericUser|null
	 */
	protected function getGenericUser($user)
	{
		if ($user !== null)
{
			return new GenericUser((array) $user);
		}
	}

	/**
	 * Validate a user against the given credentials.
	 *
	 * @param  \Penoaks\Contracts\Auth\Authenticatable  $user
	 * @param  array  $credentials
	 * @return bool
	 */
	public function validateCredentials(UserContract $user, array $credentials)
	{
		$plain = $credentials['password'];

		return $this->hasher->check($plain, $user->getAuthPassword());
	}
}
