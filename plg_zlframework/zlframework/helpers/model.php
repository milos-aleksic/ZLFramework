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
	Class: ModelHelper
		Helper for models
*/
class ZlModelHelper extends AppHelper {

	/* prefix */
	protected $_prefix;

	/* models */
	protected $_models = array();
    
	/*
		Function: __construct
			Class Constructor.
	*/
	public function __construct($app) {
		parent::__construct($app);

		// set table prefix
		$this->_prefix = 'ZLModel';
	}

	/*
		Function: get
			Retrieve a model

		Parameters:
			$name - Model name
			$prefix - Model prefix

		Returns:
			Mixed
	*/
	public function get($name, $prefix = null) {
		
		// set prefix
		if ($prefix == null) {
			$prefix = $this->_prefix;
		}
		
		// load class
		$class = $prefix . $name;
		
		$this->app->loader->register($class, 'models:'.strtolower($name).'.php');
		
		// add model, if not exists
		if (!isset($this->_models[$name])) {
			$this->_models[$name] = ZLModel::getInstance($name, $prefix);
		}

		return $this->_models[$name];
	}

	/*
		Function: getNew
			Retrieve a new instance model

		Parameters:
			$name - Model name
			$prefix - Model prefix

		Returns:
			Mixed
	*/
	public function getNew($name, $prefix = null)
	{
		// set prefix
		if ($prefix == null) {
			$prefix = $this->_prefix;
		}

		// register class
		$class = $prefix.$name;
		$this->app->loader->register($class, 'models:'.strtolower($name).'.php');

		return ZLModel::getInstance($name, $prefix);
	}
	
	/*
		Function: __get
			Retrieve a model

		Parameters:
			$name - Model name

		Returns:
			Mixed
	*/
	public function __get($name) {
		return $this->get($name);
	}
	
}