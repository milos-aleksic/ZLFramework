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
	
	public $joomla;
	public $app;

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
		$this->joomla = JFactory::getApplication();
		$this->app = App::getInstance('zoo');
		
		// load default and current language
		$this->app->system->language->load('plg_system_zlframework', JPATH_ADMINISTRATOR, 'en-GB', true);
		$this->app->system->language->load('plg_system_zlframework', JPATH_ADMINISTRATOR, null, true);
		
		// register plugin path
		if ( $path = $this->app->path->path( 'root:plugins/system/zlframework/zlframework' ) ) {
			$this->app->path->register($path, 'zlfw');
		}
		
		// register elements fields
		if ( $path = $this->app->path->path( 'zlfw:zlfield' ) ) {
			$this->app->path->register($path, 'zlfield'); // used since ZLFW 2.5.8
			$this->app->path->register($path.'/fields/elements', 'zlfields'); // temporal until all ZL Extensions adapted
			$this->app->path->register($path.'/fields/elements', 'fields'); // necessary since ZOO 2.5.13
		}
		
		// register elements - order is important!
		if ( $path = $this->app->path->path( 'zlfw:elements' ) ) {
			$this->app->path->register($path, 'elements'); // register elements path
		
			$this->app->loader->register('ElementPro', 'elements:pro/pro.php');
			$this->app->loader->register('ElementRepeatablepro', 'elements:repeatablepro/repeatablepro.php');
			$this->app->loader->register('ElementFilespro', 'elements:filespro/filespro.php');
		}

		if ( $path = JPATH_ROOT.'/media/zoo/custom_elements' ) {
			$this->app->path->register($path, 'elements'); // register custom elements path
		}
		
		// register helpers
		if ( $path = $this->app->path->path( 'zlfw:helpers' ) ) {
			$this->app->path->register($path, 'helpers');
			$this->app->loader->register('zlfwHelper', 'helpers:zlfwhelper.php');
			$this->app->loader->register('ZLDependencyHelper', 'helpers:zldependency.php');
			$this->app->loader->register('ZlStringHelper', 'helpers:zlstring.php');
			$this->app->loader->register('ZlFilesystemHelper', 'helpers:zlfilesystem.php');
			$this->app->loader->register('ZlPathHelper', 'helpers:zlpath.php');
			$this->app->loader->register('ZlModelHelper', 'helpers:model.php');
			$this->app->loader->register('ZLXmlHelper', 'helpers:zlxmlhelper.php');
			$this->app->loader->register('ZLFieldHTMLHelper', 'helpers:zlfieldhtml.php');
		}
		
		// check and perform installation tasks
		if(!$this->checkInstallation()) return; // must go after language, elements path and helpers

		// let's define the check was succesfull to speed up other plugins loading
		if (!defined('ZLFW_DEPENDENCIES_CHECK_OK')) define('ZLFW_DEPENDENCIES_CHECK_OK', true);

		// register zlfield helper
		if ($this->app->path->path('zlfield:')) $this->app->loader->register('ZlfieldHelper', 'zlfield:zlfield.php');
		
		// register controllers
		if ( $path = $this->app->path->path( 'zlfw:controllers' ) ) {
			$this->app->path->register( $path, 'controllers' );
		}

		// register models
		if ( $path = $this->app->path->path( 'zlfw:models' ) ) {
			$this->app->path->register( $path, 'models' );
			$this->app->loader->register('ZLModel', 'models:zl.php');
			$this->app->loader->register('ZLModelItem', 'models:item.php');
		}
		
		// register events
		$this->app->event->register('TypeEvent');
		$this->app->event->dispatcher->connect('type:coreconfig', array($this, 'coreConfig'));
		$this->app->event->dispatcher->connect('application:sefparseroute', array($this, 'sefParseRoute'));
		$this->app->event->dispatcher->connect('type:beforesave', array($this, 'typeBeforeSave'));
		
		// perform admin tasks
		if ($this->joomla->isAdmin()) {
			$this->app->document->addStylesheet('zlfw:assets/css/zl_ui.css');
		}

		// init ZOOmailing if installed
		if ( $path = $this->app->path->path( 'root:plugins/acymailing/zoomailing/zoomailing' ) ) {
			
			// register path and include
			$this->app->path->register($path, 'zoomailing');
			require_once($path.'/init.php');
		}

		// load ZL Fields, workaround for first time using ZL elements
		if ($this->app->zlfw->isTheEnviroment('zoo-type-edit')) $this->app->zlfield->loadAssets();
	}

	/**
	 * Actions for type:beforesave event
	 */
	public function typeBeforeSave($event, $arguments = array())
	{
		$type = $event->getSubject();
		$elements = $type->config->get('elements');

		// search for decrypted passwords and encrypt
		array_walk_recursive($elements, 'plgSystemZlframework::_find_and_encrypt');

		// save result
		$type->config->set('elements', $elements);
	}

	protected static function _find_and_encrypt(&$item, $key)
	{
		$matches = array();
		if (preg_match('/zl-decrypted\[(.*)\]/', $item, $matches)) {
			$item = 'zl-encrypted['.App::getInstance('zoo')->zlfw->crypt($matches[1], 'encrypt').']';
		}
	}

	/**
	 * Setting the Core Elements
	 */
	public function coreConfig( $event, $arguments = array() ){
		$config = $event->getReturnValue();
		// keep static content linek in case ZOOtools is not installed
		$config['_staticcontent'] = array('name' => 'Static Content', 'type' => 'staticcontent');
		$config['_itemlinkpro'] = array('name' => 'Item Link Pro', 'type' => 'itemlinkpro');
		$event->setReturnValue($config);
	}
	
	/**
	 *  checkInstallation
	 */
	public function checkInstallation()
	{
		if($this->joomla->isAdmin())
		{
			// checks if ZOO and ZL Extensions are up to date only on ZOO and Plugin views
			$option = $this->app->request->getVar('option');
			if($option == 'com_zoo' || $option == 'com_plugins'){
				if(!$this->app->zldependency->check("zlfw:dependencies.config")){
					return;
				}
			}
		}
		
		return true;
	}

	public function sefParseRoute($event)
	{
		$groups = $this->app->application->groups();
		
		foreach($groups as $group => $app) {
			if($router = $this->app->path->path("applications:$group/router.php")){
				require_once $router;
				$class = 'ZLRouter'.ucfirst($group);
				$routerClass = new $class;
				$routerClass->parseRoute($event);
			}
		}

	}
}