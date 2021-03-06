<?php namespace Milky\Exceptions;

use Milky\Http\Routing\Route;

class UrlGenerationException extends \Exception
{
	/**
	 * Create a new exception for missing route parameters.
	 *
	 * @param  Route $route
	 * @return static
	 */
	public static function forMissingParameters( $route )
	{
		return new static( "Missing required parameters for [Route: {$route->getName()}] [URI: {$route->getPath()}]." );
	}
}
