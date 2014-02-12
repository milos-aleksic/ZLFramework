<?php
/**
* @package		ZL Framework
* @author    	JOOlanders, SL http://www.zoolanders.com
* @copyright 	Copyright (C) JOOlanders, SL
* @license   	http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

// import library dependencies
jimport('joomla.filesystem.file');
jimport('joomla.filesystem.path');

/**
 * ZLStorage Local Adapter class
 */
class ZLStorageAdapterURI extends ZLStorageAdapterBase implements ZLStorageAdapter {

	/**
	 * A reference to the global App object
	 *
	 * @var App
	 */
	public $app;

	/**
	 * Class Constructor
	 */
	public function __construct() {

		// init vars
		$this->app = App::getInstance('zoo');
	}

	/**
	 * Check if a file exists in the filesystem selected
	 * 
	 * @param string $file The filename (or path)
	 * 
	 * @return boolean The success of the operation
	 */
	public function exists($file) {
		return false;
	}

	/**
	 * Writes a file to the filesystem selected
	 * 
	 * @param string $file The filename (or path)
	 * @param mixed $content The content to write
	 * 
	 * @return boolean The success of the operation
	 */
	public function write($file, $content, $overwrite = true){
		return false;
	}

	/**
	 * Reads a file content from the filesystem selected
	 * 
	 * @param string file The filename (or path)
	 * 
	 * @return mixed The content of the file
	 */
	public function read($file) {
		return false;
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
		return false;
	}

	/**
	 * Moves a file
	 *
 	 * @param string $src The path to the source file
	 * @param string $dest The path to the destination file
	 *
	 * @return boolean The success of the operation
	 */
	public function move($src, $dest)
	{
		return false;
	}

	/**
	 * Moves an uploaded file to a destination folder
	 * 
	 * @param string $file The name of the php (temporary) uploaded file
	 * @param string $dest The path (including filename) to move the uploaded file to
	 * 
	 * @return boolean The success of the operation
	 */
	public function upload($file, $dest)
	{
		return false;
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
		return false;
	}

	/**
	 * Get a Folder/File tree list
	 * 
	 * @param string $root The path to the root folder
	 * 
	 * @return boolean The success of the operation
	 */
	public function getTree($root, $legalExt)
	{
		return false;
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
		// make sure is file url
		if(!preg_match('/(\.[a-zA-Z0-9]{3,4}$)/', $path)) return false;

		$obj = array('type' => 'file');
		$obj['name'] = basename($path);
		$obj['path'] = $path;
		$obj['ext']  = JFile::getExt($obj['name']);
		$obj['basename'] = basename($obj['name'], '.' . $obj['ext']);
		$obj['content_type'] = $this->app->zlfw->filesystem->getContentType($obj['name']);
		// $obj['size']['value'] = $this->app->zlfw->filesystem->getSourceSize($path, false);
		// $obj['size']['display'] = $this->app->zlfw->filesystem->formatFilesize($obj['size']['value'], 'KB');

		return $obj;
	}

	/**
	 * Get valid resources, a list of readable files from a folder or individually
	 * 
	 * @param string $path The object path
	 * 
	 * @return array The resources
	 */
	public function getValidResources($path, $legalExt)
	{
		$resources = array();
		$resources[] = $path;

		return $resources;
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
		return $path;
	}
}