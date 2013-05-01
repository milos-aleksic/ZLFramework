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
	 * Load ZLUX Items Manager assets
	 *
	 * @since 3.0.14
	 */
	public function loadItemsManagerAssets()
	{
		// ZLUX Main
		$this->loadMainAssets();

		// dataTables
		if(!JDEBUG){
			$this->app->document->addScript('zlfw:zlux/assets/datatables/dataTables.with.plugins.min.js');
		} else {
			// Site in debug Mode
			$this->app->document->addScript('zlfw:zlux/assets/datatables/dataTables.js');
			$this->app->document->addScript('zlfw:zlux/assets/datatables/dataTables.plugins.js');
		}

		// perfect scrollbar
		$this->app->document->addStylesheet('zlfw:zlux/assets/perfect-scrollbar/perfect-scrollbar.min.css');
		$this->app->document->addScript('zlfw:zlux/assets/perfect-scrollbar/perfect-scrollbar.with-mousewheel.min.js');
	}

	/**
	 * Load ZLUX Files Manager assets
	 *
	 * @since 3.0.14
	 */
	public function loadFilesManagerAssets()
	{
		// ZLUX Core
		$this->loadMainAssets();

		// dataTables
		if(!JDEBUG) {
			$this->app->document->addScript('zlfw:zlux/assets/datatables/dataTables.with.plugins.min.js');
		} else {
			// Site in debug Mode
			$this->app->document->addScript('zlfw:zlux/assets/datatables/dataTables.js');
			$this->app->document->addScript('zlfw:zlux/assets/datatables/dataTables.plugins.js');
		}

		// plupload
		$this->app->document->addScript('zlfw:zlux/assets/plupload/plupload.full.min.js');

		// perfect scrollbar
		$this->app->document->addStylesheet('zlfw:zlux/assets/perfect-scrollbar/perfect-scrollbar.min.css');
		$this->app->document->addScript('zlfw:zlux/assets/perfect-scrollbar/perfect-scrollbar.with-mousewheel.min.js');
	}

	/**
	 * Load ZLUX Main assets
	 *
	 * @since 3.0.14
	 */
	public function loadMainAssets()
	{
		if(!JDEBUG) {
			$this->app->document->addStylesheet('zlfw:zlux/zlux.css');
			$this->app->document->addScript('zlfw:zlux/zlux.all.min.js');
		} else {
			// Site in debug Mode
			$this->app->document->addStylesheet('zlfw:zlux/zlux.css');
			$this->app->document->addScript('zlfw:zlux/zluxMain.js');
			$this->app->document->addScript('zlfw:zlux/zluxDialog.js');
			$this->app->document->addScript('zlfw:zlux/zluxFilesManager.js');
			$this->app->document->addScript('zlfw:zlux/zluxItemsManager.js');
		}

		// ZL Bootstrap
		$this->loadBootstrap(true);
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