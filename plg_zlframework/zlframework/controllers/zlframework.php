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
class ZlframeworkController extends AppController {

	public function __construct($default = array())
	{
		parent::__construct($default);

		// set table
		$this->itemTable = $this->app->table->item;

		// get application
		$this->application = $this->app->zoo->getApplication();
	}

	/*
		Function: loadZLfield
			Load an entire ZL Field trough ajax

		Returns:
			JSON
	*/
	public function loadZLfield()
	{
		// init vars
		$control_name 			= $this->app->request->get('control_name', 'string', '');
		$json_path	 			= $this->app->request->get('json_path', 'string', '');
		$group	 				= $this->app->request->get('group', 'string', '');
		$type	 				= $this->app->request->get('type', 'string', '');
		$element_id	 			= $this->app->request->get('element_id', 'string', '');
		$element_type 			= $this->app->request->get('element_type', 'string', '');
		$enviroment 			= $this->app->request->get('enviroment', 'string', '');
		$node 					= $this->app->request->get('node', 'array', '');
		$params					= $this->app->data->create(array());

		// create application object
		$application = $this->app->object->create('Application');
		$application->setGroup($group);

		// get type object
		$type = $application->getType($type);

		// get element object
		$element = $type->getElement($element_id);

		// if new element, create empty one
		if(!isset($element) || !is_object($element)){
			$element = $this->app->element->create($element_type, $application);
			$element->identifier = $element_id;
			$element->config = $this->app->data->create(array());
		}

		// get json string
		$json = include($this->app->path->path($json_path));

		// set arguments
		$arguments = array('element' => $element, 'node' => $node['@attributes']);

		// render
		$result = $this->app->zlfield->parseJSON((array)$json, $control_name, array(), '', false, $arguments);

		// return to the ajax request
		echo json_encode( array('result' => $result) );
		return;
	}

	/*
		Function: loadField
			Load specific field

		Returns:
			JSON
	*/
	public function loadField()
	{
		$psv	= $this->app->request->get('psv', 'array', array()); // Parents Value
		$pid  	= $this->app->request->get('pid', 'string', ''); // Parent Value
		$json	= $this->app->request->get('json', 'string', ''); // json paths or json array
		$node	= $this->app->request->get('node', 'array', array());
		$ctrl   = $this->app->request->get('ctrl', 'string', '');
		$args	= $this->app->request->get('args', 'string', ''); // in json format

		// decode
		$json = json_decode($json, true);
		$args = (array)json_decode($args, true);

		if(isset($json['paths'])){
			$value = isset($psv[$pid]) ? $psv[$pid] : 'default';
			// replace any .php in midle of the path and the value with parent selection
			$json['paths'] = preg_replace(array('/{value}/', '/\.php\//'), array($value, '/'), $json['paths']);
		}

		// return to ajax request
		echo json_encode( array('result' => $this->app->zlfield->parseJSON((array)$json, $ctrl, $psv, $pid, false, $args)) );
		return;
	}
	
	/*
		Function: renderView
			Renders an Item View

	   Parameters:
            $item - the Item Object
			$layoutName - the Item layout
			
		Returns:
			String - html

	*/
	public function renderView()
	{
		$item 	= $this->app->request->get('item_id', 'string', '');
		$layout = $this->app->request->get('item_layout', 'string', 'full');
		
		echo $this->app->zlfw->renderView($item, $layout);
		return;
	}
	
	/*
		Function: callElement
			CallElement for AJAX requests
	*/
	public function callElement()
	{
		// get request vars
		$el_identifier  = $this->app->request->getString('elm_id', '');
		$item_id		= $this->app->request->getInt('item_id', 0);
		$type	 		= $this->app->request->getString('type', '');
		$this->method 	= $this->app->request->getCmd('method', '');
		$this->args     = $this->app->request->getVar('args', array(), 'default', 'array');

		JArrayHelper::toString($this->args);

		// load element
		if ($item_id) {
			$item = $this->itemTable->get($item_id);
		} elseif (!empty($type)){
			$item = $this->app->object->create('Item');
			$item->application_id = $this->application->id;
			$item->type = $type;
		} else {
			return;
		}

		// execute callback method
		if ($element = $item->getElement($el_identifier)) {
			echo $element->callback($this->method, $this->args);
		}
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
			return;
		}

		if(isset($post['elements'][$el_identifier]) && $item->getElement($el_identifier))
		{
			$item = $this->itemTable->get($item_id);

			$item->elements->set($el_identifier, $post['elements'][$el_identifier]);
			$this->itemTable->save($item);
		}
	}

	/*
		Function: JSONfiles
			Get directory/file list JSON formatted

		Returns:
			JSON object
	*/
	public function JSONfiles()
	{
		$legalExt 		= ''; // mp3|mp4
		$path 			= trim($this->app->request->get('path', 'string'), '/');
		$root 			= $this->app->zlfw->path->getDirectory($path); // if empty, will return joomla image folder
		$items 			= array();

		// dirs
		foreach ($this->app->path->dirs("root:$root") as $dir)
		{
			$items[] = array('name' => basename($dir), 'type' => 'folder', 'path' => "$root/$dir");
		}

		// files
		foreach ($this->app->path->files("root:$root", false, '/^.*('.$legalExt.')$/i') as $file)
		{
			$path = "$root/$file";
			$size = $this->app->zlfw->filesystem->getSourceSize($path);
			$items[] = array('name' => basename($file), 'type' => 'file', 'path' => $path, 'size' => $size);
		}
		
		echo json_encode($items);
	}

	/*
		Function: callModule
			callModule for AJAX requests
	*/
	public function callModule()
	{
		// init vars
		$module_id = $this->app->request->getInt('module_id', 0);
		$db = JFactory::getDbo();

		// get module params
		$query = $db->getQuery(true);
		$query->select('m.params')->from('#__modules AS m')->where('m.id = '.$module_id);
		$params = $this->app->data->create(json_decode($db->setQuery($query)->loadResult(), true));

		// get module base name
		$query = $db->getQuery(true);
		$query->select('m.module')->from('#__modules AS m')->where('m.id = '.$module_id);
		$module = $db->setQuery($query)->loadResult();

		// load helper
		require_once(JPATH_ROOT.'/modules/'.$module.'/helper.php');

		// get and return items
		$items = call_user_func_array(array($module.'Helper', 'callback'), compact('params'));
		echo json_encode($items);
	}
}

/*
	Class: ZoolandersControllerException
*/
class ZlframeworkControllerException extends AppException {}