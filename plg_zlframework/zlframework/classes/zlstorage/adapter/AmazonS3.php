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
App::getInstance('zoo')->loader->register('AEUtilAmazons3', 'classes:amazons3.php');

// import library dependencies
jimport('joomla.filesystem.file');

/**
 * ZLStorage Amazon S3 Adapter class
 */
class ZLStorageAdapterAmazonS3 extends ZLStorageAdapterBase implements ZLStorageAdapter {

	/**
	 * A reference to the global App object
	 *
	 * @var App
	 */
	public $app;

	/**
	 * A reference to the Amazon S3 Utility class
	 *
	 * @var s3
	 */
	public $s3;

	/**
	 * A reference to the Amazon S3 Bucket
	 *
	 * @var Bucket
	 */
	public $bucket;

	/**
	 * Class Constructor
	 */
	public function __construct($options) {

		// init vars
		$this->app = App::getInstance('zoo');

		// init S3 Utility
		$this->s3 = AEUtilAmazons3::getInstance($options['accesskey'], $options['secretkey'], false);

		// store bucket reference
		$this->bucket = $options['bucket'];
	}

	/**
	 * Check if a file exists in the filesystem selected
	 * 
	 * @param string $file The filename (or path)
	 * 
	 * @return boolean The success of the operation
	 */
	public function exists($file) {}

	/**
	 * Writes a file to the filesystem selected
	 * 
	 * @param string $file The filename (or path)
	 * @param mixed $content The content to write
	 * 
	 * @return boolean The success of the operation
	 */
	public function write($file, $content, $overwrite = true){
	}

	/**
	 * Reads a file content from the filesystem selected
	 * 
	 * @param string file The filename (or path)
	 * 
	 * @return mixed The content of the file
	 */
	public function read($file) {}

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

		// create
		$result = $this->s3->putObject(
			'',
			$this->bucket,
			$path,
			AEUtilAmazons3::ACL_BUCKET_OWNER_FULL_CONTROL,
			array(),
			array()
		);

		// propagate S3 object errors to storage object
		$this->s3->propagateToObject($this);
		
		// if something went wrong, report
		if ($result !== true) {
			$result = false;
			$this->setError(JText::_('PLG_ZLFRAMEWORK_STRG_ERR_SOMETHING_WENT_WRONG'));
		}

