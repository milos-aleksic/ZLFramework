<?php
/**
* @package		ZL Framework
* @author    	JOOlanders, SL http://www.zoolanders.com
* @copyright 	Copyright (C) JOOlanders, SL
* @license   	http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

// register ElementRepeatablePro class
App::getInstance('zoo')->loader->register('ElementRepeatablePro', 'elements:repeatablepro/repeatablepro.php');

// load libraries
jimport('joomla.filesystem.file');

/*
	Class: ElementFilesPro
		The files pro element class
*/
abstract class ElementFilesPro extends ElementRepeatablePro {

	protected $_extensions = '';
	protected $_s3;
	protected $_jfile_path;

	/* this file INDEX - render, edit, file manager, submissions */

	/*
	   Function: Constructor
	*/
	public function __construct() {

		// call parent constructor
		parent::__construct();

		// set defaults
		$params = JComponentHelper::getParams('com_media');
		$this->config->set('files', array('_source_dir' => $params->get('file_path'), '_extensions' => $this->_extensions, '_max_upload_size' => '1024'));
		
		// set joomla file path
		$this->_joomla_file_path = $params->get('file_path') ? $params->get('file_path') : 'images';
	}

	/*
		Function: get - IMPORTANT TO KEEP DATA COMPATIBILITY WITH ZOO NO REPEATABLE ELEMENTS
			Gets the elements data.

		Returns:
			Mixed - the elements data
	*/
	public function get($key, $default = null) {
		if ($value = $this->_item->elements->find("{$this->identifier}.{$key}", $default)) {
			// workaround for the repeatable element transition
			return $value;
		} else {
			return parent::get($key, $default);
		}
	}
	
	/*
	   Function: initS3
		   Init the S3 class

	   Returns:
		   Class - S3 php class
	*/
	public function _S3()
	{
		if ($this->_s3 == null)
		{
			//include the S3 class
			if (!class_exists('S3')) require_once($this->app->path->path('elements:filespro/assets/s3/S3.php'));

			$awsaccesskey = trim($this->app->zlfw->decryptPassword($this->config->find('files._awsaccesskey')));
			$awssecretkey = trim($this->app->zlfw->decryptPassword($this->config->find('files._awssecretkey')));
			$s3 = new S3($awsaccesskey, $awssecretkey); // instantiate the class

			if(@$s3->listBuckets() && (@$constraint = $s3->getBucketLocation(trim($this->config->find('files._s3bucket')))) !== false)
			{
				$location = array('US' => 's3.amazonaws.com', 'us-west-1' => 's3-us-west-1.amazonaws.com', 'us-west-2' => 's3-us-west-2.amazonaws.com', 'eu-west-1' => 's3-eu-west-1.amazonaws.com', 'EU' => 's3-eu-west-1.amazonaws.com', 'ap-southeast-1' => 's3-ap-southeast-1.amazonaws.com', 'ap-northeast-1' => 's3-ap-northeast-1.amazonaws.com', 'sa-east-1' => 's3-sa-east-1.amazonaws.com');
				$this->_s3 = new S3($awsaccesskey, $awssecretkey, false, $location[$constraint]);
			}
		}
		return $this->_s3;
	}

	/*
		Function: isDownloadLimitReached
			Gets the download file size.

		Returns:
			String - Download file with KB/MB suffix
	*/
	function isDownloadLimitReached() {
		return ($limit = $this->get('download_limit')) && $this->get('hits', 0) >= $limit;
	}

/* -------------------------------------------------------------------------------------------------------------------------------------------------------- RENDER */

	/*
		Function: _hasValue
			Checks if the repeatables element's file is set

	   Parameters:
			$params - render parameter

		Returns:
			Boolean - true, on success
	*/
	protected function _hasValue($params = array())
	{
		$files = $this->getFiles($this->get('file'));
		return !empty($files);
	}

