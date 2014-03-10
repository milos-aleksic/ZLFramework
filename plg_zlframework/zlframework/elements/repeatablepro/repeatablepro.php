<?php
/**
* @package		ZL Framework
* @author    	JOOlanders, SL http://www.zoolanders.com
* @copyright 	Copyright (C) JOOlanders, SL
* @license   	http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

App::getInstance('zoo')->loader->register('ElementRepeatable', 'elements:repeatable/repeatable.php');

/*
   Class: ElementRepeatablePro
       The repeatable element class
*/
abstract class ElementRepeatablePro extends ElementRepeatable {

	protected $_rendered_values = array();

	/*
	   Function: Constructor
	*/
	public function __construct() {

		// call parent constructor
		parent::__construct();

		// set callbacks
		$this->registerCallback('returndata');
		$this->registerCallback('getemptylayout');

		// load default and current language 
		$this->app->system->language->load('plg_system_zoo_zlelements_'.$this->getElementType(), JPATH_ADMINISTRATOR, 'en-GB');
		$this->app->system->language->load('plg_system_zoo_zlelements_'.$this->getElementType(), JPATH_ADMINISTRATOR);
	}
	
	/*
		Function: setType
			Set related type object.
	 		Added a checkInstallation call to allow for extra steps of checking installation
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
		Function: hasValue
			Override. Checks if the element's value is set.

	   Parameters:
			$params - render parameter

		Returns:
			Boolean - true, on success
	*/
	public function hasValue($params = array()) {
		$value = $this->getRenderedValues( $this->app->data->create($params) );
		return !empty($value['result']);
	}

	/*
		Function: getEmptyLayout
			Load Element specified Edit Layout
	*/
	public function getEmptyLayout($layout = null)
	{
		// get layout from var or request
		$layout = $layout ? $layout : $this->app->request->getString('layout', '');

		if ($layout = $this->getLayout($layout)) {
			echo preg_replace('/(elements\[\S+])\[(\d+)\]/', '$1[-1]', $this->renderLayout($layout, array('loadinglayout' => true)));
		}
	}
	
	/*
		Function: getLayout
			Get element layout path and use override if exists.

		Returns:
			String - Layout path
	*/
	public function getLayout($layout = null)
	{
		// init vars
		$type = $this->getElementType();

		// set default
		if ($layout == null) {
			$layout = "default.php";
		}

		// find layout
		if ($path = $this->app->path->path("elements:{$type}/tmpl/{$layout}"))
		{
			return $path;
		}
		else if ($path = $this->app->path->path("elements:repeatablepro/tmpl/{$layout}")) // if no specific use common layout
		{
			return $path;
		}
		
		// if no layout found, search on pro element
		return $this->app->path->path("elements:pro/tmpl/{$layout}");
	}
	
	/*
		Function: returnData
			Renders the element data - use for ajax requests
	*/
	public function returnData($layout, $separator = '', $filter = '', $specific = '') {
		$separator = json_decode($separator, true); $filter = json_decode($filter, true); $specific = json_decode($specific, true);
		$params = compact('layout', 'separator', 'filter', 'specific');
		return $this->render($params);
	}
	
	/*
		Function: loadAssets
			Load elements css/js assets.

		Returns:
			Void
	*/
	public function loadAssets() {
		if ($this->config->get('repeatable')) {
			$this->app->document->addScript('elements:repeatablepro/repeatablepro.js');
		}
		$this->app->document->addStylesheet('zlfw:assets/css/zl_ui.css');
		return $this;
	}
	
	/*
	   Function: _edit
	       Renders the repeatable edit form field.

	   Returns:
	       String - html
	*/
	protected function _edit(){
		// render layout
		$_edit = $this->config->find('specific._edit_sublayout', '_edit.php');
		return (($layout = $this->getLayout("edit/$_edit")) || ($layout = $this->getLayout("edit/_edit.php"))) ? $this->renderLayout($layout) : '';
	}
	
