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
jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.path');

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
	 * @param string $file The filename (or path)
	 * @param mixed $content The content to write
	 * 
	 * @return boolean The success of the operation
	 */
	public function write($file, $content, $overwrite = true){
		return JFile::write($this->app->path->path($file, $content));
	}

	/**
	 * Reads a file content from the filesystem selected
	 * 
	 * @param string file The filename (or path)
	 * 
	 * @return mixed The content of the file
	 */
	public function read($file) {
		return JFile::read($this->app->path->path($file));
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
		$result = false;
		$path = $this->app->path->path('root:' . $path);

		if (!is_readable($path)) {
			$this->setError('Permission denied or object not found.');
			return $result;
		} else if (is_file($path)) {
			$result = JFile::delete($path);
		} else if (is_dir($path)) {
			$result = JFolder::delete($path);
		}

		// if something went wrong, report
		if (!$result) $this->setError('Something went wrong, the task was not performed.');

		return $result;
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
		$result = false;
		$src = $this->app->path->path('root:' . $src);
		$dest = dirname($src) . '/' . basename($dest); // workaround as the path does not exist yet

		// clean paths if valid
		$src = $src ? JPath::clean($src) : false;
		$dest = $dest ? JPath::clean($dest) : false;

		if (!is_readable($src)) {
			$this->setError('Permission denied or object not found.');
			return $result;
		} else if (is_file($src)) {
			$result = JFile::move($src, $dest);
		} else if (is_dir($src)) {
			$result = JFolder::move($src, $dest);
		}

		// if something went wrong, report
		if (!$result) $this->setError('Something went wrong, the task was not performed.');

		return $result;
	}

	/**
	 * Check if a file exists in the filesystem selected
	 * 
	 * @param string $file The filename (or path)
	 * 
	 * @return boolean The success of the operation
	 */
	public function exists($file) {
		return JFile::exists($this->app->path->path($file));
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

		if (JFile::move($file, $dest)){
			return $fileName;
		} else return false;
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
		// init vars
		$root = $this->app->zlpath->getDirectory($root); // if empty, will return joomla image folder
		$rows = array();

		// dirs
		foreach ($this->app->path->dirs("root:$root") as $dir) {
			$row = array();
			$row['name'] = basename($dir);
			$row['type'] = 'folder';
			$row['path'] = $dir;
			// $row['size']['value'] = $this->getDirectorySize($root . '/' . $row['path']);
			// $row['size']['display'] = $this->app->zlfilesystem->formatFilesize($row['size']['value'], 'KB');

			// details
			$row['details'] = array();
			$row['details']['Name'] = $row['name'];
			// $row['details']['Size'] = $row['size']['display'];

			$rows[] = $row;
		}

		// files
		foreach ($this->app->path->files("root:$root", false, '/^.*('.$legalExt.')$/i') as $file) {
			$row = array();
			$row['name'] = basename($file);
			$row['type'] = 'file';
			$row['path'] = $file;
			$row['size']['value'] = $this->app->zlfilesystem->getSourceSize($root . '/' . $row['path'], false);
			$row['size']['display'] = $this->app->zlfilesystem->formatFilesize($row['size']['value'], 'KB');

			// details
			$row['details'] = array();
			$row['details']['Name'] = basename($row['name'], '.' . JFile::getExt($row['name']));
			$row['details']['Type'] = $this->app->zlfilesystem->getContentType($row['name']);
			$row['details']['Size'] = $row['size']['display'];

			$rows[] = $row;
		}

		// return list
		return compact('root', 'rows');
	}

	/**
	 * Get the given directory size
	 * 
	 * @param string $root The path to the root folder
	 * @param string $recursive If the search should be recursive (default: true)
	 * 
	 * @return boolean The success of the operation
	 */
	public function getDirectorySize($root, $recursive = true)
	{
		// get size of root
		$total_size = $this->app->zlfilesystem->getSourceSize($root, false);

		// get size of subdirectories
		if ($recursive) {
			$dirs = $this->app->zlfilesystem->readDirectory($this->app->path->path('root:' . $root));
			foreach ($dirs as $dir) {
				$total_size += $this->app->zlfilesystem->getSourceSize($root . '/' . $dir, false);
			}
		}

		return $total_size;
	}
}