	/*
		Function: getRenderedValues
			render repeatable values

		Returns:
			array
	*/
	public function getRenderedValues($params=array(), $wk=false, $opts=array())
	{
		$opts['data_is_subarray'] = true;
		return parent::getRenderedValues($params, $wk, $opts);
	}

	/*
		Function: getFileObject
			Create and return the individual File object

		Returns:
			Array
	*/
	protected function getFileObject($file, $params)
	{
		$fileObj = array();
		$fileObj['path']				= $file;
		//$fileObj['file']				= 'root:'.$this->app->path->relative($file);
		$fileObj['ext'] 				= $this->getExtension($file);
		$fileObj['url']					= $this->getURL($file);
		$fileObj['name'] 				= basename($file, '.'.$fileObj['ext']);
		$fileObj['title'] 				= $this->get('title') ? $this->get('title') : $fileObj['name'];
		
		return $fileObj;
	}

	/*
		Function: getURL
			Get external files url

		Returns:
			Array
	*/
	protected function getURL($file)
	{
		if($this->config->find('files._s3', 0) || strpos($file, 'http') === 0) // S3 or external source
		{
			$bucket = $this->config->find('files._s3bucket');
			return $this->_S3()->getAuthenticatedURL($bucket, $file, 3600);
		}
		else
		{
			return JURI::base().$this->app->path->relative($file); // using base is important
		}
	}

	/*
		Function: getFiles
			Retrieve files from folders and individuals

		Returns:
			Array
	*/
	public function getFiles($source = null)
	{
		// get source or use default
		$source = $source ? $source : $this->getDefaultSource();

		$files = array();
		if(!empty($source))
		{
			// S3 integration
			if($this->config->find('files._s3', 0) && $this->_S3())
			{
				// TODO check if is readable
				$files[] = $source;
			}

			// external source
			else if (strpos($source, 'http') === 0)
			{
				// TODO check if is readable
				$files[] = $source;
			}

			// local source
			else 
			{
				// get full path
				$sourcepath = $this->app->path->path("root:$source");

				// if directory
				if($sourcepath && is_dir($sourcepath)){

					// retrieve all valid files
					foreach ($this->app->path->files("root:$source", false, '/^.*('.$this->getLegalExtensions().')$/i') as $filename) {
						$file = "$sourcepath/$filename";
						if ($file && is_file($file) && is_readable($file)) {
							$files[] = "$source/$filename";
						}
					}

				// if file
				} else if($sourcepath && is_file($sourcepath) && is_readable($sourcepath)) {
					$files[] = $source;
				}
			}
		}

		return $files;
	}

	/*
		Function: getDefaultSource
			Retrieve default source if empty value

		Returns:
			String - Path to file(s)
	*/
	protected function getDefaultSource()
	{
		// get default, fallback to default_file as the param name was changed
		$default_source = $this->config->find('files._default_source', $this->config->find('files._default_file', ''));

		// get item author user object
		$user = $this->app->user->get($this->getItem()->created_by);

		// Replace any path variables
		$pattern = array(
			'/\[authorname\]/'
		);
		$replace = array(
			$user = $user ? $user : ''
		);
		
		return preg_replace($pattern, $replace, $default_source); 
	}

/* -------------------------------------------------------------------------------------------------------------------------------------------------------- EDIT */

	/*
		Function: loadAssets
			Load elements css/js assets.

		Returns:
			Void
	*/
	public function loadAssets()
	{
		parent::loadAssets();

		// load ZLUX assets
		$this->app->zlfw->zlux->loadFilesManagerAssets();

		// load the FilesPro js
		$this->app->document->addScript('elements:filespro/assets/js/filespro.min.js');
	}

/* -------------------------------------------------------------------------------------------------------------------------------------------------------- FILE MANAGER */

