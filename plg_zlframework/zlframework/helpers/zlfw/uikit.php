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
	Class: zlfwHelperUikit
		A class that contains uikit functions
*/
class zlfwHelperUikit extends AppHelper {

	/**
	 * Load ZLUX Main assets
	 */
	public function loadMainAssets()
	{
		$this->app->document->addStylesheet('zlfw:assets/libraries/uikit/css/uikit.almost-flat.min.css');
		$this->app->document->addScript('zlfw:assets/libraries/uikit/js/uikit.min.js');
	}
}