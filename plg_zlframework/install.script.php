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
		
		// check dependencies if not uninstalling
		if($type != 'uninstall' && !$this->checkRequirements($parent)){
			Jerror::raiseWarning(null, $this->_error);
			return false;
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

		if($type == 'install'){
			echo JText::sprintf('ZL_EXTENSIONS_SYS_INSTALL', $this->_ext_name, $release);
		}

		if($type == 'update'){
			echo JText::sprintf('ZL_EXTENSIONS_SYS_UPDATE', $this->_ext_name, $release);
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
	 * @version 1.7
	 *
	 * @return  boolean  True on success
	 */
	protected function checkRequirements($parent)
	{
		// get joomla release
		$joomla_release = new JVersion();
		$joomla_release = $joomla_release->getShortVersion();

		// manifest file minimum joomla version
		$min_joomla_release = $parent->get( "manifest" )->attributes()->version;

		/*
		 * abort if the current Joomla! release is older
		 */
		if( version_compare( (string)$joomla_release, (string)$min_joomla_release, '<' ) ) {
			$this->_error = JText::sprintf('ZL_EXTENSIONS_SYS_EXTENSION_OUTDATED', $this->_ext_name, 'Joomla!', $joomla_release, $min_joomla_release);
			return false;
		}

		/*
		 * make sure ZOO is up to date
		 */
		$zoo_manifest = simplexml_load_file(JPATH_ADMINISTRATOR.'/components/com_zoo/zoo.xml');
		$min_zoo_release = $parent->get( "manifest" )->attributes()->zooversion;

		if( version_compare((string)$zoo_manifest->version, (string)$min_zoo_release, '<') ) {
			$this->_error = JText::sprintf('ZL_EXTENSIONS_SYS_EXTENSION_OUTDATED', $this->_ext_name, 'ZOO', $zoo_manifest->version, $min_zoo_release);
			return false;
		}

		return true;
	}
}