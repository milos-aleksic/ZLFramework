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
	Class: zlfwHelperEnviroment
		The ZLFW enviroment helper class
*/
class zlfwHelperEnviroment extends AppHelper {

	public $joomla;
	public $app;

	/*
	   Function: Constructor
	*/
	public function __construct($app) {

		// call parent constructor
		parent::__construct($app);

		// set Joomla instance
		$this->joomla = JFactory::getApplication();
	}

	/*
		Function: get
			returns the current enviroment

		Enviroments:
			Item Full View - site.com_zoo.item

		Returns:
			string - the enviroment route
	*/
	public function get()
	{
		// init vars
		$enviroment = array();
		$params = array();
		$Itemid = $this->app->request->getCmd('Itemid', null);
		$task = $this->app->request->getCmd('task', '');
		$view = $this->app->request->getCmd('view', '');

		// set back or frontend
		$enviroment[] = $this->joomla->isAdmin() ? 'admin' : 'site';

		// set extension
		$enviroment[] = $this->app->request->getCmd('option', null);

		// if zoo item full view
		if ($task == 'item') {
			$enviroment[] = 'item';
			$params['item_id'] = $this->app->request->getCmd('item_id');
		} else if ($view == 'item') { // if joomla item menu route
			$enviroment[] = 'item';
			$params['item_id'] = $this->joomla->getMenu()->getItem($Itemid)->params->get('item_id');
		}

		// if zoo cat
		if ($task == 'category') {
			$enviroment[] = 'category';
			$params['category_id'] = $this->app->request->getCmd('category_id');
		} else if ($view == 'category') { // if joomla item menu route
			$enviroment[] = 'category';
			$params['category_id'] = $this->joomla->getMenu()->getItem($Itemid)->params->get('category');
		}

		// clean values
		$enviroment = array_filter($enviroment);

		// set point format
		$enviroment = implode('.', $enviroment);

		// save params in session
		$this->app->system->session->set($enviroment, $params);

		return $enviroment;
	}
	
	/*
		Function: getParams
			get the enviroment related params

		Returns:
			array
	*/
	public function getParams($enviroment)
	{
		return $this->app->data->create($this->app->system->session->get($enviroment));
	}

	/*
		Function: is
			checks if the passed enviroment is the current enviroment

		Returns:
			boolean
	*/
	public function is($enviroment)
	{
		return strpos($this->get(), $enviroment) === 0;
	}
}