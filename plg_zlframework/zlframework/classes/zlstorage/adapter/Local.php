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

/**
 * ZLStorage Local Adapter class
 */
class ZLStorageAdapterLocal extends ZLStorageAdapterBase implements ZLStorageAdapter {

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
	 * Writes a file to the filesystem selected
	 * 
	 * @param  string  $file      The filename (or path)
	 * @param  mixed   $content   The content to write
	 * 
	 * @return boolean             The success of the operation
	 */
	public function write($file, $content, $overwrite = true){
		return JFile::write($this->app->path->path($file, $content));
	}

	/**
	 * Reads a file content from the filesystem selected
	 * 
	 * @param  string  $file      The filename (or path)
	 */
	public function read($file) {
		return JFile::read($this->app->path->path($file));
	}

	/**
	 * Deletes a file from the filesystem selected
	 * 
	 * @param  string  $file      The filename (or path)
	 * 
	 * @return boolean             The success of the operation
	 */
	public function delete($file) {
		return JFile::delete($this->app->path->path($file));
	}

	/**
	 * Check if a file exists in the filesystem selected
	 * 
	 * @param  string  $file      The filename (or path)
	 */
	public function exists($file) {
		return JFile::exists($this->app->path->path($file));
	}

	/**
	 * Moves an uploaded file to a destination folder
	 * 
	 * @param   string   $file         The name of the php (temporary) uploaded file
	 * @param   string   $dest         The path (including filename) to move the uploaded file to
	 */
	public function upload($file, $dest)
	{
		// init vars
		$basename 	= basename($file, '.' . JFile::getExt($file));
		$ext 		= strtolower(JFile::getExt($file));
		$targetDir  = dirname($dest);
		
		// construct filename
		$fileName = "{$basename}.{$ext}";

		// Make sure the fileName is unique
		if (JFile::exists($dest)) {
			$count = 1;
			while (JFile::exists("{$targetDir}/{$basename}_{$count}.{$ext}"))
				$count++;
		
			$fileName = "{$basename}_{$count}.{$ext}";
		}

		$dest = $targetDir . '/' . $fileName;

		return JFile::move($file, $dest);
	}

	/**
	 * Get a Folder/File tree list
	 * 
	 * @param   string   $root     The path to the root folder
	 * 
	 * @return boolean             The success of the operation
	 */
	public function getTree($root, $legalExt)
	{
		// init vars
		$root = $this->app->zlpath->getDirectory($root); // if empty, will return joomla image folder
		$rows = array();

		// dirs
		foreach ($this->app->path->dirs("root:$root") as $dir) {
			$row = array();
			$row['name'] = basename($dir);
			$row['type'] = 'folder';
			$row['path'] = $dir;
			$row['size']['value'] = $this->app->zlfilesystem->getSourceSize($row['path'], false);
			$row['size']['display'] = $this->app->zlfilesystem->formatFilesize($row['size']['value'], 'KB');

			$rows[] = $row;
		}

		// files
		foreach ($this->app->path->files("root:$root", false, '/^.*('.$legalExt.')$/i') as $file) {
			$row = array();
			$row['name'] = basename($file);
			$row['type'] = 'file';
			$row['path'] = $file;
			$row['size']['value'] = $this->app->zlfilesystem->getSourceSize($row['path'], false);
			$row['size']['display'] = $this->app->zlfilesystem->formatFilesize($row['size']['value'], 'KB');

			$rows[] = $row;
		}

		// return list
		return $rows;
	}
}