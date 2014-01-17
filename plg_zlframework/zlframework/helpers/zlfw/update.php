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

	// vars
	public $ext_id;

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
	 * @return array response
	 */
	public function run($ext_id, $path)
	{
		// vars
		$this->ext_id = $ext_id;

		// get current version
		$current_version = $this->getVersion($ext_id);

		// get relative path
		$path = $this->app->path->relative($path);

		// get requires updates
		$updates = $this->getRequiredUpdates($current_version, "root:$path");

		// run each of them
		foreach ($updates as $version) {
			if ((version_compare($version, $current_version) > 0)) {
				$class = 'Update'.str_replace('.', '', $version);
				$this->app->loader->register($class, "root:$path/$version.php");

				if (class_exists($class)) {
					
					// make sure class implemnts zlUpdate interface
					$r = new ReflectionClass($class);
					if ($r->isSubclassOf('zlUpdate') && !$r->isAbstract()) {

						try {

							// run the update
							$r->newInstance()->run();
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

	/**
	 * Gets the current version from versions table.
	 */
	public function getVersion() {
		// set query
		$this->db->setQuery("SELECT `version_id` FROM `#__schemas` WHERE `extension_id` = '{$this->ext_id}'");

		// load and return
		if($obj = $this->db->loadObject()) {
			return $obj->version_id;
		}
	}

	/**
	 * Writes the current version in schemas table.
	 *
	 * @param string $version
	 */
	public function setVersion($version)
	{
		// insert row
		$this->db->setQuery("SELECT * FROM `#__schemas` WHERE `extension_id` = '{$this->ext_id}'");
		if (!$this->db->loadObject()) {
			$query = $this->db->getQuery(true);
			$query->clear()
				->insert($this->db->quoteName('#__schemas'))
				->columns(array($this->db->quoteName('extension_id'), $this->db->quoteName('version_id')))
				->values($this->ext_id . ', ' . $this->db->quote($version));
			$this->db->setQuery($query)->execute();

		// or update if exists
		} else {
			$query = $this->db->getQuery(true);
			$query->clear()
				->update($this->db->quoteName('#__schemas'))
				->set($this->db->quoteName('version_id') . ' = ' . $this->db->quote($version))
				->where($this->db->quoteName('extension_id').' = '. $this->ext_id);
			$this->db->setQuery($query)->execute();
		}
	}

	/**
	 * Removes obsolete files and folders
	 */
	public function removeObsolete($obsolete)
	{
		// Remove files
		if(!empty($obsolete['files'])) foreach($obsolete['files'] as $file) {
			$f = JPATH_ROOT.'/'.$file;
			if(!JFile::exists($f)) continue;
			JFile::delete($f);
		}

		// Remove folders
		if(!empty($obsolete['folders'])) foreach($obsolete['folders'] as $folder) {
			$f = JPATH_ROOT.'/'.$folder;
			if(!JFolder::exists($f)) continue;
			JFolder::delete($f);
		}
	}

}

/**
 * Update interface
 */
interface zlUpdate {

	/**
	 * Performs the update.
	 *
	 * @return boolean true if updated successful
	 */
	public function run();
}