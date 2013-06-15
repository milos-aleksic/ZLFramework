<?php
/**
* @package		ZL Framework
* @author    	JOOlanders, SL http://www.zoolanders.com
* @copyright 	Copyright (C) JOOlanders, SL
* @license   	http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

/*
	Class: ZlframeworkController
		The controller class for zoolanders extensions
*/
class ZluxController extends AppController {

	public function __construct($default = array())
	{
		parent::__construct($default);

		// set table
		$this->itemTable = $this->app->table->item;

		// get application
		$this->application = $this->app->zoo->getApplication();
	}


	/*
		Function: saveElement
			Save Element data without the need to save the entire Item
	*/
	public function saveElement()
	{
		// get request vars
		$el_identifier = $this->app->request->getString('elm_id', '');
		$item_id	   = $this->app->request->getInt('item_id', 0);
		$post		   = $this->app->request->get('post:', 'array', array());

		// load element
		if ($item_id) {
			$item = $this->itemTable->get($item_id);
		} elseif (!empty($type)){
			$item = $this->app->object->create('Item');
			$item->application_id = $this->application->id;
			$item->type = $type;
		} else {
			echo json_encode(array('result' => false));
			return;
		}

		if(isset($post['elements'][$el_identifier]) && $item->getElement($el_identifier))
		{
			$item = $this->itemTable->get($item_id);

			$item->elements->set($el_identifier, $post['elements'][$el_identifier]);
			$this->itemTable->save($item);

			echo json_encode(array('result' => true));
		}
	}

