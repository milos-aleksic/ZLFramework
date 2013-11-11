<?php
/**
* @package		ZL Framework
* @author    	ZOOlanders http://www.zoolanders.com
* @copyright 	Copyright (C) JOOlanders SL
* @license   	http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');

class plgSystemZlframeworkInstallerScript
{
	protected $_error;
	protected $_src;
	protected $_target;
	protected $_ext = 'zlframework';
	protected $_ext_name = 'ZL Framework';
	protected $_ext_version = '';
	protected $_lng_prefix = 'PLG_ZLFRAMEWORK_SYS';
	public $app;

	/* List of obsolete files and folders */
	protected $_obsolete = array(
		'files'	=> array(
			'plugins/system/zlframework/zlframework/control.json',
			'plugins/system/zlframework/zlframework/elements/core.config',
			'plugins/system/zlframework/zlframework/elements/staticcontent/tmpl/render/qtip.php',
			'plugins/system/zlframework/zlframework/assets/css/repeatablepro.css',
			'plugins/system/zlframework/zlframework/models/query.php',
			'plugins/system/zoo_zlelements/zoo_zlelements/fields/specific.php',

			// files pro old assets
			'plugins/system/zlframework/zlframework/elements/filespro/assets/filespro.css',

			// moved to ZLFW helpers
			'plugins/system/zlframework/zlframework/helpers/zlfilesystem.php',
			'plugins/system/zlframework/zlframework/helpers/zlpath.php',
			'plugins/system/zlframework/zlframework/helpers/zlstring.php',
			'plugins/system/zlframework/zlframework/helpers/zlxmlhelper.php',
			'plugins/system/zlframework/zlframework/helpers/zldependency.php',
			// 'plugins/system/zlframework/zlframework/assets/js/zldialog.js', // to be checked
			'plugins/system/zlframework/zlframework/assets/js/zldialog_moved.txt',

			// until complete cleanup of this folder, proceede individually
			'plugins/system/zlframework/zlframework/fields/example.php',
			'plugins/system/zlframework/zlframework/fields/fields.php',
			'plugins/system/zlframework/zlframework/fields/files.php',
			'plugins/system/zlframework/zlframework/fields/filter.php',
			'plugins/system/zlframework/zlframework/fields/separator.php',
			'plugins/system/zlframework/zlframework/fields/specific.php',
			'plugins/system/zlframework/zlframework/fields/zlapplication.php',
			'plugins/system/zlframework/zlframework/fields/zlinfo.php',
			'plugins/system/zlframework/zlframework/fields/zllayout.php',
			'plugins/system/zlframework/zlframework/fields/zlspacer.php',

			// deprecated assets
			'plugins/system/zlframework/zlframework/assets/js/yepnope.min.js',
			'plugins/system/zlframework/zlframework/zlux/zlux.css'
		),
		'folders' => array(
			'plugins/system/zlframework/zlframework/assets/libraries/zlparams',
			'plugins/system/zoo_zlelements/zoo_zlelements/elements_core',
			'plugins/system/zlframework/zlframework/zlfwhelpers',

			// moved to zlux/assets
			'plugins/system/zlframework/zlframework/zlux/zlbootstrap',

			// moved to helpers folder
			'plugins/system/zlframework/zlframework/helpers_zlfw',

			// filespro old assets
			'plugins/system/zlframework/zlframework/elements/filespro/assets/js',
			'plugins/system/zlframework/zlframework/elements/filespro/assets/images',
			'plugins/system/zlframework/zlframework/elements/filespro/assets/plupload',
			'plugins/system/zlframework/zlframework/elements/filespro/assets/s3',

			// moved to ZLUX
			'plugins/system/zlframework/zlframework/assets/libraries/bootstrap'
			// 'plugins/system/zlframework/zlframework/assets/libraries/zlux', // the old zlux
			// 'plugins/system/zlframework/zlframework/assets/images' // to be checked
		)
	);

	/**
	 * Called before any type of action
	 *
	 * @param   string  $type  Which action is happening (install|uninstall|discover_install)
	 * @param   object  $parent  The object responsible for running this script
	 *
	 * @return  boolean  True on success
	 */
	public function preflight($type, $parent)
	{
		// init vars
		$db = JFactory::getDBO();
		$type = strtolower($type);
		$this->_src = $parent->getParent()->getPath('source'); // tmp folder
		$this->_target = JPATH_ROOT.'/plugins/system/zlframework'; // install folder
		$this->_ext_version = $parent->get( "manifest" )->version;

		// load ZLFW sys language file EXAMPLE
		// JFactory::getLanguage()->load('plg_system_zlframework.sys', JPATH_ADMINISTRATOR, 'en-GB', true);

		// check dependencies if not uninstalling EXAMPLE
		// if($type != 'uninstall' && !$this->checkRequirements($parent)){
		// 	Jerror::raiseWarning(null, $this->_error);
		// 	return false;
		// }

		// don't overide layouts EXAMPLE
		/* 
		 * when updating we don't wont to override renderer/item folder,
		 * so let's delete the temp folder before install only if it already exists
		 */
		// if($type == 'update'){
		// 	JFolder::exists($this->_target.'/renderer/item') && 
		// 	JFolder::delete($this->_src.'/renderer/item');
		// }
		
		
		if($type == 'update'){

			/* warn about update requirements only once */
			if(!JFile::exists($this->_src.'/warned.txt')
				&& !$this->checkCompatibility($this->_src.'/zlframework/dependencies.config')) {

				// rise error
				Jerror::raiseWarning(null, $this->_error);

				// create a dummy indicational mark file
				$some = 'dummy content';
				JFile::write($this->_src.'/warned.txt', $some);

				// copy the entire install to avoid it delition on cancel
				JFolder::copy($this->_src, JPath::clean(JPATH_ROOT . '/tmp/' . basename($this->_src.'_copy')));

				// cancel update
				return false;
			} else {
				if (JFile::exists($this->_src.'/warned.txt')) JFile::delete($this->_src.'/warned.txt');
			}

		}
	}

	/**
	 * Called on installation
	 *
	 * @param   object  $parent  The object responsible for running this script
	 *
	 * @return  boolean  True on success
	 */
	function install($parent)
	{
		// init vars
		$db = JFactory::getDBO();

        // enable plugin
        $db->setQuery("UPDATE `#__extensions` SET `enabled` = 1 WHERE `type` = 'plugin' AND `element` = '{$this->_ext}'");
        $db->query();
    }

    /**
	 * method to update the component
	 *
	 * @return void
	 */
	function update($parent) {}

    /**
	 * Called on uninstallation
	 *
	 * @param   object  $parent  The object responsible for running this script
	 *
	 * @return  boolean  True on success
	 */
	function uninstall($parent)
	{
		// show uninstall message
		echo JText::_($this->langString('_UNINSTALL'));
    }

	/**
	 * Called after install
	 *
	 * @param   string  $type  Which action is happening (install|uninstall|discover_install)
	 * @param   object  $parent  The object responsible for running this script
	 *
	 * @return  boolean  True on success
	 */
	public function postflight($type, $parent)
	{
		// init vars
		$type = strtolower($type);
		$release = $parent->get( "manifest" )->version;

		if($type == 'install'){
			echo JText::sprintf('PLG_ZLFRAMEWORK_SYS_INSTALL', $this->_ext_name, $release);
		}

		if($type == 'update'){
			echo JText::sprintf('PLG_ZLFRAMEWORK_SYS_UPDATE', $this->_ext_name, $release);
		}

		// remove obsolete
		$this->removeObsolete();

		// remove install folder
		JFolder::delete($this->_src);
	}

	/**
	 * Removes obsolete files and folders
	 * @version 1.1
	 */
	private function removeObsolete()
	{
		// Remove files
		if(!empty($this->_obsolete['files'])) foreach($this->_obsolete['files'] as $file) {
			$f = JPATH_ROOT.'/'.$file;
			if(!JFile::exists($f)) continue;
			JFile::delete($f);
		}

		// Remove folders
		if(!empty($this->_obsolete['folders'])) foreach($this->_obsolete['folders'] as $folder) {
			$f = JPATH_ROOT.'/'.$folder;
			if(!JFolder::exists($f)) continue;
			JFolder::delete($f);
		}
	}

	/**
	 * creates the lang string
	 * @version 1.0
	 *
	 * @return  string
	 */
	protected function langString($string)
	{
		return $this->_lng_prefix.$string;
	}

	/**
	 * check requirements EXAMPLE
	 *
	 * @return  boolean  True on success
	 */
	protected function checkRequirements($parent)
	{
		/*
		 * make sure Akeeba Subscription exist, is enabled
		 */
		if (!JFile::exists(JPATH_ADMINISTRATOR.'/components/com_akeebasubs/aaa_akeebasubs.xml')
			|| !JComponentHelper::getComponent('com_akeebasubs', true)->enabled) {
			$this->_error = "ZOOaksubs relies on <a href=\"https://www.akeebabackup.com\" target=\"_blank\">Akeeba Subscriptions</a>, be sure is installed and enabled before retrying the installation.";
			return false;
		}

		// and up to date
		$akeeba_manifest = simplexml_load_file(JPATH_ADMINISTRATOR.'/components/com_akeebasubs/aaa_akeebasubs.xml');
		$min_release = 2;

		if( version_compare((string)$akeeba_manifest->version, (string)$min_release, '<') ) {
			$this->_error = "Akeeba Subscription v{$min_release} or higher required, please update it and retry the installation.";

			return false;
		}

		/*
		 * make sure ZLFW is up to date
		 */
		if($min_zlfw_release = $parent->get( "manifest" )->attributes()->zlfw)
		{
			$zlfw_manifest = simplexml_load_file(JPATH_ROOT.'/plugins/system/zlframework/zlframework.xml');

			if( version_compare((string)$zlfw_manifest->version, (string)$min_zlfw_release, '<') ) {
				$this->_error = "<a href=\"https://www.zoolanders.com/extensions/zl-framework\" target=\"_blank\">ZL Framework</a> v{$min_zlfw_release} or higher required, please update it and retry the installation.";
				return false;
			}
		}

		return true;
	}

	/**
	 * Check if the extensions are updated before current update is allowed
	 * @version 1.0
	 *
	 * @return  boolean  true if all extensions are compatible
	 */
	protected function checkCompatibility($file)
	{
		// set zoo app instance
		$this->app = App::getInstance('zoo');

		// check dependencies
		$status = $this->_dependencyCheck('root:'.$this->app->path->relative($file));
		if (!$status['state']){

			// list the outdated extensions
			$outdated_ext = array();
			foreach ($status['extensions'] as $ext) {

				$dep_req = $ext['dependency']; // required
				$dep_inst = $ext['installed']; // installed

				// set name
				$name = isset($dep_req->url) ? "<a href=\"{$dep_req->url}\" target=\"_blank\">{$dep_inst->name}</a>" : (string)$dep_inst->name;
				$outdated_ext[] = $name;
			}

			// set the proceede link with it's behaviour
			$path = JPATH_ROOT . '/tmp/' . basename($this->_src.'_copy');
			$path = str_replace('\\', '\\/', $path);
			$javascript = "document.getElementById('install_directory').value = '{$path}';document.querySelectorAll('form .uploadform .button, form .uploadform .btn')[1].click();return false;";
			$this->_error = JText::sprintf('PLG_ZLFRAMEWORK_SYS_OUTDATED_EXTENSIONS', $this->_ext_version, implode(', ', $outdated_ext), $javascript);
		}
		
		return $status['state'];
	}


	/*
		Function: _dependencyCheck
			Copy of the zlfw->dependencies::check function

		Returns:
			bool - true if all requirements are met
	*/
	protected function _dependencyCheck($file, $extension = 'ZL Framework')
	{
		// init vars
		$status = array('state' => true, 'extensions' => array());
		$groups = $this->app->path->path($file);

		// get the content from file
		if ($groups && $groups = json_decode(JFile::read($groups)))
		{
			// iterate over the groups
			foreach ($groups as $group => $dependencies) foreach ($dependencies as $name => $dependency)
			{
				if ($group == 'plugins') {
					// get plugin
					$folder = isset($dependency->folder) ? $dependency->folder : 'system';
					$plugin = JPluginHelper::getPlugin($folder, strtolower($name));

					// if plugin disable, skip it
					if (empty($plugin)) continue;
				}
				
				$version  = $dependency->version;
				$manifest = $this->app->path->path('root:'.$dependency->manifest);
				if ($version && is_file($manifest) && is_readable($manifest) && $xml = simplexml_load_file($manifest)) {
						
					// check if the extension is outdated
					if (version_compare($version, (string) $xml->version, 'g')) {
						$status['state'] = false;
						$status['extensions'][] = array('dependency' => $dependency, 'installed' => $xml);
					}
					
				}
			}
		}
		
		return $status;
	}
}