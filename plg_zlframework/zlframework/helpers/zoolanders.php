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
 Class: ZOOlanders Helper
 The ZOOlanders helper Class for zoo
 */
class zoolandersHelper extends AppHelper
{
	/**
	 * Save wrapped params in the #__extensions com_zoolanders params field
	 *
	 * @param string $wrapper The params wrapper name
	 * @param mixed $params An array or object of params
	 *
	 * @return @boolean True on success
	 */
	public function setConfig($wrapper, $params)
	{
		// basic check
		if (!isset($wrapper)) return false;

		// set and save
		$this->app->component->com_zoolanders->set($wrapper, $params);
		$this->app->component->com_zoolanders->save();

		return true;
	}

	/**
	 * Retrieve the wrapped params from the #__extensions com_zoolanders params
	 *
	 * @param string $wrapper The params wrapper name
	 *
	 * @return @object The params data object
	 */
	public function getConfig($wrapper)
	{
		// retrieve and return
		return $this->app->data->create($this->app->component->com_zoolanders->get($wrapper));
	}
}