	/*
		Function: getRenderedValues
			render repeatable values

		Returns:
			array
	*/
	public function getRenderedValues($params=array(), $mode=false, $opts=array()) 
	{
		// create a unique hash for this element position
		$hash = md5(serialize(array(
			$opts,
			$this->getType()->getApplication()->getGroup(),
			$this->getType()->id,
			$params->get('element').$params->get('_layout'),
			$params->get('_position').$params->get('_index')
		)));

		// check for value, if not exist render it
		if (!array_key_exists($hash, $this->_rendered_values))
		{
			// of limit 0, abort
			if ($params->find('filter._limit') == '0') return null;
			
			$report = array();

			$data_is_subarray = isset($opts['data_is_subarray']) ? $opts['data_is_subarray'] : false;
		
			// render
			$result = array();
			$this->seek(0); // let's be sure is starting from first index
			foreach ($this as $self) if ($this->_hasValue($params) && $values = $this->_render($params, $mode, $opts)) {
				if($data_is_subarray) foreach ($values as $value) {
					$result[] = $value; // filespro compatibility
				} else {
					$result[] = $values;
				}
			}
			
			if (empty($result)) return null; // if no results abort
			
			// set offset/limit
			$offset = ( ($params->find('filter._offset', '') == '') || (!is_numeric($params->find('filter._offset', 0))) ) ? 0 : $params->find('filter._offset', 0);
			$limit  = ( ($params->find('filter._limit', '') == '') || (!is_numeric($params->find('filter._limit', ''))) ) ? null : $params->find('filter._limit', null);
			
			$report['limited'] = $limit != null ? $limit < count($result) : false;
			$result = array_slice($result, $offset, $limit);
			
			// set prefix/suffix
			if ($prefix = $params->find('specific._prefix')) array_unshift($result, '<span class="prefix">'.$prefix.'</span>');
			if ($suffix = $params->find('specific._suffix')) {
				if (count($result) > 1) $result[] = '<span class="suffix">'.$suffix.'</span>';
				else $result[0] .= '<span class="suffix">'.$suffix.'</span>';
			}
			
			$report['hash'] = $hash;
			$this->_rendered_values[$hash] = compact('report', 'result');
		}
		
		return $this->_rendered_values[$hash];
	}
	
	/*
		Function: render
			Renders the element.

	   Parameters:
            $params - AppData render parameter

		Returns:
			String - html
	*/
	public function render($params = array())
	{
		$params = $this->app->data->create($params);
		
		// render layout
		if ($layout = $this->getLayout('render/'.$params->find('layout._layout', 'default.php'))) {
			return $this->renderLayout($layout, compact('params'));
		} 
		else 
		{
			// for old elements
			$result = array();
			foreach ($this as $self) {
				$result[] = $this->_render($params);
			}
			return $this->app->zlfw->applySeparators($params->find('separator._by'), $result, $params->find('separator._class'));
		}
	}
	
	/*
		Function: _render
			Renders the repeatable element.

	   Parameters:
            $params - AppData render parameter

		Returns:
			String - html
	*/
	protected function _render($params = array())
	{
		// render layout or value
		$main_layout = basename($params->find('layout._layout', 'default.php'), '.php');
		if($layout = $this->getLayout('render/'.$main_layout.'/_sublayouts/'.$params->find('layout._sublayout', '_default.php'))){
			return $this->renderLayout($layout, compact('params'));
		} else {
			return $this->get('value');
		}
	}
	
	/*
		Function: _renderRepeatable
			Renders the repeatable

		Returns:
			String - output
	*/
    protected function _renderRepeatable($function, $params = array()) {
		return $this->renderLayout($this->app->path->path('elements:'.$this->getElementType().'/tmpl/edit/edit.php'), compact('function', 'params'));
    }

    	/*
		Function: getControlName
			Gets the controle name for given name.

		Returns:
			String - the control name
	*/
	public function getControlName($name, $array = false) {
		return "elements[{$this->identifier}][{$this->index()}][{$name}]" . ($array ? "[]":"");
	}

}