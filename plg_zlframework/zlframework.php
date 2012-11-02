<?php
/**
* @package		ZL Framework
* @author    	JOOlanders, SL http://www.zoolanders.com
* @copyright 	Copyright (C) JOOlanders, SL
* @license   	http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

// Import library dependencies
jimport('joomla.plugin.plugin');
jimport('joomla.filesystem.file');

class plgSystemZlframework extends JPlugin {
	
	public $app;
	public $zoo;

	/**
	 * onAfterInitialise handler
	 *
	 * Adds ZOO event listeners
	 *
	 * @access	public
	 * @return null
	 */
	function onAfterInitialise()
	{
		// make sure ZOO exist
		if (!JFile::exists(JPATH_ADMINISTRATOR.'/components/com_zoo/config.php')
				|| !JComponentHelper::getComponent('com_zoo', true)->enabled) {
			return;
		}
		
		// load zoo
		require_once(JPATH_ADMINISTRATOR.'/components/com_zoo/config.php');
		
		// check if Zoo > 2.4 is loaded
		if (!class_exists('App')) {
			return;
		}
		
		// Get the Joomla and ZOO App instance
		$this->app = JFactory::getApplication();
		$this->zoo = App::getInstance('zoo');
		
		// load default and current language
		$this->zoo->system->language->load('plg_system_zlframework', JPATH_ADMINISTRATOR, 'en-GB', true);
		$this->zoo->system->language->load('plg_system_zlframework', JPATH_ADMINISTRATOR, null, true);
		
		// register plugin path
		$plg_path = $this->zoo->joomla->isVersion('1.5') ? 'plugins/system/zlframework/' : 'plugins/system/zlframework/zlframework/';
		if ( $path = $this->zoo->path->path( 'root:'.$plg_path ) ) {
			$this->zoo->path->register($path, 'zlfw');
		}
		
		// register elements fields
		if ( $path = $this->zoo->path->path( 'zlfw:zlfield' ) ) {
			$this->zoo->path->register($path, 'zlfield'); // used since ZLFW 2.5.8
			$this->zoo->path->register($path.'/fields/elements', 'zlfields'); // temporal until all ZL Extensions adapted
			$this->zoo->path->register($path.'/fields/elements', 'fields'); // necessary since ZOO 2.5.13
		}
		
		// register elements - order is important!
		if ( $path = $this->zoo->path->path( 'zlfw:elements' ) ) {
			$this->zoo->path->register($path, 'elements'); // register elements path
		
			$this->zoo->loader->register('ElementPro', 'elements:pro/pro.php');
			$this->zoo->loader->register('ElementRepeatablepro', 'elements:repeatablepro/repeatablepro.php');
			$this->zoo->loader->register('ElementFilespro', 'elements:filespro/filespro.php');
		}

		if ( $path = JPATH_ROOT.'/media/zoo/custom_elements' ) {
			$this->zoo->path->register($path, 'elements'); // register custom elements path
		}
		
		// register helpers
		if ( $path = $this->zoo->path->path( 'zlfw:helpers' ) ) {
			$this->zoo->path->register($path, 'helpers');
			$this->zoo->loader->register('ZlfwHelper', 'helpers:zlfwhelper.php');
			$this->zoo->loader->register('ZLDependencyHelper', 'helpers:zldependency.php');
			$this->zoo->loader->register('ZlStringHelper', 'helpers:zlstring.php');
			$this->zoo->loader->register('ZlFilesystemHelper', 'helpers:zlfilesystem.php');
			$this->zoo->loader->register('ZlPathHelper', 'helpers:zlpath.php');
			$this->zoo->loader->register('ZlModelHelper', 'helpers:model.php');
			$this->zoo->loader->register('ZLXmlHelper', 'helpers:zlxmlhelper.php');
		}
		
		// check and perform installation tasks
		if(!$this->checkInstallation()) return; // must go after language, elements path and helpers

		// let know other plugins is all OK to load
		define('ZLFW_DEPENDENCIES_CHECK_OK', true);

		// register zlfield helper
		if ($this->zoo->path->path('zlfield:')) $this->zoo->loader->register('ZlfieldHelper', 'zlfield:zlfield.php');
		
		// register controllers
		if ( $path = $this->zoo->path->path( 'zlfw:controllers' ) ) {
			$this->zoo->path->register( $path, 'controllers' );
		}

		// register models
		if ( $path = $this->zoo->path->path( 'zlfw:models' ) ) {
			$this->zoo->path->register( $path, 'models' );
			$this->zoo->loader->register('ZlQuery', 'models:query.php');
			$this->zoo->loader->register('ZLModel', 'models:zl.php');
		}
		
		// register events
		$this->zoo->event->register('TypeEvent');
		$this->zoo->event->dispatcher->connect('type:coreconfig', array($this, 'coreConfig'));
		$this->zoo->event->dispatcher->connect('application:sefparseroute', array($this, 'sefParseRoute'));
		
		// perform admin tasks
		if ($this->app->isAdmin()) {
			$this->zoo->document->addStylesheet('zlfw:assets/css/zl_ui.css');
		}

		// init ZOOmailing if installed
		$plg_path = $this->zoo->joomla->isVersion('1.5') ? 'plugins/acymailing/zoomailing/' : 'plugins/acymailing/zoomailing/zoomailing/';
		if ( $path = $this->zoo->path->path( 'root:'.$plg_path ) ) {
			
			// register path and include
			$this->zoo->path->register($path, 'zoomailing');
			require_once($path.'/init.php');
		}		
	}

	/**
	 * Setting the Core Elements
	 */
	public function coreConfig( $event, $arguments = array() ){
		$config = $event->getReturnValue();
		$config['_staticcontent'] = array('name' => 'Static Content', 'type' => 'staticcontent');
		$config['_itemlinkpro'] = array('name' => 'Item Link Pro', 'type' => 'itemlinkpro');
		$event->setReturnValue($config);
	}
	
	/**
	 *  checkInstallation
	 */
	public function checkInstallation()
	{
		if($this->app->isAdmin())
		{
			$option = $this->zoo->request->getVar('option');
			if($option == 'com_zoo' || $option == 'com_plugins'){ // limit the check
				// checks if ZOO and ZL Extensions are up to date
				if(!$this->zoo->zldependency->check("zlfw:dependencies.config")){
					return;
				}
			}
		}
		
		return true;
	}

	public function sefParseRoute($event) {

		$zoo = App::getInstance('zoo');
		$groups = $zoo->application->groups();
		
		foreach($groups as $group => $app) {
			if($router = $zoo->path->path("applications:$group/router.php")){
				require_once $router;
				$class = 'ZLRouter'.ucfirst($group);
				$routerClass = new $class;
				$routerClass->parseRoute($event);
			}
		}

	}
}