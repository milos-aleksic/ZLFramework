<?php
/**
* @package		ZL Framework
* @author    	ZOOlanders http://www.zoolanders.com
* @copyright 	Copyright (C) JOOlanders SL
* @license   	http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

/*
	Class: Fieldset
		The Fieldset abstract class
*/
abstract class Fieldset {

	/* ZOO app */
	public $app;

	/* The Fieldset config data */
	public $config;

	/* The Fieldset name */
	public $name;

	/* The type of the Fieldset */
	protected $_type;

	/* The Element object reference */
	protected $_element;

	/*
		Function: Constructor
			Class Constructor.

		Parameters:
			$app - App A reference to an App Object
	*/
	public function __construct($app, $element) {

		// set application
		$this->app = $app;

		// set default name
		$this->_type = strtolower(str_replace('Fieldset', '', get_class($this)));

		// set element object
		$this->_element = $element;

		// set config data
		$this->config = $element->config;
	}

	/*
		Function: getElement
			Get related element object.

		Returns:
			Element - element object
	*/
	public function getElement() {
		return $this->_element;
	}

	/*
		Function: getItem
			Get related item object.

		Returns:
			Item - item object
	*/
	public function getItem() {
		return $this->_element->getItem();
	}

	/*
		Function: getFieldsetType
			Gets the fieldset type.

		Returns:
			string - the fieldset type
	*/
	public function getFieldsetType() {
		return $this->_type;
	}

	/*
		Function: getFieldsetName
			Gets the fieldset name.

		Returns:
			string - the fieldset name
	*/
	public function getFieldsetName() {
		return $this->name ? $this->name : ucfirst($this->getFieldsetType());
	}

	/*
		Function: get
			Gets the elements data.

		Returns:
			Mixed - the elements data
	*/
	public function get($key, $default = null) {
		return $this->_element->get($key, $default);
	}

	/*
		Function: set
			Sets the elements data.

		Returns:
			Element - this
	*/
	public function set($key, $value) {
		return $this->_element->set($key, $value);
	}

	/*
		Function: getControlName
			Gets the controle name for given name.

		Returns:
			String - the control name
	*/
	public function getControlName($name, $array = false) {
		return $this->_element->getControlName($name, $array);
	}

	/*
		Function: getLayout
			Get element layout path and use override if exists.

		Returns:
			String - Layout path
	*/
	public function getLayout($layout = null) {

		// init vars
		$type = $this->getFieldsetType();

		// set default
		if ($layout == null) {
			$layout = "default.php";
		}

		// find layout in element root
		if ($path = $this->app->path->path("fieldsets:{$type}/{$layout}")) {
			return $path;	
		}
	}

	/*
		Function: hasValue
			Checks if the fieldset value is set

		Parameters:
			$params - render parameter

		Returns:
			Boolean - true, on success
	*/
	public function hasValue($params = array())
	{
		$value = $this->get('value');
		
		return !empty($value);
	}

	/*
		Function: renderLayout
			Renders the element using template layout file.

	   Parameters:
            $__layout - layouts template file
	        $__args - layouts template file args

		Returns:
			String - html
	*/
	public function renderLayout($__layout, $__args = array())
	{
		// init vars
		if (is_array($__args)) {
			foreach ($__args as $__var => $__value) {
				$$__var = $__value;
			}
		}

		// render layout
		$__html = '';
		ob_start();
		include($__layout);
		$__html = ob_get_contents();
		ob_end_clean();

		return $__html;
	}

 	/*
		Function: edit
			Renders the edit form field.

		Returns:
			String - html
	*/
	public function edit()
	{
		// render layout
		if ($layout = $this->getLayout('edit/layout.php')) {
			return $this->renderLayout($layout);
		}
	}

 	/*
		Function: render
			Renders the fieldset values

		Returns:
			String - html
	*/
	public function render($params = array())
	{
		return $this->get('value', array());
	}


	/*
		Function: validateSubmission
			Validates the submitted fieldset

		Parameters:
			$value  - AppData value
			$params - AppData submission parameters

		Returns:
			Array - cleaned value
	*/
	public function validateSubmission($values, $params)
	{
		// init vars
		$required = $params->get('required');

		// validate
		$value = $this->app->validator->create('string', array('required' => $required))->clean($values->get('value'));

		// important, validate fieldset type
		$fieldset = $this->getFieldsetType();

		return compact('value', 'fieldset');
	}

	/*
		Function: bindData
			Set data through data array.

		Parameters:
			$data - array

		Returns:
			Void
	*/
	public function bindData(){}
}