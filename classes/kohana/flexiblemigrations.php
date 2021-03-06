<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Flexible Migrations
 *
 * An open source utility inspired by Ruby on Rails
 *
 * Reworked for Kohana by Fernando Petrelli
 *
 * Based on Migrations module by Jamie Madill
 *
 * @package		Flexiblemigrations
 * @author 		Matías Montes
 * @author    Jamie Madill
 * @author    Fernando Petrelli
 */

class Kohana_Flexiblemigrations
{
	protected $_config;

	public function __construct()
	{
		$this->_config = Kohana::$config->load('flexiblemigrations')->as_array();
	}

	public function get_config() 
	{
		return $this->_config;
	}

	/**
	 * Run all pending migrations
	 *
	 */
	public function migrate() 
	{
		$migration_keys = $this->get_migration_keys();
		$migrations = ORM::factory('migration')->find_all();

		//Remove executed migrations from queue
		foreach ($migrations as $migration) 
		{
			if (array_key_exists($migration->hash, $migration_keys)) 
			{
				unset($migration_keys[$migration->hash]);
			}
		}

		if (count($migration_keys) > 0) 
		{
			foreach ($migration_keys as $key => $value) 
			{
				echo "Executing migration: '" . $value . "' with hash: " .$key;
				
				$migration_object = $this->load_migration($key);
				$migration_object->up();
				$model = ORM::factory('migration');
				$model->hash = $key;
				$model->name = $value;
				$model->save();
				echo $model ? '<span class="ok">OK</span>' : '<span class="error">ERROR</span>';
			}
		} 
		else 
		{
			echo "Nothing to migrate";
		}
		echo html::anchor( Route::get('migrations_route')->uri() , "<br>Back");
	}

	/**
	 * Rollback last executed migration.
	 *
	 */
	public function rollback() 
	{
		//Get last executed migration
		$model = ORM::factory('migration')->order_by('created_at','DESC')->order_by('hash','DESC')->limit(1)->find();

		if ($model->loaded()) 
		{
			$migration_object = $this->load_migration($model->hash);
			$migration_object->down();
			if ($model) 
			{
				echo "Migration '" . $model->name . "' with hash: " . $model->hash . ' was succefully "rollbacked"';
			} else {
				echo "ERROR WHEN ROLLBACK";
			}
			echo "<br>";
			$model->delete();
		} 
		else 
		{
			echo "Nothing to do.";
		}
		echo html::anchor( Route::get('migrations_route')->uri() , "<br>Back");
	}

	/**
	 * Rollback last executed migration.
	 *
	 */
	public function get_timestamp() 
	{
		return date('YmdHis');
	}

	/**
	 * Get all valid migrations file names
	 *
	 * @return array migrations_filenames
	 */
	public function get_migrations()	
	{
		$migrations = glob($this->_config['path'].'*'.EXT);
		foreach ($migrations as $i => $file)
		{
			$name = basename($file, EXT);
			if (!preg_match('/^\d{14}_(\w+)$/', $name)) //Check filename format
				unset($migrations[$i]);
		}
		sort($migrations);
		return $migrations;
	}

	/**
	 * Get all migration keys (timestamps)
	 *
	 * @return array migrations_keys
	 */
	protected function get_migration_keys() 
	{
		$migrations = $this->get_migrations();
		$keys = array();
		foreach ($migrations as $migration) 
		{
			$sub_migration = substr(basename($migration, EXT), 0, 14);
			$keys = Arr::merge($keys, array($sub_migration => substr(basename($migration, EXT), 15)));
		}
		return $keys;
	}

	/**
	 * Load the migration file, and returns a Migration object
	 *
	 * @return Migration object with up and down functions
	 */
	protected function load_migration($version) 
	{
		$f = glob($this->_config['path'].$version.'*'. EXT);

		if ( count($f) > 1 ) // Only one migration per step is permitted
			throw new Kohana_Exception('There are repeated migration names');

		if ( count($f) == 0 ) // Migration step not found
			throw new Kohana_Exception('Nothing to rollback');

		$file = basename($f[0]);
		$name = basename($f[0], EXT);

		// Filename validation
		if ( !preg_match('/^\d{14}_(\w+)$/', $name, $match) )
			throw new Kohana_Exception('Invalid filename :file', array(':file' => $file));

		$match[1] = strtolower($match[1]);
		require $f[0]; //Includes migration class file

		$class = ucfirst($match[1]); //Get the class name capitalized

		if ( !class_exists($class) )
			throw new Kohana_Exception('Class :class doesn\'t exists', array(':class' => $class));

		if ( !method_exists($class, 'up') OR !method_exists($class, 'down') )
			throw new Kohana_Exception('Up or down functions missing on class :class', array(':class' => $class));

		return new $class();
	}

}