	/*
		Function: ItemsManager
			Get Item list JSON formatted

		Returns:
			JSON object
	*/
	public function ItemsManager()
	{
		// init vars
		$s_apps	 	= explode(',', $this->app->request->get('apps', 'string', ''));
		$s_types	= explode(',', $this->app->request->get('types', 'string', ''));
		$g_apps	 	= explode(',', $this->app->request->get('filter_apps', 'string', ''));
		$g_types 	= explode(',', $this->app->request->get('filter_types', 'string', ''));
		$g_cats		= explode(',', $this->app->request->get('filter_cats', 'string', ''));
		$g_tags		= explode(',', $this->app->request->get('filter_tags', 'string', ''));
		$sEcho	 	= $this->app->request->get('sEcho', 'string', '');

		/* Array of database columns which should be read and sent back to DataTables. Use a space where
		   you want to insert a non-database field (for example a counter or static image) */
		$aColumns = array('_itemname', 'application', 'type', 'access', 'author', 'id');

		// filter values
		$s_apps = array_filter($s_apps);
		$s_types = array_filter($s_types);

		$g_apps = array_filter($g_apps);
		$g_types = array_filter($g_types);
		$g_cats = array_filter($g_cats);
		$g_tags = array_filter($g_tags);

		// convert App groups into IDs
		foreach ($g_apps as $key => $group) if (!is_numeric($group)) {
			// remove group
			unset($g_apps[$key]);

			// add Group Apps ID
			foreach ($this->app->application->getApplications($group) as $app) {
				$g_apps[] = $app->id;
			}
		}

		// remove duplicates
		$g_apps = array_unique($g_apps);
		$g_types = array_unique($g_types);
		$g_cats = array_unique($g_cats);
		$g_tags = array_unique($g_tags);


		// get model
		$model = $this->app->zlmodel->getNew('item');
		$model->setState('select', 'DISTINCT a.*');

		// get all Apps object
		$apps =  $this->app->table->application->all();

		// get filter listings
		$aaApps = $aaTypes = $aaCats = $aaTags = array();
		foreach($apps as $app) if (empty($s_apps) || in_array($app->id, $s_apps))
		{
			// add App to list
			$aaApps[] = array('name' => $app->name, 'id' => $app->id);

			// skip filtered apps
			if (!empty($g_apps) && !in_array($app->id, $g_apps)) {
				continue;
			}

			// apps filtering
			if (in_array($app->id, $g_apps)) {
				$model->application(array('value' => $app->id));
			}

			// get types list
			foreach ($app->getTypes() as $type) {
				// if type is not filtered or it is filtered array
				if (empty($s_types) || in_array($type->id, $s_types)) {
					$aaTypes[] = array('name' => $type->name, 'id' => $type->id);

					// types filtering
					if (in_array($type->id, $g_types)) {
						$model->type(array('value' => $type->id));
					}
				}
			}

			// get categories list
			$list = $this->app->tree->buildList(0, $app->getCategoryTree(), array(), '- ', '.   ', '  ');
			foreach ($list as $category) {
				$aaCats[] = array('name' => $category->treename, 'id' => $category->id);

				// tag filtering
				if (in_array($category->id, $g_cats)) {
					$model->categories(array('value' => array($category->id)));
				}
			}

			// get tag list
			$tags = $this->app->table->tag->getAll($app->id);
			foreach ($tags as $key => $tag) {
				$tags[$key] = array('name' => $tag->name, 'id' => $tag->name);

				// tag filtering
				if (in_array($tag->name, $g_tags)) {
					$model->tags(array('value' => array($tag->name)));
				}
			}
			$aaTags = array_merge($aaTags, $tags);
		}

		// ordering
		$aOrder = "";
		if ( isset( $_GET['iSortCol_0'] ) )
		{
			for ( $i=0 ; $i<intval( $_GET['iSortingCols'] ) ; $i++ )
			{
				if ( $_GET[ 'bSortable_'.intval($_GET['iSortCol_'.$i]) ] == "true" )
				{
					$iColumnIndex = array_search( $_GET['mDataProp_'.$_GET['iSortCol_'.$i]], $aColumns );
					$aOrder[] = $aColumns[ $iColumnIndex ];
					$aOrder[] = $_GET['sSortDir_'.$i] == 'desc' ? '_reversed' : '';
				}
			}
		}

		$aOrder = array_values($aOrder);
		$model->setState('order_by', $aOrder);

		// paging
		if ( isset( $_GET['iDisplayStart'] ) && $_GET['iDisplayLength'] != '-1' )
		{
			$model->setState('limitstart', $_GET['iDisplayStart'], true);
			$model->setState('limit', $_GET['iDisplayLength'], true);
		}

		// Input search filtering
		if ( isset($_GET['sSearch']) && $_GET['sSearch'] != "" )
		{
			for ( $i=0 ; $i<count($aColumns) ; $i++ )
			{
				if ( isset($_GET['bSearchable_'.$i]) && $_GET['bSearchable_'.$i] == "true" )
				{
					/* === Core: Name element === */
					if( strlen( trim( $aColumns[$i] == '_itemname' ) ) )
					{
						$name = array(
							'value' => trim( $_GET['sSearch'] ),
							'logic' => 'AND'
						);
						
						$model->name($name);
					}
				}
			}
		}

		// pretty print of sql
		if (false) {
			$find = Array("FROM", "WHERE", "AND", "ORDER BY", "LIMIT", "OR");
			$replace = Array(" FROM", " WHERE", " AND", " ORDER BY", " LIMIT", " OR");
			$in = $model->getQuery();
			// dump(str_replace($find, $replace, $in));
		}

		// get items
		$rows = array();
		$items = $model->getList();
		foreach ($items as &$item) {
			$row = array();
			$row['id'] = $item->id;
			$row['_itemname'] = $item->name;
			$row['application']['name'] = $item->getApplication()->name;
			$row['application']['id'] = $item->getApplication()->id;
			$row['type']['name'] = $item->getType()->name;
			$row['type']['id'] = $item->getType()->id;
			$row['access'] = JText::_($this->app->zoo->getGroup($item->access)->name);

			// author
			$author = $item->created_by_alias;
			$author_id = $item->created_by;
			$users  = $this->itemTable->getUsers($item->application_id);
			if (!$author && isset($users[$item->created_by])) {
				$author = $users[$item->created_by]->name;
				$author_id = $users[$item->created_by]->id;
			}
			$row['author']['name'] = $author;
			$row['author']['id'] = $author_id;

			// item details
			$row['details'] = array();
			$row['details']['Route'] = $item->getApplication()->name.' / '.$item->getType()->name.' / ID '.$item->id;
			if ($author) $row['details']['Author'] = $author;
			$row['details']['Created'] = $item->created;
			$row['details']['Access'] = $row['access'];
			
			// add row
			$rows[] = $row;
		};


		// form and send JSON string
		$JSON = array(
			'sEcho' => $sEcho,
			'iTotalRecords' => $model->getResult(),
			'iTotalDisplayRecords' => $model->getResult(),
			'sColumns' => implode(', ', $aColumns),
			'aaApps' => $aaApps,
			'aaTypes' => $aaTypes,
			'aaCategories' => $aaCats,
			'aaTags' => $aaTags,
			'aaData' => $rows // items
		);

		echo json_encode($JSON);
	}

