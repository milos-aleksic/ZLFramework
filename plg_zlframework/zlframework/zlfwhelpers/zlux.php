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
   Class: zlfwHelperZLUX
   	  A class that contains ZLUX functions
*/
class zlfwHelperZLUX extends AppHelper {

	/**
	 * Load ZLUX related Assets
	 *
	 * @param string $plugins	Comma separated list of extra plugins to load
	 *
	 * @since 3.0.14
	 */
	public function loadAssets($plugins = array())
	{
		// prepare array of plugins
		if (!is_array($plugins)) {
			$plugins = str_replace(' ', '', $plugins);
			$plugins = explode(',', $plugins);
		}

		// Items/Files manager
		if (in_array('ItemsManager', $plugins) || in_array('FilesManager', $plugins)) {

			// ZLUX Core
			$this->loadZLUXcore();

			// plupload
			$this->app->document->addScript('zlfw:zlux/assets/plupload/plupload.full.min.js');

			// dataTables
			if(!JDEBUG){
				$this->app->document->addScript('zlfw:zlux/assets/datatables/dataTables.with.plugins.min.js');
			} else {
				// Site in debug Mode
				$this->app->document->addScript('zlfw:zlux/assets/datatables/dataTables.js'); // when developing
				$this->app->document->addScript('zlfw:zlux/assets/datatables/dataTables.plugins.js'); // when developing
			}

			// perfect scrollbar
			$this->app->document->addStylesheet('zlfw:zlux/assets/perfect-scrollbar/perfect-scrollbar.min.css');
			$this->app->document->addScript('zlfw:zlux/assets/perfect-scrollbar/perfect-scrollbar.with-mousewheel.min.js');

			// ZL Bootstrap
			$this->loadBootstrap(true);
		}

		// ZL Bootstrap
		if (in_array('Bootstrap', $plugins)) 
			$this->loadBootstrap();
	}

	/**
	 * Load ZLUX core assets
	 *
	 * @since 3.0.14
	 */
	public function loadZLUXcore()
	{
		$this->app->document->addStylesheet('zlfw:zlux/zlux.css');
		// $this->app->document->addScript('zlfw:zlux/zlux.all.min.js');
		
		// TODO: Do this as in loadAssets() with JDebug

		// when developing
		$this->app->document->addScript('zlfw:zlux/zluxMain.js');
		$this->app->document->addScript('zlfw:zlux/zluxDialog.js');
		$this->app->document->addScript('zlfw:zlux/zluxFilesManager.js');
		$this->app->document->addScript('zlfw:zlux/zluxItemsManager.js');
	}

	/**
	 * Load ZL Bootstrap assets
	 *
	 * @since 3.0.14
	 */
	public function loadBootstrap($loadJS = false)
	{
		$this->app->document->addStylesheet('zlfw:zlux/zlbootstrap/css/bootstrap-zl.min.css');
		$this->app->document->addStylesheet('zlfw:zlux/zlbootstrap/css/bootstrap-zl-responsive.min.css');

		// if stated load JS too
		if ($loadJS) 
			$this->app->document->addScript('zlfw:zlux/zlbootstrap/js/bootstrap.min.js');
	}
}