		return $result;
	}

	/**
	 * Moves an object
	 *
 	 * @param string $src The path to the source file
	 * @param string $dest The path to the destination file
	 *
	 * @return boolean The success of the operation
	 */
	public function move($src, $dest)
	{
		$result = false;

		// if is folder
		if (preg_match('/\/$/', $src)) { // they have a slash at the end

			// check if the folder is empty
			$objects = $this->s3->getBucket($this->bucket, $src, null, 20);

			// unset the folder being deleted
			unset($objects[$src]);

			// if they are objects in the folder, cancel
			if (!empty($objects)) {
				$this->setError(JText::_('PLG_ZLFRAMEWORK_STRG_S3_MUST_EMPTY_FOLDER'));
				return false;
			}
		}

		// Copy the object
		$result = $this->s3->copyObject(
			$this->bucket,
			$src,
			$this->bucket,
			$dest,
			AEUtilAmazons3::ACL_BUCKET_OWNER_FULL_CONTROL,
			array(),
			array()
		);

		// if copy success delete the original object
		if ($result === true) {
			$this->delete($src);
		}

		// propagate S3 object errors to storage object
		$this->s3->propagateToObject($this);

		// if something went wrong, report
		if ($result !== true) {
			$result = false;
			$this->setError(JText::_('PLG_ZLFRAMEWORK_STRG_ERR_SOMETHING_WENT_WRONG'));
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
		$rrs = false;
		$absolute_filename = $this->app->path->path('zlfw:changelog.txt');
		// Legacy single part uploads
		$result = $this->s3->putObject(
			AEUtilAmazons3::inputFile( $absolute_filename, false ),		// File to read from
			'milcom.testing',													// Bucket name
			'changelog.txt',													// Remote relative filename, including directory
			AEUtilAmazons3::ACL_BUCKET_OWNER_FULL_CONTROL,				// ACL (bucket owner has full control, file owner gets full control)
			array(),													// Meta headers
			// Other request headers
			array(
				// Amazon storage class (support for RRS - Reduced Redundancy Storage)
				'x-amz-storage-class'	=> $rrs ? 'REDUCED_REDUNDANCY' : 'STANDARD'
			)
		);

		// $this->s3->setError('You have not set up your Amazon S3 Access Key');
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

		// if is folder
		if (preg_match('/\/$/', $path)) { // they have a slash at the end

			// check if the folder is empty
			$objects = $this->s3->getBucket($this->bucket, $path, null, 20);

			// unset the folder being deleted
			unset($objects[$path]);

			// if they are objects in the folder, cancel
			if (!empty($objects)) {
				$this->setError(JText::_('PLG_ZLFRAMEWORK_STRG_S3_MUST_EMPTY_FOLDER'));
				return false;
			}
		}

		// delete the object
		$result = $this->s3->deleteObject(
			$this->bucket,
			$path
		);

		// propagate S3 object errors to storage object
		$this->s3->propagateToObject($this);

		// if something went wrong, report
		if ($result !== true) {
			$result = false;
			$this->setError(JText::_('PLG_ZLFRAMEWORK_STRG_ERR_SOMETHING_WENT_WRONG'));
		}

		return $result;
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
		$rows = array();
		$prefix = $root ? $root . '/' : '';

		// get range of objects
		$objects = $this->s3->getBucket($this->bucket, $prefix, null, null, '/', true);

		// must be array
		settype($objects, 'array');

		// folders
		foreach ($objects as $name => $obj) 
		{
			// skip root folder
			if(!isset($obj['prefix']) && $obj['size'] == 0) {
				unset($objects[$name]);
				continue;
			}

			// if folder
			if(isset($obj['prefix'])) {
				$row = array('type' => 'folder');
				$row['name'] = basename($name);
				$row['path'] = $obj['prefix'];
				$row['basename'] = $row['name'];

				$rows[] = $row;

				unset($objects[$name]);
			}
		}

		// files, only left
		foreach ($objects as $name => $obj) 
		{
			$row = array('type' => 'file');
			$row['name'] = basename($name);
			$row['path'] = $name;
			$row['ext'] = JFile::getExt($row['name']);
			$row['basename'] = basename($row['name'], '.' . $row['ext']);
			$row['content_type'] = $this->app->zlfw->filesystem->getContentType($row['name']);
			$row['size']['value'] = $this->app->zlfw->filesystem->returnBytes($obj['size']);
			$row['size']['display'] = $this->app->zlfw->filesystem->formatFilesize($row['size']['value'], 'KB');

			$rows[] = $row;
		}

		// propagate S3 object errors to storage object
		$this->s3->propagateToObject($this);
		
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

		// check if path is set
		if (!$path) return false;

		// get info from s3
		$result = $this->s3->getObjectInfo($this->bucket, $path);

		// propagate S3 object errors to storage object
		$this->s3->propagateToObject($this);

		// if something went wrong, report
		if ($result == FALSE) {
			$this->setError(JText::_('PLG_ZLFRAMEWORK_STRG_ERR_SOMETHING_WENT_WRONG'));

			return false;
		}

		// prepare object
		$obj = array();
		$obj['name'] = basename($path);
		$obj['path'] = $path;
		$obj['ext']  = JFile::getExt($obj['name']);
		$obj['basename'] = basename($obj['name'], '.' . $obj['ext']);
		$obj['content_type'] = $this->app->zlfw->filesystem->getContentType($obj['name']);
		$obj['size']['value'] = $this->app->zlfw->filesystem->returnBytes($result['size']);
		$obj['size']['display'] = $this->app->zlfw->filesystem->formatFilesize($obj['size']['value'], 'KB');

		// 'preview'	=> $this->s3->getAuthenticatedURL($this->bucket, $path, 900);

		// set the object type
		if (preg_match('/\/$/', $path)) { // is folder, they have a slash at the end
			$obj['type'] = 'folder';
		} else {
			$obj['type'] = 'file';
		}

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
		// basic check
		if (empty($path)) return false;

		// init vars
		$resources = array();

		// if folder
		if (preg_match('/\.+[a-zA-Z0-9]+$/', $path) == 0) { // folders have no extension

			// they do need a slash at the end, adding if missing
			if (preg_match('/\/$/', $path) == 0) $path = $path . '/';

			// get range of objects
			$objects = $this->s3->getBucket($this->bucket, $path, null, null, '/', true);

			// folders
			foreach ($objects as $name => $obj) 
			{
				// skip root folder
				if (!isset($obj['prefix']) && $obj['size'] == 0) continue;
				
				// skip folders
				if (isset($obj['prefix'])) continue;

				// add resource
				$resources[] = $obj['name'];
			}

		// if file
		} else if ($obj = $this->getObjectInfo($path)){
			$resources[] = $obj['path'];
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
		return $this->s3->getAuthenticatedURL($this->bucket, $path, 3600);
	}
}