	/*
		DEPRICATED since 3.0.15
	*/
	public function getPreview($source = null) 
	{
		$sourcepath = $this->app->path->path('root:'.$source);
		
		if (is_dir($sourcepath))
		{
			return '<img src="'.$this->app->path->url('elements:filespro/assets/images/folder_horiz.png').'">';
		}
		else if (is_file($sourcepath) || strpos($source, 'http') === 0 || $this->config->find('files._s3', 0))
		{
			$url = parse_url($source);
			$ext = strtolower($this->app->zlfilesystem->getExtension($url['path']));
			return '<span class="file-type">'.$ext.'</span>';
		}
	}
	
	/*
		Function: getFileDetails
			Return file info
			
		Parameters:
			$file - source file
			$json - boolean, format
			
		Returns:
			JSON or Array
	*/
	public function getFileDetails($file = null, $json = true)
	{
		$file = $file === null ? $this->get('file') : $file;
	
		$data = null;
		if(strlen($file) && $this->config->find('files._s3', 0) && $this->_S3()){
			$data = $this->_s3FileDetails($file, $json);
		} else if (strlen($file)){
			$data = $this->_fileDetails($file, $json); // local or external source
		}
		
		$data = $this->app->data->create($data);
		// $data['all'] = ($data->get('size') ? $data->get('size') : '')
		// 			  .($data->get('res') ? ' - '.$data->get('res') : '')
		// 			  .($data->get('dur') ? ' - '.$data->get('dur') : '')
		// 			  .($data->get('files') ? ' - '.$data->get('files').' '.JText::_('PLG_ZLFRAMEWORK_FILES') : '');
						
		
		return $json ? json_encode($data) : $data;
	}
	
	private function _fileDetails($source = null)
	{
		$data = null;
		if (strpos($source, 'http') === 0) // external source
		{
			$data = array(
				'type'		=> 'file',
				'basename'	=> JFile::stripExt(basename($source)),
				'ext'		=> strtolower($this->app->zlfilesystem->getExtension($source)),
				'size'		=> array('display' => $this->getSourceSize($source))
			);
		} 
		else // local source
		{
			$sourcepath = $this->app->path->path('root:'.$source);
			if (is_readable($sourcepath) && is_file($sourcepath)){
				$imageinfo = getimagesize($sourcepath);
				$data = array(
					'type'		=> 'file',
					'basename'	=> JFile::stripExt(basename($source)),
					'ext'		=> strtolower($this->app->zlfilesystem->getExtension($source)),
					'size'		=> array('display' => $this->getSourceSize($source)),
					'res'		=> $imageinfo ? $imageinfo[0].'x'.$imageinfo[1].'px' : ''
				);
			} else if (is_readable($sourcepath) && is_dir($sourcepath)){
				$tSize = $this->getSourceSize($source);
				$data = array(
					'type'		=> 'folder',
					'basename'	=> ($tSize ? basename($source) : JText::_('PLG_ZLFRAMEWORK_FLP_NO_VALID_FILES')),
					'size'		=> array('display' => $tSize),
					'files'		=> count($this->app->path->files('root:'.$source, false, '/^.*('.$this->getLegalExtensions().')$/i'))
				);
			}
		}
		
		return $data;
	}
	
	private function _s3FileDetails($file = null)
	{		
		$bucket = $this->config->find('files._s3bucket');
		$object = $this->_S3()->getObjectInfo($bucket, $file);

		$data = array(
			'type'		=> $this->getElementType(),
			'name'		=> JFile::stripExt(basename($file)),
			'ext'		=> strtolower($this->app->zlfilesystem->getExtension($file)),
			'size'		=> array('display' => $this->app->zlfilesystem->formatFilesize($this->app->zlfilesystem->returnBytes($object['size'])))
		);	
		
		return $data;
	}
	
	/*
		Function: getFileDetailsDom
			Return file details dom
			
		Parameters:
			$file - source file
			
		Returns:
			HTML
	*/
	public function getFileDetailsDom($file=null)
	{
		$file = $file === null ? $this->get('file') : $file;

		return "<span class=\"zlux-x-filedata\" data-zlux-data='" . json_encode($this->getFileDetails($file, false)) . "'></span>";
	}

