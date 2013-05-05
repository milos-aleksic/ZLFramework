<?php
/**
* @package		ZL Framework
* @author    	JOOlanders, SL http://www.zoolanders.com
* @copyright 	Copyright (C) JOOlanders, SL
* @license   	http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

/**
 * Base class to deal with file storage using abstraction layers
 */
class ZLStorage {

	/**
	 * A reference to the global App object
	 *
	 * @var App
	 */
	public $app;

	/**
	 * @var Adapter
	 */
	protected $adapter = null;

	/**
	 * @var Adapters
	 */
	protected $adapters = array();

	/**
	 * Class Constructor
	 */
	public function __construct($adapter, $options) {

		// init vars
		$this->app = App::getInstance('zoo');

		// load the adapter
		$this->adapter = $this->getAdapterInstance($adapter, $options);
	}

	/**
	 * Returns the current Adapter
	 */
	public function getAdapter() {
		return $this->adapter;
	}

	/**
	 * Returns and init an Adapter instance
	 */
	public function getAdapterInstance($type, $options = array()) {
		$types = $this->getAdapters();

		if (!in_array($type, $types)) {
			return false;
		}

		// register the Adapter class
		$classname = 'ZLStorageAdapter' . ucfirst($type);
		$this->app->loader->register($classname, 'classes:zlstorage/adapter/' . $type . '.php');

		// return the adapter
		return new $classname($options);
	}

	/**
	 * Writes a file to the filesystem selected
	 * 
	 * @param  string  $file      The filename (or path)
	 * @param  mixed   $content   The content to write
	 * @param  boolean $overwrite If we have to overwrite the file (default: true)
	 * 
	 * @return boolean             The success of the operation
	 */
	public function write($file, $content, $overwrite = true) {
		return $this->adapter->write($file, $content, $overwrite);
	}

	/**
	 * Reads a file content from the filesystem selected
	 * 
	 * @param  string  $file      The filename (or path)
	 * 
	 * @return mixed             The content of the file
	 */
	public function read($file) {
		return $this->adapter->read($file);
	}

	/**
	 * Deletes a file from the filesystem selected
	 * 
	 * @param  string  $file      The filename (or path)
	 * 
	 * @return boolean             The success of the operation
	 */
	public function delete($file) {
		return $this->adapter->delete($file);
	}

	/**
	 * Check if a file exists in the filesystem selected
	 * 
	 * @param  string  $file      The filename (or path)
	 * 
	 * @return boolean             The success of the operation
	 */
	public function exists($file) {
		return $this->adapter->exists($file);
	}

	/**
	 * Moves an uploaded file to a destination folder
	 * 
	 * @param   string   $file         The name of the php (temporary) uploaded file
	 * @param   string   $dest         The path (including filename) to move the uploaded file to
	 * 
	 * @return boolean             The success of the operation
	 */
	public function upload($file, $dest) {
		return $this->adapter->upload($file, $dest);
	}

	/**
	 * Get a Folder/File tree list
	 * 
	 * @param   string   $path         The path to the root folder
	 * 
	 * @return boolean             The success of the operation
	 */
	public function getTree($root, $legalExt) {
		return $this->adapter->getTree($root, $legalExt);
	}

	/**
	 * Populate the Adapters list
	 * 
	 * @return Adapters array        The populated array of valid Adapters
	 */
	public function getAdapters() {
		
		if (!$this->adapters) {
			foreach ($this->app->path->files('classes:zlstorage/adapter', false, '/^.*(php)$/i') as $adapter) {
				$this->adapters[] = substr($adapter, 0, strrpos($adapter, '.'));
			}
		}

		return $this->adapters;
	}
}

interface ZLStorageAdapter {

	/**
	 * Writes a file to the filesystem selected
	 * 
	 * @param  string  $file      The filename (or path)
	 * @param  mixed   $content   The content to write
	 * @param  boolean $overwrite If we have to overwrite the file (default: true)
	 * 
	 * @return boolean             The success of the operation
	 */
	public function write($file, $content, $overwrite = true);

	/**
	 * Reads a file content from the filesystem selected
	 * 
	 * @param  string  $file      The filename (or path)
	 * 
	 * @return mixed             The content of the file
	 */
	public function read($file);

	/**
	 * Deletes a file from the filesystem selected
	 * 
	 * @param  string  $file      The filename (or path)
	 * 
	 * @return boolean             The success of the operation
	 */
	public function delete($file);

	/**
	 * Check if a file exists in the filesystem selected
	 * 
	 * @param  string  $file      The filename (or path)
	 * 
	 * @return boolean             The success of the operation
	 */
	public function exists($file);	
}

/**
 * This class should contain the common methods between the adapters
 */
abstract class ZLStorageAdapterBase {

}