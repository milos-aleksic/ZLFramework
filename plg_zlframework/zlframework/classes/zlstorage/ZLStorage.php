<?php
/**
* @package		ZL Framework
* @author    	JOOlanders, SL http://www.zoolanders.com
* @copyright 	Copyright (C) JOOlanders, SL
* @license   	http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

// register necesary classes
App::getInstance('zoo')->loader->register('ZLErrorHandlerAbstractObject', 'classes:errorhandler.php');

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
	public function __construct($adapter, $options = array()) {

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
	 * Check if a file exists in the filesystem selected
	 * 
	 * @param string $file The filename (or path)
	 * 
	 * @return boolean The success of the operation
	 */
	public function exists($file) {
		return $this->adapter->exists($file);
	}

	/**
	 * Writes a file to the filesystem selected
	 * 
	 * @param string $file The filename (or path)
	 * @param mixed $content The content to write
	 * 
	 * @return boolean The success of the operation
	 */
	public function write($file, $content, $overwrite = true) {
		return $this->adapter->write($file, $content, $overwrite);
	}

	/**
	 * Reads a file content from the filesystem selected
	 * 
	 * @param string file The filename (or path)
	 * 
	 * @return mixed The content of the file
	 */
	public function read($file) {
		return $this->adapter->read($file);
	}

	/**
	 * Creates a folder
	 *
 	 * @param string $path The path to the new object
	 *
	 * @return boolean The success of the operation
	 */
	public function createFolder($path)
	{
		return $this->adapter->createFolder($path);
	}

	/**
	 * Moves a file
	 *
	 * @param string $src The path to the source file
	 * @param string $dest The path to the destination file
	 *
	 * @return boolean The success of the operation
	 */
	public function move($src, $dest){
		return $this->adapter->move($src, $dest);
	}

	/**
	 * Moves an uploaded file to a destination folder
	 * 
	 * @param string $file The name of the php (temporary) uploaded file
	 * @param string $dest The path (including filename) to move the uploaded file to
	 * 
	 * @return boolean The success of the operation
	 */
	public function upload($file, $dest) {
		return $this->adapter->upload($file, $dest);
	}

	/**
	 * Deletes an asset from the filesystem selected
	 * 
	 * @param string $path The path to the asset
	 * 
	 * @return boolean The success of the operation
	 */
	public function delete($path)
	{
		// workaround if object passed instead
		if (is_object($path)) $path = $path->getPathname();

		return $this->adapter->delete($path);
	}

	/**
	 * Get a Folder/File tree list
	 * 
	 * @param string $root The path to the root folder
	 * @param string $legalExt The allowed file extensions comma separated
	 * 
	 * @return boolean The success of the operation
	 */
	public function getTree($root, $legalExt = '') {
		return $this->adapter->getTree($root, $legalExt);
	}

	/**
	 * Get Object related information
	 * 
	 * @param string $path The object path
	 * 
	 * @return array The object info
	 */
	public function getObjectInfo($path)
	{
		// workaround if object passed instead
		if (is_object($path)) $path = $path->getPathname();

		return $this->adapter->getObjectInfo($path);
	}

	/**
	 * Get valid resources, a list of readable files from a folder or individually
	 * 
	 * @param string $path The object path
	 * @param string $legalExt The allowed file extensions comma separated
	 * 
	 * @return array The resources
	 */
	public function getValidResources($path, $legalExt = '')
	{
		// workaround if object passed instead
		if (is_object($path)) $path = $path->getPathname();

		return $this->adapter->getValidResources($path, $legalExt);
	}

	/**
	 * Get the absolute url to a resource
	 *
	 * @param string $path The object path
	 *
	 * @return string The absolute url
	 */
	public function getAbsoluteURL($path)
	{
		// workaround if object passed instead
		if (is_object($path)) $path = $path->getPathname();

		return $this->adapter->getAbsoluteURL($path);
	}

	/**
	 * Add an error message
	 * 
	 * @param string $error The error message
	 */
	public function setError($error) {
		return $this->adapter->setError($error);
	}

	/**
	 * Get the reported errors
	 * 
	 * @param string $root The path to the root folder
	 * 
	 * @return array A list of errors
	 */
	public function getErrors() {
		return $this->adapter->getErrors();
	}

	/**
	 * Get the reported warnings
	 * 
	 * @param string $root The path to the root folder
	 * 
	 * @return array A list of warnings
	 */
	public function getWarnings() {
		return $this->adapter->getWarnings();
	}

	/**
	 * Populate the Adapters list
	 * 
	 * @return Adapters array The populated array of valid Adapters
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
	 * Check if a file exists in the filesystem selected
	 * 
	 * @param string $file The filename (or path)
	 * 
	 * @return boolean The success of the operation
	 */
	public function exists($file);

	/**
	 * Writes a file to the filesystem selected
	 * 
	 * @param string $file The filename (or path)
	 * @param mixed $content The content to write
	 * @param boolean $overwrite If we have to overwrite the file (default: true)
	 * 
	 * @return boolean The success of the operation
	 */
	public function write($file, $content, $overwrite = true);

	/**
	 * Reads a file content from the filesystem selected
	 * 
	 * @param string $file The filename (or path)
	 * 
	 * @return mixed The content of the file
	 */
	public function read($file);

	/**
	 * Deletes a file from the filesystem selected
	 * 
	 * @param string $file The filename (or path)
	 * 
	 * @return boolean The success of the operation
	 */
	public function move($src, $dest);

	/**
	 * Deletes a file from the filesystem selected
	 * 
	 * @param string $file The filename (or path)
	 * 
	 * @return boolean The success of the operation
	 */
	public function delete($file);
}

/**
 * This class should contain the common methods between the adapters
 */
abstract class ZLStorageAdapterBase extends ZLErrorHandlerAbstractObject {}