	/*
	   Function: getExtension
		   Get the file extension string.

	   Returns:
		   String - file extension
	*/
	public function getExtension($file = null, $checkMime = true) {
		$file = empty($file) ? $this->get('file') : $file;
		return strtolower($this->app->zlfilesystem->getExtension($file, $checkMime));
	}
	
	/*
	   Function: getLegalExtensions
		   Get the legal extensions string

	   Returns:
		   String - element legal extensions
	*/
	public function getLegalExtensions($separator = '|') {
		$extensions = $this->config->find('files._extensions', $this->_extensions);
		return str_replace('|', $separator, $extensions);
	}
	
	/*
		Function: getSourceSize
			get the file or folder files size with extension filter

		Returns:
			Array
	*/
	protected function getSourceSize($source = null)
	{
		// init vars
		$sourcepath = $this->app->path->path('root:'.$source);
		$size = '';
		
		if (strpos($source, 'http') === 0) // external source
		{
			$ch = curl_init(); 
			curl_setopt($ch, CURLOPT_HEADER, true); 
			curl_setopt($ch, CURLOPT_NOBODY, true);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); 
			curl_setopt($ch, CURLOPT_URL, $source); //specify the url
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE); 
			$head = curl_exec($ch);
			
			$size = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
		} 
		if (is_file($sourcepath))
		{
			$size = filesize($sourcepath);
		}
		else if(is_dir($sourcepath)) foreach ($this->app->path->files('root:'.$source, false, '/^.*('.$this->getLegalExtensions().')$/i') as $file){
			$size += filesize($this->app->path->path("root:{$source}/{$file}"));
		}
		
		return ($size ? $this->app->zlfilesystem->formatFilesize($size) : 0);
	}
	
	/*
	 * Return the full directory path
	 *
	 * Original Credits:
	 * @package   	JCE
	 * @copyright 	Copyright ¬© 2009-2011 Ryan Demmer. All rights reserved.
	 * @license   	GNU/GPL 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
	 * 
	 * Adapted to ZOO (ZOOlanders.com)
	 * Copyright 2011, ZOOlanders.com
	 */
	public function getDirectory($allowroot = false)
	{
		$user = JFactory::getUser();
		$item = $this->getItem();

		// Get base directory as shared parameter
		$root = $this->config->find('files._source_dir', $this->_joomla_file_path);
		
		// Restricted Joomla! folders
		$restricted = explode(',', 'administrator,cache,components,includes,language,libraries,logs,media,modules,plugins,templates,xmlrpc');
		

		// Remove whitespace
		$root = trim($root);
		// Convert slashes / Strip double slashes
		$root = preg_replace('/[\\\\]+/', '/', $root);
		// Remove first leading slash
		$root = ltrim($root, '/');
		
		// Split in parts to better manage
		$parts = explode('/', $root);
		// Force default directory if path starts with a variable, a . or is empty
		if (preg_match('/[\.\[]/', $parts[0]) || (empty($root) && !$allowroot)) {
			$parts[0] = $this->_joomla_file_path;
		}
		// Force default if directory is a joomla directory conserving the variables
		if (!$allowroot && in_array(strtolower($parts[0]), $restricted)) {
			$parts[0] = $this->_joomla_file_path;
		}
		// join back
		$root = implode('/', $parts);
		
		jimport('joomla.user.helper');
		// Joomla! 1.6+
		if (method_exists('JUserHelper', 'getUserGroups')) {
			$groups 	= JUserHelper::getUserGroups($user->id);
			$groups		= array_keys($groups);
			$usertype 	= array_shift($groups);												
		} else {
			$usertype 	= $user->usertype;
		}

		// Replace any path variables
		$pattern = array(
			'/\[userid\]/', '/\[username\]/', '/\[usertype\]/',
			'/\[zooapp\]/', '/\[zooprimarycat\]/', '/\[zooprimarycatid\]/',
			'/\[day\]/', '/\[month\]/', '/\[year\]/'
		);
		$replace = array(
			$user->id, $user->username, $usertype,
			strtolower($item->getApplication()->name), ($item->getPrimaryCategory() ? $item->getPrimaryCategory()->alias : 'none'), $item->getPrimaryCategoryId(),
			date('d'), date('m'), date('Y')
		);
		
		$root = preg_replace($pattern, $replace, $root);

		// split into path parts to preserve /
		$parts = explode('/', $root);
		// clean path parts
		$parts = $this->app->zlfilesystem->makeSafe($parts, 'ascii');
		// join path parts
		$root = implode('/', $parts);
		
		// Create the folder
		$full = $this->app->zlfilesystem->makePath(JPATH_SITE, $root);
		if (!$this->config->find('files._s3', 0) && !JFolder::exists($full))
		{
			$this->app->zlfilesystem->folderCreate($full);
			return JFolder::exists($full) ? $root : $this->_joomla_file_path;
		}
		
		return $root;
	}
	
