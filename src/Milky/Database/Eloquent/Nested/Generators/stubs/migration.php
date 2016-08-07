<?php

use Milky\Database\Schema\Blueprint;
use Milky\Facades\Schema;

class {{class}} extends Migration
{
	/**
	 * Run the migrations.
	 */
	public function up()
	{
		Schema::create( '{{table}}', function ( Blueprint $table )
		{
			// These columns are needed for Baum's Nested Set implementation to work.
			// Column names may be changed, but they *must* all exist and be modified
			// in the model.
			// Take a look at the model scaffold comments for details.
			// We add indexes on parent_id, lft, rgt columns by default.
			$table->increments( 'id' );
			$table->integer( 'parent_id' )->nullable()->index();
			$table->integer( 'lft' )->nullable()->index();
			$table->integer( 'rgt' )->nullable()->index();
			$table->integer( 'depth' )->nullable();

			// Add needed columns here (f.ex: name, slug, path, etc.)
			// $table->string('name', 255);

			$table->timestamps();
		} );
	}

	/**
	 * Reverse the migrations.
	 */
	public function down()
	{
		Schema::drop( '{{table}}' );
	}
}
