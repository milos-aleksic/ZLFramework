<?php
/**
* @package		ZL Framework
* @author    	JOOlanders, SL http://www.zoolanders.com
* @copyright 	Copyright (C) JOOlanders, SL
* @license   	http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
*/

// register Element class
App::getInstance('zoo')->loader->register('Element', 'elements:element/element.php');

/*
	Class: ElementPro
		The Element Pro abstract class
*/
abstract class ElementPro extends Element {

	/*
	   Function: Constructor
	*/
	public function __construct() {

		// call parent constructor
		parent::__construct();

		// set callbacks
		$this->registerCallback('returndata');
	}
	
	/*
		Function: setType
			Set related type object.
	 		Added a checkInstallation call to allow for extra steps of checkin installation
	 		on advanced elements. Here and not in the constructor to be sure to have type and
	 		therefore config available

		Parameters:
			$type - type object

		Returns:
			Void
	*/
	public function setType($type) {
		parent::setType($type);
		
		$this->checkInstallation();
	}
	
	/*
		Function: checkInstallation
			Allow for extra steps of checkin installation
	 		on advanced elements. 

		Returns:
			Void
	*/
	protected function checkInstallation(){
		
	}
	
	/*
		Function: getLayout
			Get element layout path and use override if exists.

		Returns:
			String - Layout path
	*/
	public function getLayout($layout = null) {

		// init vars
		$type = $this->getElementType();

		// set default
		if ($layout == null) {
			$layout = "default.php";
		}

		// find layout
		if ($path = $this->app->path->path("elements:{$type}/tmpl/{$layout}")){
			return $path;	
		}
		
		// if layout not found, search on pro element
		return $this->app->path->path("elements:pro/tmpl/{$layout}");
	}
	
	/*
		Function: returnData
			Renders the element data - use for ajax requests
	*/
	public function returnData($layout, $separator = '', $filter = '', $specific = '') {
		$layout = json_decode($layout, true); $separator = json_decode($separator, true); $filter = json_decode($filter, true); $specific = json_decode($specific, true);
		$params = compact('layout', 'separator', 'filter', 'specific');
		return $this->render($params);
	}
	
	/*
		Function: render
			Renders the element.

	   Parameters:
            $params - AppData render parameter

		Returns:
			String - html
	*/
	public function render($params = array()) {
		$params = $this->app->data->create($params);
		
		// render layout
		if ($layout = $this->getLayout('render/'.$params->find('layout._layout', 'default.php'))) {
			return $this->renderLayout($layout, compact('params'));
		}
	}
    
}