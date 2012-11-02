<?php
/**
* @package		ZL Framework
* @author    	JOOlanders, SL http://www.zoolanders.com
* @copyright 	Copyright (C) JOOlanders, SL
* @license   	http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
*/

// register ElementRepeatablePro class
App::getInstance('zoo')->loader->register('ElementRepeatablePro', 'elements:repeatablepro/repeatablepro.php');

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
		
		// set callbacks
		$this->registerCallback('uploadFiles');
		$this->registerCallback('getfiledetails');
		$this->registerCallback('files');
		$this->registerCallback('delete');
		$this->registerCallback('newfolder');
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
	protected function _S3()
	{
		if ($this->_s3 == null) {
			//include the S3 class
			if (!class_exists('S3')) require_once($this->app->path->path('elements:filespro/assets/s3/S3.php'));
			$s3 = new S3(trim($this->config->find('files._awsaccesskey')), trim($this->config->find('files._awssecretkey'))); // instantiate the class

			if(@$s3->listBuckets() && (@$constraint = $s3->getBucketLocation(trim($this->config->find('files._s3bucket')))) !== false)
			{
				$location = array('US' => 's3.amazonaws.com', 'us-west-1' => 's3-us-west-1.amazonaws.com', 'us-west-2' => 's3-us-west-2.amazonaws.com', 'eu-west-1' => 's3-eu-west-1.amazonaws.com', 'EU' => 's3-eu-west-1.amazonaws.com', 'ap-southeast-1' => 's3-ap-southeast-1.amazonaws.com', 'ap-northeast-1' => 's3-ap-northeast-1.amazonaws.com', 'sa-east-1' => 's3-sa-east-1.amazonaws.com');
				$this->_s3 = new S3(trim($this->config->find('files._awsaccesskey')), trim($this->config->find('files._awssecretkey')), false, $location[$constraint]);
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
			Checks if the repeatables element's file is set.
			It checks default path if no value.

	   Parameters:
			$params - render parameter

		Returns:
			Boolean - true, on success
	*/
	protected function _hasValue($params = array())
	{
		if ($this->config->find('files._s3', 0)){
			$file = $this->get('file') ? $this->_S3()->getObjectInfo($this->config->find('files._s3bucket'), $this->get('file')) : false;
		} else {
			$file = $this->get('file', $this->config->find('files._default_source', $this->config->find('files._default_file', false)));
		}
		return !empty($file);
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
		Function: getFileObject
			Create and return the individual File object

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
	protected function getFiles($source = null)
	{
		$files = array();
	
		if($this->config->find('files._s3', 0) || strpos($source, 'http') === 0) // S3 or external source
		{
			$files[] = $source;			
		}
		else 
		{
			
			// get source or use default
			$source = $source ? $source : $this->getDefaultSource();
	
			if(!empty($source))
			{
				$sourcepath = $this->app->path->path("root:$source");
				if($sourcepath && is_dir($sourcepath)){
					// if directory get all files from it
					foreach ($this->app->path->files("root:$source", false, '/^.*('.$this->getLegalExtensions().')$/i') as $filename) {
						$file = $this->app->path->path("root:$source/$filename");
						if ($file && is_file($file) && is_readable($file)) {
							$files[] = "$source/$filename";
						}
					}
				} else if($sourcepath && is_file($sourcepath) && is_readable($sourcepath)) {
					// if file procede as usual
					$files[] = $source;
				}
			}
		}
		
		return $files;
	}

	/*
		Function: getDefaultSource
			Retrieve default source if source
			is not present in item edit

		Returns:
			String - Path to file(s)
	*/
	protected function getDefaultSource() {

		$default_source = $this->config->find('files._default_source', $this->config->find('files._default_file', ''));

		// Replace any path variables
		$pattern = array(
			'/\[authorname\]/'
		);
		$replace = array(
			$user = JFactory::getUser($this->getItem()->created_by)->username,$this->getItem()->name
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
		
		// ui must be loaded first
		$this->app->document->addStylesheet('libraries:jquery/jquery-ui.custom.css');
		$this->app->document->addScript('libraries:jquery/jquery-ui.custom.min.js');
		
		//then plupload
		$this->app->document->addStylesheet('elements:filespro/assets/plupload/css/jquery.ui.plupload.custom.css');
		$this->app->document->addScript('elements:filespro/assets/plupload/plupload.full.js');
		$this->app->document->addScript('elements:filespro/assets/plupload/jquery.ui.plupload.js');
		$this->app->zlfw->pluploadTranslation();
		$this->app->zlfw->filesproTranslation();
		
		// and others
		$this->app->zlfw->loadLibrary('qtip');
		$this->app->document->addScript('elements:filespro/assets/js/plupload.js');
		$this->app->document->addStylesheet('elements:filespro/assets/filespro.css');
		$this->app->document->addScript('elements:filespro/assets/js/filespro.js');
		$this->app->document->addScript('elements:filespro/assets/js/finder.js');
	}

/* -------------------------------------------------------------------------------------------------------------------------------------------------------- FILE MANAGER */

	/*
		Function: getPreview
			Return file preview
			
		Parameters:
			$source
			
		Returns:
			string
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
		$data['all'] = ($data->get('size') ? $data->get('size') : '')
					  .($data->get('res') ? ' - '.$data->get('res') : '')
					  .($data->get('dur') ? ' - '.$data->get('dur') : '')
					  .($data->get('files') ? ' - '.$data->get('files').' '.JText::_('PLG_ZLFRAMEWORK_FILES') : '');
						
		
		return $json ? json_encode($data) : $data;
	}
	
	private function _fileDetails($source = null)
	{
		$data = null;
		if (strpos($source, 'http') === 0) // external source
		{
			$data = array(
				'source'	=> 'file',
				'name'		=> JFile::stripExt(basename($source)),
				'preview'	=> $this->getPreview($source),
				'ext'		=> strtolower($this->app->zlfilesystem->getExtension($source)),
				'size'		=> $this->getSourceSize($source)
			);
		} 
		else // local source
		{
			$sourcepath = $this->app->path->path('root:'.$source);
			if (is_readable($sourcepath) && is_file($sourcepath)){
				$imageinfo = getimagesize($sourcepath);
				$data = array(
					'source'	=> 'file',
					'name'		=> JFile::stripExt(basename($source)),
					'preview'	=> $this->getPreview($source),
					'ext'		=> strtolower($this->app->zlfilesystem->getExtension($source)),
					'size'		=> $this->getSourceSize($source),
					'res'		=> $imageinfo ? $imageinfo[0].'x'.$imageinfo[1].'px' : ''
				);
			} else if (is_readable($sourcepath) && is_dir($sourcepath)){
				$tSize = $this->getSourceSize($source);
				$data = array(
					'source'	=> 'folder',
					'name'		=> ($tSize ? basename($source) : JText::_('PLG_ZLFRAMEWORK_FLP_NO_VALID_FILES')),
					'preview'	=> $this->getPreview($source),
					'size'		=> $tSize,
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
			'preview'	=> $this->getPreview($this->_S3()->getAuthenticatedURL($bucket, $file, 3600)),
			'ext'		=> strtolower($this->app->zlfilesystem->getExtension($file)),
			'size'		=> $this->app->zlfilesystem->formatFilesize($this->app->zlfilesystem->returnBytes($object['size']))
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
		$fd = $this->app->data->create($this->getFileDetails($file, false));
		
		return '<div class="file-details">'
					.'<div class="fp-found" style="display: '.($fd->get('name') ? 'block' : 'none').'">'
						.'<div class="file-preview">'.$fd->get('preview').'</div>'
						.'<div class="file-info">'
							.'<div class="file-name"><span>'.$fd->get('name').'</span></div>'
							.'<div class="file-properties">'.$fd->get('all').'</div>'
						.'</div>'
					.'</div>'
					.'<div class="fp-missing" style="display: '.(!strlen($file) || $fd->get('name') ? 'none' : 'block').'">'.JText::_('PLG_ZLFRAMEWORK_FLP_MISSING_FILE').'</div>'
				.'</div>';
	}
	
	/*
		Function: delete
			Delete Folder or File
			
		Parameters:
			$path: file or folder relative path
	*/
	public function delete()
	{
		$path = $this->app->request->get('path', 'string', ''); // selected path to delete
		$fullpath = JPATH_ROOT . '/' . $this->getDirectory() . '/' . ($path ? $path : '');
	
		jimport('joomla.filesystem.file');
		if (is_readable($fullpath) && is_file($fullpath))
			JFile::delete($fullpath);
		else if (is_readable($fullpath) && is_dir($fullpath))
			JFolder::delete($fullpath);
	}
	
	/*
		Function: newfolder
			Create new Folder
			
		Parameters:
			$path: parent folder path
	*/
	public function newfolder()
	{
		$path	   = $this->app->request->get('path', 'string', ''); // selected path
		$newfolder = $this->app->request->get('newfolder', 'string', ''); // new folder name
		$fullpath  = JPATH_ROOT . '/' . $this->getDirectory() . '/' . ($path ? $path : '').'/'.$newfolder;		
		
		// if does not exist, create
		if (!JFolder::exists($fullpath))
			JFolder::create($fullpath);
	}

	/*
	   Function: getExtension
	       Get the file extension string.

	   Returns:
	       String - file extension
	*/
	public function getExtension($file = null) {
		$file = empty($file) ? $this->get('file') : $file;
		return strtolower($this->app->zlfilesystem->getExtension($file));
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
		Function: files
			Get directory/file list JSON formatted

		Returns:
			Void
	*/
	public function files()
	{
		if ($this->config->find('files._s3', 0)) return $this->filesFromS3();
		else return $this->filesFromDirectory();
	}
	
	protected function filesFromDirectory() {
		$tree = array();
		$path = trim($this->app->request->get('path', 'string'), '/');
		$path = empty($path) ? '' : '/'.$path;
		foreach ($this->app->path->dirs('root:'.$this->getDirectory().$path) as $dir) {
			$name = basename($dir); $name = (strlen($name) > 30) ? substr($name, 0,30).'...' : $name; // limit name length
			$tree[] = array('name' => $name, 'path' => $path.'/'.$dir, 'type' => 'folder', 'val' => $this->getDirectory().$path.'/'.$dir);
		}
		foreach ($this->app->path->files('root:'.$this->getDirectory().$path, false, '/^.*('.$this->getLegalExtensions().')$/i') as $file) {
			$name = basename($file); $name = (strlen($name) > 30) ? substr($name, 0,30).'...' : $name; // limit name length
			$tree[] = array('name' => $name, 'path' => $path.'/'.$file, 'type' => 'file', 'val' => $this->getDirectory().$path.'/'.$file);
		}
		
		return json_encode($tree);
	}

	protected function filesFromS3() {
	
		$s3 = $this->_S3();
	
		if ($s3){
		
			$awsbucket = trim($this->config->find('files._s3bucket'));
			$req_type  = $this->app->request->get('req_type', 'string', 0);
			$folders = $files = array();

			$path   = $req_type == 'init' ? $this->getDirectory(true) : $this->app->request->get('path', 'string', '');
			$prefix = trim($path, '/');
			
			// get all objects and filter by folder/file
			$objects = $s3->getBucket($awsbucket, $prefix);
			if (count($objects) && $req_type != 'file') foreach ($objects as $obj) {
				$name		  = $obj['name'];
				$last_car 	  = substr($name, -1);
				$child		  = substr($name, strlen($prefix)+1);
				$count_folder = substr_count($child, '/');
				
				// filter current folder and subfolders objects
				if ($name == $prefix.'/' || $count_folder >= 2 || ($count_folder >= 1 && $last_car != '/')) continue;
			
				if ($obj['size'] == 0 && $last_car == '/') {
					$folders[] = array('name' => basename($name), 'path' => $name, 'type' => 'folder', 'val' => $name);
				} else {
					// continue if no regex filter match
					if (!preg_match('/^.*('.$this->_extensions.')$/i', $name)) continue;
					$files[]   = array('name' => basename($name), 'path' => $name, 'type' => 'file', 'val' => $name);
				}
			}
			else if ($req_type == 'init')
			{
				return json_encode(array('msg' => JText::_('PLG_ZLFRAMEWORK_FLS_S3_NO_DATA')));
			}
			
			return json_encode(array_merge($folders, $files));
			

		} else {
			return json_encode(array('msg' => JText::_('PLG_ZLFRAMEWORK_FLS_S3_ACCES_FAIL')));
		}
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
		$parts = $this->app->zlfilesystem->makeSafe($parts, $this->config->find('files._websafe_mode', 'utf-8'));
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
	
	/**
	 * Original Credits:
	 * upload.php
	 *
	 * Copyright 2009, Moxiecode Systems AB
	 * Released under GPL License.
	 *
	 * License: http://www.plupload.com/license
	 * Contributing: http://www.plupload.com/contributing
	 * 
	 * Adapted to ZOO (ZOOlanders.com)
	 * Copyright 2011, ZOOlanders.com
	 */
	public function uploadFiles()
	{	
		$path = $this->app->request->get('path', 'string', ''); // selected subfolder
		
		// Settings
		$targetDir = JPATH_ROOT . '/' . $this->getDirectory() . '/' . (isset($path) ? $path.'/' : '');
		
		// Get parameters
		$chunk = JRequest::getVar("chunk", 0);
		$chunks = JRequest::getVar("chunks", 0);
		$fileName = JRequest::getVar("name", '');
		
		// Clean the fileName for security reasons
		$fileName = preg_replace('/[^\w\._]+/', '', $fileName);
		
		// Make sure the fileName is unique but only if chunking is disabled
		if ($chunks < 2 && JFile::exists($targetDir . '/' . $fileName)) {
			$fileName_b = strtolower(JFile::getExt($fileName));
			$fileName_a = JFile::stripExt($fileName);
		
			$count = 1;
			while (JFile::exists($targetDir . '/' . $fileName_a . '_' . $count . '.' . $fileName_b))
				$count++;
		
			$fileName = $fileName_a . '_' . $count . '.' . $fileName_b;
		}
		
		// Create target dir
		if (!JFolder::exists($targetDir))
			JFolder::create($targetDir);
		
		// Look for the content type header
		if (isset($_SERVER["HTTP_CONTENT_TYPE"]))
			$contentType = $_SERVER["HTTP_CONTENT_TYPE"];
		
		if (isset($_SERVER["CONTENT_TYPE"]))
			$contentType = $_SERVER["CONTENT_TYPE"];
		
		// Handle non multipart uploads older WebKit versions didn't support multipart in HTML5
		if (strpos($contentType, "multipart") !== false) {
			if (isset($_FILES['file']['tmp_name']) && is_uploaded_file($_FILES['file']['tmp_name'])) {
				// Open temp file
				$out = fopen($targetDir . DIRECTORY_SEPARATOR . $fileName, $chunk == 0 ? "wb" : "ab");
				if ($out) {
					// Read binary input stream and append it to temp file
					$in = fopen($_FILES['file']['tmp_name'], "rb");
		
					if ($in) {
						while ($buff = fread($in, 4096))
							fwrite($out, $buff);
					} else
						die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
					fclose($in);
					fclose($out);
					@unlink($_FILES['file']['tmp_name']);
				} else
					die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
			} else
				die('{"jsonrpc" : "2.0", "error" : {"code": 103, "message": "Failed to move uploaded file."}, "id" : "id"}');
		} else {
			// Open temp file
			$out = fopen($targetDir . DIRECTORY_SEPARATOR . $fileName, $chunk == 0 ? "wb" : "ab");
			if ($out) {
				// Read binary input stream and append it to temp file
				$in = fopen("php://input", "rb");
		
				if ($in) {
					while ($buff = fread($in, 4096))
						fwrite($out, $buff);
				} else
					die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
		
				fclose($in);
				fclose($out);
			} else
				die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
		}
		
		// Return JSON-RPC response
		die('{"jsonrpc" : "2.0", "result" : null, "id" : "id"}');
		
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
		// get old file values
		$old_files = array();
		foreach($this as $self) {
			$old_files[] = $this->get('file');
		}
		
		// Reorganize the files to make them easier to manage (tricky)
 		$userfiles = array();
		foreach($value->get('userfile', array()) as $key => $vals) foreach($vals as $i => $val){
			$userfiles[$i][$key] = $val;
		}

		// remove the old user info
		if(isset($value['userfile']))
			unset($value['userfile']);

			
		$result = array();
		foreach($value as $key => $single_value)
		{
			// prepare value array
			if (isset($userfiles[$key]))
			{
				$single_value = array('old_file' => (isset($old_files[$key]) ? $old_files[$key] : ''), 'userfile' => $userfiles[$key], 'values' => $single_value);
			} else {
				$single_value = array('values' => $single_value);
			}
	
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
	            // get file name
	            $ext = strtolower($this->app->zlfilesystem->getExtension($userfile['name']));
	            $base_path = JPATH_ROOT . '/' . $this->getDirectory() . '/';
	            $file = $tmp = $base_path . $userfile['name'];
	            $filename = basename($file, '.'.$ext);
	
	            $i = 1;
	            while (JFile::exists($tmp)) {
	                $tmp = $base_path . $filename . '-' . $i++ . '.' . $ext;
	            }
	            $file = $this->app->path->relative($tmp);
				
	            if (!JFile::upload($userfile['tmp_name'], $file)) {
	                throw new AppException('Unable to upload file.');
	            }
	
	            $files[] = $file;
	        }
		}
    }
	
}