/* -------------------------------------------------------------------------------------------------------------------------------------------------------- SUBMISSIONS */
	
	/*
		Function: _renderSubmission
			Renders the element in submission.

		Parameters:
			$params - AppData submission parameters

		Returns:
			String - html
	*/
	public function _renderSubmission($params = array())
	{
		// init vars
		$trusted_mode = $params->get('trusted_mode');
		$layout		  = $params->find('layout._layout', 'default.php');

		if ($layout == 'advanced') {
			if ($trusted_mode)
				return $this->_edit();
			else
				$layout = 'default.php';
		} 
		
		if ($layout = $this->getLayout("submission/$layout"))
		{
			return $this->renderLayout($layout, compact('params', 'trusted_mode'));
		}
	}
	
	/*
		Function: validateSubmission
			Validates the submitted element

		Parameters:
			$value  - AppData value
			$params - AppData submission parameters

		Returns:
			Array - cleaned value
	*/
	public function validateSubmission($value, $params)
	{	
		// get old file values, the allready stored ones
		$old_files = array();
		foreach($this as $self) {
			$old_files[] = $this->get('file');
		}
		$old_files = array_filter($old_files);

		// Reorganize the files to make them easier to manage (tricky)
		$userfiles = array();
		foreach($value->get('userfile', array()) as $key => $vals) {
			$vals = array_filter($vals);
			foreach($vals as $i => $val){
				$userfiles[$i][$key] = $val;
			}
		}

		// remove the old user info
		if(isset($value['userfile']))
			unset($value['userfile']);

		// reindex values, important
		$value = array_values((array)$value);

		$result = array();
		foreach($value as $key => &$single_value)
		{
			if (isset($userfiles[$key]))
			{	
				$single_value = array('old_file' => (isset($old_files) ? $old_files : ''), 'userfile' => $userfiles[$key], 'values' => $single_value);
			} else {
				$single_value = array('values' => $single_value);
			}

			// validate
			try {
				$result[] = $this->_validateSubmission($this->app->data->create($single_value), $params);

			} catch (AppValidatorException $e) {

				if ($e->getCode() != AppValidator::ERROR_CODE_REQUIRED) {
					throw $e;
				}
			}
		}
		
		if ($params->get('required') && !count($result)) {
			if (isset($e)) {
				throw $e;
			}
			throw new AppValidatorException('This field is required');
		}
		
		// connect to submission beforesave event
		$this->params = $params;
		$this->app->event->dispatcher->connect('submission:beforesave', array($this, 'submissionBeforeSave'));

		return $result;
	}
	
	/*
		Function: submissionBeforeSave
			Callback before item submission is saved

		Returns:
			void
	*/
	public function submissionBeforeSave($event)
	{
		$userfiles = array();
		// merge userfiles element data with post data
		foreach ($_FILES as $key => $userfile) {
			if (strpos($key, 'elements_'.$this->identifier) === 0) {
				// Reorganize the files to make them easier to manage (tricky)
				foreach($userfile as $key => $values) foreach($values as $i => $value){
					$userfiles[$i][$key] = $value;
				}
			}
		}
		
		$files = array();
		// now for the real upload
		foreach($userfiles as $userfile)
		{
			// get the uploaded file information
			if ($userfile && $userfile['error'] == 0 && is_array($userfile)) {

				// get filename and make it websafe
				$fileName = $this->app->zlfilesystem->makeSafe($userfile['name'], 'ascii');

				// init vars
				$ext 		= strtolower(JFile::getExt($fileName));
				$basename 	= substr($fileName, 0, strrpos($fileName, '.'));
				$targetDir 	= JPATH_ROOT.'/'.$this->getDirectory();

				// construct filename
				$fileName = "{$basename}.{$ext}";

				// Make sure the fileName is unique
				if (JFile::exists("$targetDir/$fileName")) {
					$count = 1;
					while (JFile::exists("{$targetDir}/{$basename}_{$count}.{$ext}"))
						$count++;
				
					$fileName = "{$basename}_{$count}.{$ext}";
				}

				// Create target dir
				if (!JFolder::exists($targetDir))
					JFolder::create($targetDir);
				
				// upload the file
				if (!JFile::upload($userfile['tmp_name'], "$targetDir/$fileName")) {
					throw new AppException('Unable to upload file.');
				}

				// set the index file in directory
				$this->app->zoo->putIndexFile($targetDir);

				$files[] = $file;
			}
		}
	}
}

