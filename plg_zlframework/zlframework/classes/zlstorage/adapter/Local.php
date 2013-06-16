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
	 * Creates a folder
	 *
 	 * @param string $path The path to the new object
	 *
	 * @return boolean The success of the operation
	 */
	public function createFolder($path)
	{
		$result = false;
		$path = $this->app->path->path('root:' . dirname($path)) . '/' . basename($path); // workaround as the path does not exist yet

		// clean paths if valid
		$path = $path ? JPath::clean($path) : false;

		if (JFolder::exists($path)) {
			$this->setError('The folder already exists.');
			return true;
		} else {
			$result = JFolder::create($path);
		}

		// if something went wrong, report
		if ($result !== true) {
			$result = false;
			$this->setError('Something went wrong, the task was not performed.');
		}

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
		if ($result !== true) {
			$result = false;
			$this->setError('Something went wrong, the task was not performed.');
		}

		return $result;
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
	 * Deletes an asset from the filesystem selected
	 * 
	 * @param string $path The path to the asset
	 * 
	 * @return boolean The success of the operation
	 */
	public function delete($path)
	{
		$result = false;
		$fullpath = $this->app->path->path("root:$path");

		if (!$fullpath || !is_readable($fullpath)) {
			$this->setError('Permission denied or object not found.');
			return $result;
		} else if (is_file($fullpath)) {
			$result = JFile::delete($fullpath);
		} else if (is_dir($fullpath)) {
			$result = JFolder::delete($fullpath);
		}

		// if something went wrong, report
		if ($result !== true) {
			$result = false;
			$this->setError('Something went wrong, the task was not performed.');
		}

		return $result;
	}

	/**
	 * Get a Folder/File tree list
	 * 
	 * @param string $root The path to the root folder
	 * @param string $legalExt The allowed file extensions comma separated
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
			$rows[] = $this->getObjectInfo($root . '/' . $dir);
		}

		// files
		foreach ($this->app->path->files("root:$root", false, '/^.*('.$legalExt.')$/i') as $file) {
			$rows[] = $this->getObjectInfo($root . '/' . $file);
		}

		// return list
		return compact('root', 'rows');
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
		$result = false;
		$obj = null;
		$fullpath = $this->app->path->path("root:$path");

		if (!$fullpath || !is_readable($fullpath)) {
			$this->setError('Permission denied or object not found.');
			return $result;

		// if file
		} else if (is_file($fullpath)) {

			$obj = array('type' => 'file');
			$obj['name'] = basename($path);
			$obj['path'] = $path;
			$obj['ext']  = JFile::getExt($obj['name']);
			$obj['basename'] = basename($obj['name'], '.' . $obj['ext']);
			$obj['content_type'] = $this->app->zlfw->filesystem->getContentType($obj['name']);
			$obj['size']['value'] = $this->app->zlfw->filesystem->getSourceSize($path, false);
			$obj['size']['display'] = $this->app->zlfw->filesystem->formatFilesize($obj['size']['value'], 'KB');

			// if image
			if ($imageinfo = getimagesize($fullpath)) {
				$obj['resolution'] = $imageinfo[0].'x'.$imageinfo[1].'px';
			}

			// all ok
			$result = true;

		// if folder
		} else if (is_dir($fullpath)){

			$obj = array('type' => 'folder');
			$obj['name'] = basename($path);
			$obj['path'] = $path;
			$obj['basename'] = $obj['name'];
			$obj['size']['value'] = $this->app->zlfw->filesystem->getSourceSize($path, false);
			$obj['size']['display'] = $this->app->zlfw->filesystem->formatFilesize($obj['size']['value'], 'KB');
			// $obj['size'] = count($this->app->path->files('root:'.$path, false, '/^.*('.$this->getLegalExtensions().')$/i'))

			// all ok
			$result = true;
		}

		// if something went wrong, report
		if ($result !== true) {
			$this->setError('Something went wrong, the task was not performed.');

			return false;
		}

		return $obj;
	}

	/**
	 * Get valid resources, a list of readable files from a folder or individually
	 * 
	 * @param string $path The object path
	 * @param string $legalExt The allowed file extensions comma separated
	 * 
	 * @return array The resources
	 */
	public function getValidResources($path, $legalExt)
	{
		$result = false;
		$resources = array();
		$fullpath = $this->app->path->path("root:$path");

		if (!$fullpath || !is_readable($fullpath)) {
			$this->setError('Permission denied or object not found.');
			return $result;

		// if file
		} else if (is_file($fullpath)) {

			$resources[] = $path;

			// all ok
			$result = true;

		// if folder
		} else if (is_dir($fullpath)){
			
			// retrieve all valid files
			foreach ($this->app->path->files("root:$path", false, '/^.*('.$legalExt.')$/i') as $filename) {
				$filepath = "$fullpath/$filename";
				if (is_readable($filepath) && is_file($filepath)) {
					$resources[] = "$path/$filename"; // add relative path
				}
			}

			// all ok
			$result = true;
		}

		// if something went wrong, report
		if ($result !== true) {
			$this->setError('Something went wrong, the task was not performed.');

			return false;
		}

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
		$this->app->path->url("root:$path");
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