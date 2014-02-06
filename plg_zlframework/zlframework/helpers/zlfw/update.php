<?php
/**
* @package		ZL Framework
* @author    	ZOOlanders http://www.zoolanders.com
* @copyright 	Copyright (C) JOOlanders SL
* @license   	http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

/*
	Class: zlfwHelperUpdate
		The ZLFW update helper class
*/
class zlfwHelperUpdate extends AppHelper {

	/*
		Function: __construct
			Class Constructor.
	*/
	public function __construct($app) {
		parent::__construct($app);

		// set db shortcut
		$this->db = $app->database;
	}

	/**
	 * Return required update versions.
	 *
	 * @param string $version The version for which to get required updates
	 * @param string $path The path where the updates are stored
	 *
	 * @return array versions of required updates
	 */
	public function getRequiredUpdates($version, $path)
	{
		if ($files = $this->app->path->files($path, false, '/^\d+.*\.php$/')) {
			$files = array_map(create_function('$file', 'return basename($file, ".php");'), array_filter($files, create_function('$file', 'return version_compare("'.$version.'", basename($file, ".php")) < 0;')));
			usort($files, create_function('$a, $b', 'return version_compare($a, $b);'));
		}

		return $files;
	}

	/**
	 * Performs the next update.
	 *
	 * @param string $current_v The current version of the installed extension
	 * @param string $path The path where the updates are stored
	 *
	 * @return bool Result of the update
	 */
	public function run($current_v, $path)
	{
		// get relative path
		$path = $this->app->path->relative($path);

		// get required updates
		$updates = $this->getRequiredUpdates($current_v, "root:$path");

		// run each of them
		foreach ($updates as $version) {
			if ((version_compare($version, $current_v) > 0)) {
				$class = 'Update'.str_replace('.', '', $version);
				$this->app->loader->register($class, "root:$path/$version.php");

				if (class_exists($class)) {
					
					// make sure class implemnts zlUpdate interface
					$r = new ReflectionClass($class);
					if ($r->isSubclassOf('zlUpdate') && !$r->isAbstract()) {

						try {

							// run the update
							$r->newInstance($this->app)->run();
						} catch (Exception $e) {

							Jerror::raiseWarning(null, "Error during update! ($e)");
							return false;
						}
					}
				}
			}
		}

		return true;
	}
}

/**
 * ZL Update class
 */
abstract class zlUpdate {

	/*
		Variable: app
			App instance.
	*/
	public $app;

	/*
		Variable: db
			Database instance.
	*/
	public $db;

	/*
		Function: __construct
			Class Constructor.
	*/
	public function __construct($app) {

		// set App instance
		$this->app = $app;

		// set DB instance
		$this->db = $app->database;
	}

	/**
	 * Check if column exists in specified table
	 */
	function column_exists($column, $table)
	{
		$exists = false;
		$query = "SHOW columns FROM `{$table}`";
		$columns = $this->db->queryAssocList($query);

		while (list ($key, $val) = each ($columns)) {
			if($val['Field'] == $column) {
				$exists = true;
				break;
			}
		}

		return $exists;
	}

	/**
	 * Removes obsolete files and folders
	 */
	public function removeObsolete()
	{
		// Remove files
		if(!empty($this->_obsolete['files'])) foreach($this->_obsolete['files'] as $file) {
			$f = JPATH_ROOT.'/'.$file;
			if(!JFile::exists($f)) continue;
			JFile::delete($f);
		}

		// Remove folders
		if(!empty($this->_obsolete['folders'])) foreach($this->_obsolete['folders'] as $folder) {
			$f = JPATH_ROOT.'/'.$folder;
			if(!JFolder::exists($f)) continue;
			JFolder::delete($f);
		}
	}

	/**
	 * Performs the update.
	 *
	 * @return boolean true if updated successful
	 */
	abstract public function run();
}