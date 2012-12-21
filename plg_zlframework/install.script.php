<?php
/**
* @package		ZL Framework
* @author    	ZOOlanders http://www.zoolanders.com
* @copyright 	Copyright (C) JOOlanders SL
* @license   	http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
*/

jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');

class plgSystemZlframeworkInstallerScript
{
	protected $_error;
	protected $_src;
	protected $_target;
	protected $_ext = 'zlframework';
	protected $_ext_name = 'ZL Framework';
	protected $_lng_prefix = 'PLG_ZLFRAMEWORK_SYS';

	/* List of obsolete files and folders */
	protected $_obsolete = array(
		'files'	=> array(
			'plugins/system/zlframework/zlframework/control.json',
			'plugins/system/zlframework/zlframework/elements/core.config',
			'plugins/system/zlframework/zlframework/elements/staticcontent/tmpl/render/qtip.php',
			'plugins/system/zlframework/zlframework/assets/css/repeatablepro.css',
			'plugins/system/zoo_zlelements/zoo_zlelements/fields/specific.php',

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
			'plugins/system/zlframework/zlframework/fields/zlspacer.php'
		),
		'folders' => array(
			'plugins/system/zlframework/zlframework/assets/libraries/zlparams',
			'plugins/system/zoo_zlelements/zoo_zlelements/elements_core'
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
		$this->_src = $parent->getParent()->getPath('source'); // tmp folder
		$this->_target = JPATH_ROOT.'/plugins/system/zlframework'; // install folder

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
		$release = $parent->get( "manifest" )->version;

		if(strtolower($type) == 'install'){
			echo JText::sprintf('PLG_ZLFRAMEWORK_SYS_INSTALL', $this->_ext_name, $release);
		}

		if(strtolower($type) == 'update'){
			echo JText::sprintf('PLG_ZLFRAMEWORK_SYS_UPDATE', $this->_ext_name, $release);
		}

		// remove obsolete
		$this->removeObsolete();
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
	 * check extensions requirements
	 *
	 * @return  boolean  True on success
	 */
	protected function checkRequirementsEXAMPLE($parent)
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

		return true;
	}

}