<?php

namespace Penoaks\Database\Query\Processors;

use Exception;
use Penoaks\Database\Query\Builder;

class SqlServerProcessor extends Processor
{
	/**
	 * Process an "insert get ID" query.
	 *
	 * @param  \Penoaks\Database\Query\Builder  $query
	 * @param  string  $sql
	 * @param  array   $values
	 * @param  string  $sequence
	 * @return int
	 */
	public function processInsertGetId(Builder $query, $sql, $values, $sequence = null)
	{
		$connection = $query->getConnection();

		$connection->insert($sql, $values);

		if ($connection->getConfig('odbc') === true)
{
			$id = $this->processInsertGetIdForOdbc($connection);
		}
else
{
			$id = $connection->getPdo()->lastInsertId();
		}

		return is_numeric($id) ? (int) $id : $id;
	}

	/**
	 * Process an "insert get ID" query for ODBC.
	 *
	 * @param  \Penoaks\Database\Connection  $connection
	 * @return int
	 */
	protected function processInsertGetIdForOdbc($connection)
	{
		$result = $connection->select('SELECT CAST(COALESCE(SCOPE_IDENTITY(), @@IDENTITY) AS int) AS insertid');

		if (! $result)
{
			throw new Exception('Unable to retrieve lastInsertID for ODBC.');
		}

		return $result[0]->insertid;
	}

	/**
	 * Process the results of a column listing query.
	 *
	 * @param  array  $results
	 * @return array
	 */
	public function processColumnListing($results)
	{
		$mapping = function ($r)
{
			$r = (object) $r;

			return $r->name;
		};

		return array_map($mapping, $results);
	}
}