	/*
		Function: FilesManager
			Get directory/file list JSON formatted

		Returns:
			JSON object
	*/
	public function getFilesManagerData()
	{
		// init vars
		$root = trim($this->app->request->get('root', 'string'), '/');
		$legalExt = str_replace(array(' ', ','), array('', '|'), $this->app->request->get('extensions', 'string'));
		$storage = $this->app->request->get('storage', 'string');

		// init storage
		switch($storage) {
			case 's3':
				$bucket 	= $this->app->request->get('bucket', 'string');
				$accesskey 	= $this->app->request->get('accesskey', 'string');
				$secretkey 	= $this->app->zlfw->crypt($this->app->request->get('key', 'string'), 'decrypt');
				$storage = new ZLStorage('AmazonS3', array('secretkey' => $secretkey, 'accesskey' => $accesskey, 'bucket' => $bucket));
				break;

			default:
				$storage = new ZLStorage('Local');
				break;
		}
		
		// retrieve tree
		$tree = $storage->getTree($root, $legalExt);
		
		/* Array of database columns which should be read and sent back to DataTables. Use a space where
		   you want to insert a non-database field (for example a counter or static image) */
		$aColumns = array('type', 'name', 'size', 'path');

		// JSON
		$JSON = array(
			'iTotalRecords' => 40,
			'iTotalDisplayRecords' => 40,
			'sColumns' => implode(', ', $aColumns),
			'root' => $tree['root'],
			'aaData' => $tree['rows']
		);

		echo json_encode($JSON);
	}

	/*
		Function: deleteObject
			Delete the Object
			
		Request parameters:
			$path: the relative path to the object
			$storage: the storage related information
	*/
	public function deleteObject()
	{
		// init vars
		$path = $this->app->request->get('path', 'string', '');
		$storage = $this->app->request->get('storage', 'string');
		$result = false;

		// init storage
		switch($storage) {
			case 's3':
				$bucket 	= $this->app->request->get('bucket', 'string');
				$accesskey 	= $this->app->request->get('accesskey', 'string');
				$secretkey 	= $this->app->zlfw->crypt($this->app->request->get('key', 'string'), 'decrypt');
				$storage = new ZLStorage('AmazonS3', array('secretkey' => $secretkey, 'accesskey' => $accesskey, 'bucket' => $bucket));
				break;

			default:
				$storage = new ZLStorage('Local');
				break;
		}

		// proceed
		$result = $storage->delete($path);

		// get any error / warning
		$errors = array_merge($storage->getErrors(), $storage->getWarnings());

		echo json_encode(compact('result', 'errors'));
	}

	/*
		Function: moveObject
			Move the Object
			
		Request parameters:
			$src: the relative path to the source object
			$dest: the relative path to the destination object
			$storage: the storage related information
	*/
	public function moveObject()
	{
		// init vars
		$src = $this->app->request->get('src', 'string', '');
		$dest = $this->app->request->get('dest', 'string', '');
		$storage = $this->app->request->get('storage', 'string');
		$result = false;

		// clean the destination path
		$dest = dirname($dest) . '/' . $this->app->zlfilesystem->makeSafe(basename($dest), 'ascii');

		// init storage
		switch($storage) {
			case 's3':
				$bucket 	= $this->app->request->get('bucket', 'string');
				$accesskey 	= $this->app->request->get('accesskey', 'string');
				$secretkey 	= $this->app->zlfw->crypt($this->app->request->get('key', 'string'), 'decrypt');

				// workaround when object is on root
				$dest = preg_replace('/^(\.\/)/', '', $dest);

				// workaround to get back the slash if the object is folder
				if (preg_match('/\/$/', $src)) $dest = $dest . '/';

				// construct storage
				$storage = new ZLStorage('AmazonS3', array('secretkey' => $secretkey, 'accesskey' => $accesskey, 'bucket' => $bucket));
				break;

			default:
				$storage = new ZLStorage('Local');
				break;
		}

		// proceed
		$result = $storage->move($src, $dest);

		// get any error / warning
		$errors = array_merge($storage->getErrors(), $storage->getWarnings());

		// get final name
		$name = basename($dest);

		echo json_encode(compact('result', 'errors', 'name'));
	}
	
	/*
		Function: newfolder
			Create new Folder
			
		Parameters:
			$path: parent folder path
	*/
	public function newFolder()
	{
		// init vars
		$path = $this->app->request->get('path', 'string', '');
		$storage = $this->app->request->get('storage', 'string');
		$result = false;

		// clean the destination path
		$path = dirname($path) . '/' . $this->app->zlfilesystem->makeSafe(basename($path), 'ascii');

		// init storage
		switch($storage) {
			case 's3':
				$bucket 	= $this->app->request->get('bucket', 'string');
				$accesskey 	= $this->app->request->get('accesskey', 'string');
				$secretkey 	= $this->app->zlfw->crypt($this->app->request->get('key', 'string'), 'decrypt');

				// workaround when object is on root
				$path = preg_replace('/^(\.\/)/', '', $path);

				// workaround to get back the slash
				$path = $path . '/';

				// construct storage
				$storage = new ZLStorage('AmazonS3', array('secretkey' => $secretkey, 'accesskey' => $accesskey, 'bucket' => $bucket));
				break;

			default:
				$storage = new ZLStorage('Local');
				break;
		}

		// proceed
		$result = $storage->createFolder($path);

		// get any error / warning
		$errors = array_merge($storage->getErrors(), $storage->getWarnings());

		// get final name
		$name = basename($path);

		echo json_encode(compact('result', 'errors', 'name'));
	}

