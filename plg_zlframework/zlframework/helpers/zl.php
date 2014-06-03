<?php
/**
* @package		ZOOlanders
* @author    	ZOOlanders http://www.zoolanders.com
* @copyright 	Copyright (C) JOOlanders SL
* @license   	http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

/**
 * ZL Helper
 *
 * The general ZL helper Class for zoo
 */
class zlHelper extends AppHelper {

	/* prefix */
	protected $_prefix;

	/* models */
	protected $_helpers = array();
	
	/**
	 * Class Constructor
	 */
	public function __construct($app){
		parent::__construct($app);

		// set helper prefix
		$this->_prefix = 'zlHelper';
	}

	/**
	 * Retrieve a helper
	 *
	 * @param string $name Helper name
	 * @param string $prefix Helper prefix
	 *
	 * @return mixed
	 */
	public function get($name, $prefix = null)
	{	
		// set prefix
		if ($prefix == null) {
			$prefix = $this->_prefix;
		}
		
		// load class
		$class = $prefix . $name;
		$this->app->loader->register($class, 'helpers:zl/'.strtolower($name).'.php');
		
		// add helper, if not exists
		if (!isset($this->_helpers[$name])) {
			$this->_helpers[$name] = class_exists($class) ? new $class($this->app) : new AppHelper($this->app, $prefix.$name);
		}

		return $this->_helpers[$name];
	}

	/**
	 * Retrieve a helper
	 *
	 * @param string $name Helper name
	 *
	 * @return mixed
	 */
	public function __get($name) {
		return $this->get($name);
	}

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

	/**
	 * Get link to component related resources
	 *
	 * @param array $query HTTP query options
	 * @param boolean $xhtml Replace & by &amp; for xml compilance
	 * @param boolean $ssl Secure state for the resolved URI. [1 => Make URI secure using global secure site URI, 0 => Leave URI in the same secure state as it was passed to the function, -1 => Make URI unsecure using the global unsecure site URI]
	 *
	 * @return string The link to the resource
	 */
	public function link($query = array(), $xhtml = true, $ssl = null) {
		return $this->app->component->com_zoolanders->link($query, $xhtml, $ssl);
	}
}