// register AppValidatorFile class
App::getInstance('zoo')->loader->register('AppValidatorFile', 'classes:validator.php');

/**
 * Filespro validator
 *
 * @package Component.Classes.Validators
 */
class AppValidatorFilepro extends AppValidatorFile {

  /**
	 * Clean the file value
	 *
	 * @param  mixed $value The value to clean
	 *
	 * @return mixed        The cleaned value
	 *
	 * @see AppValidator::clean()
	 *
	 * @since 2.0
	 */
	public function clean($value) {
		if (!is_array($value) || !isset($value['tmp_name'])) {
			throw new AppValidatorException($this->getMessage('invalid'));
		}

		if (!isset($value['name'])) {
			$value['name'] = '';
		}

		// init vars
		$ext 		= strtolower(JFile::getExt($value['name']));
		$basename 	= substr($value['name'], 0, strrpos($value['name'], '.'));

		// construct filename
		$value['name'] = "{$basename}.{$ext}";

		// split into parts
		$parts = explode('/', $value['name']);

		// clean path parts
		$parts = $this->app->zlfilesystem->makeSafe($parts, 'ascii');

		// join path parts
		$value['name'] = implode('/', $parts);

		if (!isset($value['error'])) {
			$value['error'] = UPLOAD_ERR_OK;
		}

		if (!isset($value['size'])) {
			$value['size'] = filesize($value['tmp_name']);
		}

		if (!isset($value['type'])) {
			$value['type'] = 'application/octet-stream';
		}

		switch ($value['error']) {
			case UPLOAD_ERR_INI_SIZE:
				throw new AppValidatorException(sprintf($this->getMessage('max_size'), $this->returnBytes(@ini_get('upload_max_filesize')) / 1024), UPLOAD_ERR_INI_SIZE);
			case UPLOAD_ERR_FORM_SIZE:
				throw new AppValidatorException($this->getMessage('max_size'), UPLOAD_ERR_FORM_SIZE);
			case UPLOAD_ERR_PARTIAL:
				throw new AppValidatorException($this->getMessage('partial'), UPLOAD_ERR_PARTIAL);
			case UPLOAD_ERR_NO_FILE:
				throw new AppValidatorException($this->getMessage('no_file'), UPLOAD_ERR_NO_FILE);
			case UPLOAD_ERR_NO_TMP_DIR:
				throw new AppValidatorException($this->getMessage('no_tmp_dir'), UPLOAD_ERR_NO_TMP_DIR);
			case UPLOAD_ERR_CANT_WRITE:
				throw new AppValidatorException($this->getMessage('cant_write'), UPLOAD_ERR_CANT_WRITE);
			case UPLOAD_ERR_EXTENSION:
				throw new AppValidatorException($this->getMessage('err_extension'), UPLOAD_ERR_EXTENSION);
		}

		// check mime type
		if ($this->hasOption('mime_types')) {
			$mime_types = $this->getOption('mime_types') ? $this->getOption('mime_types') : array();
			if (!in_array($value['type'], array_map('strtolower', $mime_types))) {
				throw new AppValidatorException($this->getMessage('mime_types'));
			}
		}

		// check mime type group
		if ($this->hasOption('mime_type_group')) {
			if (!in_array($value['type'], $this->_getGroupMimeTypes($this->getOption('mime_type_group')))) {
				throw new AppValidatorException($this->getMessage('mime_type_group'));
			}
		}

		// check file size
		if ($this->hasOption('max_size') && $this->getOption('max_size') < (int) $value['size']) {
			throw new AppValidatorException(sprintf(JText::_($this->getMessage('max_size')), ($this->getOption('max_size') / 1024)));
		}

		// check extension
		if ($this->hasOption('extension') && !in_array($this->app->filesystem->getExtension($value['name']), $this->getOption('extension'))) {
			throw new AppValidatorException($this->getMessage('extension'));
		}

		return $value;
	}
}


