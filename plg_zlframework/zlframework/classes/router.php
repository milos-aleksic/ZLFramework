<?php
/**
* @package		ZL Framework
* @author    	ZOOlanders http://www.zoolanders.com
* @copyright 	Copyright (C) JOOlanders SL
* @license   	http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

/**
 * The ZL router class
 */
class zlRouter {

	/**
	 * A reference to the global App object
	 *
	 * @var App
	 */
	public $app;

	/**
	 * The parsed menu items
	 * @var array
	 */
	protected $_menu_items;

	/**
	 * The route cache
	 * @var AppCache
	 */
	protected $_cache;

	/**
	 * The active menu item id
	 * @var string
	 */
	protected $_active_menu_item_id;

	/**
	 * Class constructor
	 *
	 * @param string $app App instance.
	 */
	public function __construct($app)
	{
		// set application
		$this->app = $app;

		// cache
		if ($this->app->get('cache_routes', false)) {
			// get route cache
			// refreshes after one hour automatically
			$this->_cache = $this->app->cache->create($this->app->path->path('cache:') . '/routes', true, 3600, 'apc');
			if (!$this->_cache || !$this->_cache->check()) {
				$this->_cache = null;
			} else {
				$this->_find(null, null);
				$key = json_encode($this->_menu_items);
				if (!$this->_cache->get($key)) {
					$this->_cache->clear()->set($key, true)->save();
				}
			}
		}

		// save default menu
		if ($menu_item = $this->app->menu->getActive()) {
			$this->_active_menu_item_id = $menu_item->id;
		}
	}

	/**
	 * Route building
	 */
	public function buildRoute(&$query, &$segments) {}
	
	/**
	 * Route parsing
	 */
	public function parseRoute(&$segments, &$vars) {}

	/**
	 * Clears the router cache
	 */
	public function clearCache() {
		if ($this->_cache) {
			$this->_cache->clear()->save();
		}
	}

	/**
	 * Gets this route helpers link base
	 *
	 * @return string the link base
	 */
	public function getLinkBase() {
		return 'index.php?option='.$this->app->component->com_zoolanders->name;
	}
}