	/*
		Function: validateObjectName
			Validate the name for uploading
			
		Parameters:
			$name: file name
	*/
	public function validateObjectName()
	{
		// init vars
		$name = $this->app->request->get('name', 'string', '');

		// convert to ASCII		
		$result = $this->app->zlfilesystem->makeSafe($name, 'ascii');

		// lowercase the extension
		$result = JFile::stripExt($result) . '.' . strtolower( JFile::getExt($result) );

		// return result
		echo json_encode(compact('result'));
	}

	/**
	 * Plupload Upload Function
	 *
	 * Original Credits:
	 * Copyright 2009, Moxiecode Systems AB
	 * Released under GPL License.
	 * License: http://www.plupload.com/license
	 * 
	 * Adapted to ZOO by ZOOlanders.com
	 * Copyright (C) JOOlanders, SL
	 */
	public function upload()
	{
		// Make sure file is not cached (as it happens for example on iOS devices)
		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		header("Cache-Control: no-store, no-cache, must-revalidate");
		header("Cache-Control: post-check=0, pre-check=0", false);
		header("Pragma: no-cache");

		// 5 minutes execution time
		@set_time_limit(5 * 60);

		// Uncomment this one to fake upload time
		// usleep(5000);

		// Settings
		$targetDir = ini_get("upload_tmp_dir") . DIRECTORY_SEPARATOR . "plupload";
		$cleanupTargetDir = true; // Remove old files
		$maxFileAge = 5 * 3600; // Temp file age in seconds

		// get filename and make it websafe
		$fileName = isset($_REQUEST["name"]) ? $_REQUEST["name"] : uniqid("file_");
		$fileName = $this->app->zlfilesystem->makeSafe(JRequest::getVar("name", ''), 'ascii');

		// Create target dir
		if (!file_exists($targetDir)) {
			@mkdir($targetDir);
		}

		// get file path
		$filePath = $targetDir . DIRECTORY_SEPARATOR . $fileName;

		// get chunking
		$chunking = isset($_REQUEST["offset"]) && isset($_REQUEST["total"]);


		// Remove old temp files	
		if ($cleanupTargetDir) {
			if (!is_dir($targetDir) || !$dir = opendir($targetDir)) {
				die('{"jsonrpc" : "2.0", "error" : {"code": 100, "message": "Failed to open temp directory."}, "id" : "id"}');
			}

			while (($file = readdir($dir)) !== false) {
				$tmpfilePath = $targetDir . DIRECTORY_SEPARATOR . $file;

				// If temp file is current file proceed to the next
				if ($tmpfilePath == "{$filePath}.part") {
					continue;
				}

				// Remove temp file if it is older than the max age and is not the current file
				if (preg_match('/\.part$/', $file) && (filemtime($tmpfilePath) < time() - $maxFileAge)) {
					@unlink($tmpfilePath);
				}
			}
			closedir($dir);
		}	

		// Open temp file
		if (!$out = @fopen("{$filePath}.part", $chunking ? "cb" : "wb")) {
			die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
		}

		if (!empty($_FILES)) {
			if ($_FILES['file']['error'] || !is_uploaded_file($_FILES['file']['tmp_name'])) {
				die('{"jsonrpc" : "2.0", "error" : {"code": 103, "message": "Failed to move uploaded file."}, "id" : "id"}');
			}

			// Read binary input stream and append it to temp file
			if (!$in = @fopen($_FILES['file']['tmp_name'], "rb")) {
				die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
			}
		} else {	
			if (!$in = @fopen("php://input", "rb")) {
				die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
			}
		}

		if ($chunking) {
			fseek($out, $_REQUEST["offset"]); // write at a specific offset
		}

		while ($buff = fread($in, 4096)) {
			fwrite($out, $buff);
		}

		@fclose($out);
		@fclose($in);

		// Check if file has been uploaded
		if (!$chunking || filesize("{$filePath}.part") >= $_REQUEST["total"]) {
			// Strip the temp .part suffix off 
			rename("{$filePath}.part", $filePath);
		}

		// get paths for the final destination
		$path = $this->app->request->get('path', 'string', '');
		$dest = JPATH_ROOT . '/' . $path . '/' . basename($filePath);

		// move to the final destination
		$storage = new ZLStorage('Local', array('s3' => 'options'));
		$result = $storage->upload($filePath, $dest);

		// if fails
		if (!$result) die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "' . $result . '"}, "id" : "id"}');

		// Return Success JSON-RPC response
		die(json_encode(array('jsonrpc' => '2.0', 'result' => $result, 'id' => 'id')));
	}
}

/*
	Class: ZoolandersControllerException
*/
class ZluxControllerException extends AppException {}