/*
	Class: ZLSplFileInfo
		The ZLSplFileInfo extends SplFileInfo class which offers a high-level object oriented interface to information for an individual file.
		http://au1.php.net/manual/en/class.splfileinfo.php
*/
class FilesProSplFileInfo extends SplFileInfo
{
	/**
	 * Reference to the global App object
	 *
	 * @var App
	 * @since 3.0.5
	 */
	public $app;
	
	/**
	 * Class constructor. Creates a new ZLSplFileInfo object for the file_path specified.
	 * The file does not need to exist, or be readable
	 *
	 * @param String $file_path Path to the file
	 */
	public function __construct($file_path, &$element) {

		// call parent constructor
		parent::__construct($file_path);

		// set application
		$this->app = App::getInstance('zoo');
		
		// set element
		$this->element = $element;
	}

	/**
	 * Gets the file extension
	 *
	 * @return string The file extension or empty if the file has no extension
	 *
	 * @since 3.0.4
	 */
	public function getExtension()
	{
		if (version_compare(PHP_VERSION, '5.3.6', '>=')) {
			return parent::getExtension();
		} else {
			return pathinfo($this->getPathname(), PATHINFO_EXTENSION);
		}
	}

	/**
	 * Get the file content type
	 *
	 * @return string The content type
	 *
	 * @since 3.0.5
	 */
	public function getContentType()
	{
		return $this->app->filesystem->getContentType($this->getPathname());
	}

	/**
	 * Get the absolute url to a file
	 *
	 * @return string The absolute url
	 *
	 * @since 3.0.5
	 */
	public function getURL()
	{
		if ($this->element->config->find('files._s3', 0)) // Amazon S3
		{
			$bucket = $this->element->config->find('files._s3bucket');
			return $this->element->_S3()->getAuthenticatedURL($bucket, $this->getPathname(), 3600);
		} else if ($rel_url = $this->app->path->url("root:{$this->getPathname()}")) {
			return $rel_url;
		} else {
			return $this->getPathname();
		}
	}

	/**
	 * Gets the file title
	 *
	 * @return string The file title
	 *
	 * @since 3.0.5
	 */
	public function getTitle($title = null)
	{
		$title = $title ? $title : $this->getBasename('.'.$this->getExtension());

		// return without _ carachter
		return str_replace('_', ' ', $title);
	}

}