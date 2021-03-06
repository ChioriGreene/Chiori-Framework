<?php namespace Milky\Database;

use InvalidArgumentException;
use Milky\Binding\UniversalBuilder;
use Milky\Database\Connectors\ConnectionFactory;
use Milky\Facades\Config;
use Milky\Framework;
use Milky\Helpers\Arr;
use Milky\Helpers\Str;
use PDO;

class DatabaseManager implements ConnectionResolverInterface
{
	/**
	 * The database connection factory instance.
	 *
	 * @var ConnectionFactory
	 */
	protected $factory;

	/**
	 * The active connection instances.
	 *
	 * @var array
	 */
	protected $connections = [];

	/**
	 * The custom connection resolvers.
	 *
	 * @var array
	 */
	protected $extensions = [];

	/**
	 * @return $this
	 */
	public static function i()
	{
		return UniversalBuilder::resolve( 'db.mgr' );
	}

	/**
	 * Create a new database manager instance.
	 *
	 * @param ConnectionFactory $factory
	 */
	public function __construct( ConnectionFactory $factory )
	{
		$this->factory = $factory;
	}

	public function factory()
	{
		return $this->factory;
	}

	/**
	 * Get a database connection instance.
	 *
	 * @param  string $name
	 * @return Connection
	 */
	public function connection( $name = null )
	{
		list( $name, $type ) = $this->parseConnectionName( $name );

		// If we haven't created this connection, we'll create it based on the config
		// provided in the application. Once we've created the connections we will
		// set the "fetch mode" for PDO which determines the query return types.
		if ( !isset( $this->connections[$name] ) )
		{
			$connection = $this->makeConnection( $name );

			$this->setPdoForType( $connection, $type );

			$this->connections[$name] = $this->prepare( $connection );
		}

		return $this->connections[$name];
	}

	/**
	 * Parse the connection into an array of the name and read / write type.
	 *
	 * @param  string $name
	 * @return array
	 */
	protected function parseConnectionName( $name )
	{
		$name = $name ?: $this->getDefaultConnection();

		return Str::endsWith( $name, ['::read', '::write'] ) ? explode( '::', $name, 2 ) : [$name, null];
	}

	/**
	 * Disconnect from the given database and remove from local cache.
	 *
	 * @param  string $name
	 * @return void
	 */
	public function purge( $name = null )
	{
		$this->disconnect( $name );

		unset( $this->connections[$name] );
	}

	/**
	 * Disconnect from the given database.
	 *
	 * @param  string $name
	 * @return void
	 */
	public function disconnect( $name = null )
	{
		if ( isset( $this->connections[$name = $name ?: $this->getDefaultConnection()] ) )
		{
			$this->connections[$name]->disconnect();
		}
	}

	/**
	 * Reconnect to the given database.
	 *
	 * @param  string $name
	 * @return Connection
	 */
	public function reconnect( $name = null )
	{
		$this->disconnect( $name = $name ?: $this->getDefaultConnection() );

		if ( !isset( $this->connections[$name] ) )
		{
			return $this->connection( $name );
		}

		return $this->refreshPdoConnections( $name );
	}

	/**
	 * Refresh the PDO connections on a given connection.
	 *
	 * @param  string $name
	 * @return Connection
	 */
	protected function refreshPdoConnections( $name )
	{
		$fresh = $this->makeConnection( $name );

		return $this->connections[$name]->setPdo( $fresh->getPdo() )->setReadPdo( $fresh->getReadPdo() );
	}

	/**
	 * Make the database connection instance.
	 *
	 * @param  string $name
	 * @return Connection
	 */
	protected function makeConnection( $name )
	{
		$config = $this->getConfig( $name );

		// First we will check by the connection name to see if an extension has been
		// registered specifically for that connection. If it has we will call the
		// Closure and pass it the config allowing it to resolve the connection.
		if ( isset( $this->extensions[$name] ) )
			return call_user_func( $this->extensions[$name], $config, $name );

		$driver = $config['driver'];

		// Next we will check to see if an extension has been registered for a driver
		// and will call the Closure if so, which allows us to have a more generic
		// resolver for the drivers themselves which applies to all connections.
		if ( isset( $this->extensions[$driver] ) )
			return call_user_func( $this->extensions[$driver], $config, $name );

		return $this->factory->make( $config, $name );
	}

	/**
	 * Prepare the database connection instance.
	 *
	 * @param Connection $connection
	 * @return Connection
	 */
	protected function prepare( Connection $connection )
	{
		$connection->setFetchMode( Config::get( 'database.fetch' ) );

		// Here we'll set a reconnector callback. This reconnector can be any callable
		// so we will set a Closure to reconnect from this manager with the name of
		// the connection, which will allow us to reconnect from the connections.
		$connection->setReconnector( function ( $connection )
		{
			$this->reconnect( $connection->getName() );
		} );

		return $connection;
	}

	/**
	 * Prepare the read write mode for database connection instance.
	 *
	 * @param Connection $connection
	 * @param  string $type
	 * @return Connection
	 */
	protected function setPdoForType( Connection $connection, $type = null )
	{
		if ( $type == 'read' )
		{
			$connection->setPdo( $connection->getReadPdo() );
		}
		elseif ( $type == 'write' )
		{
			$connection->setReadPdo( $connection->getPdo() );
		}

		return $connection;
	}

	/**
	 * Get the configuration for a connection.
	 *
	 * @param  string $name
	 * @return array
	 *
	 * @throws \InvalidArgumentException
	 */
	protected function getConfig( $name )
	{
		$name = $name ?: $this->getDefaultConnection();

		// To get the database connection configuration, we will just pull each of the
		// connection configurations and get the configurations for the given name.
		// If the configuration doesn't exist, we'll throw an exception and bail.
		$connections = Framework::config()->get( 'database.connections' );

		if ( is_null( $config = Arr::get( $connections, $name ) ) )
			throw new InvalidArgumentException( "Database [$name] not configured." );

		return $config;
	}

	/**
	 * Get the default connection name.
	 *
	 * @return string
	 */
	public function getDefaultConnection()
	{
		return Framework::config()['database.default'];
	}

	/**
	 * Set the default connection name.
	 *
	 * @param  string $name
	 * @return void
	 */
	public function setDefaultConnection( $name )
	{
		Framework::config()['database.default'] = $name;
	}

	/**
	 * Get all of the support drivers.
	 *
	 * @return array
	 */
	public function supportedDrivers()
	{
		return ['mysql', 'pgsql', 'sqlite', 'sqlsrv'];
	}

	/**
	 * Get all of the drivers that are actually available.
	 *
	 * @return array
	 */
	public function availableDrivers()
	{
		return array_intersect( $this->supportedDrivers(), str_replace( 'dblib', 'sqlsrv', PDO::getAvailableDrivers() ) );
	}

	/**
	 * Register an extension connection resolver.
	 *
	 * @param  string $name
	 * @param  callable $resolver
	 * @return void
	 */
	public function extend( $name, callable $resolver )
	{
		$this->extensions[$name] = $resolver;
	}

	/**
	 * Return all of the created connections.
	 *
	 * @return array
	 */
	public function getConnections()
	{
		return $this->connections;
	}

	/**
	 * Dynamically pass methods to the default connection.
	 *
	 * @param  string $method
	 * @param  array $parameters
	 * @return mixed
	 */
	public function __call( $method, $parameters )
	{
		return call_user_func_array( [$this->connection(), $method], $parameters );
	}
}
