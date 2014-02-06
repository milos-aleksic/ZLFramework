<?php
/**
* @package		ZOOcart
* @author		ZOOlanders http://www.zoolanders.com
* @copyright	Copyright (C) JOOlanders SL
* @license		http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');

/*
	Class: ZL Installer Script
		The InstallerScript abstract class
*/
abstract class zlInstallerScript
{
	/*
		Variable: app
			App instance.
	*/
	public $app;

	/*
		Variable: app
			The DB object reference.
	*/
	public $db;

	/*
		Variable: ext name
			The install source folder.
	*/
	public $source;

	/*
		Variable: app
			The install target folder.
	*/
	public $target;

	/*
		Variable: type
			The install type
	*/
	public $type;

	/*
		Variable: parent
			The parent object
	*/
	public $parent;

	/*
		Variable: ext_name
			The extension name
	*/
	public $ext_name;

	/*
		Variable: _ext_id
			The extension id
	*/
	protected $_ext_id;

	/*
		Variable: _error
			The error message
	*/
	protected $_error;

	/**
	 * Set initials vars
	 */
	public function initVars($type, $parent)
	{
		$this->app = App::getInstance('zoo');
		$this->db = JFactory::getDBO();
		$this->type = strtolower($type);
		$this->parent = $parent;
		$this->source = $parent->getParent()->getPath('source');
		$this->target = $parent->getParent()->getPath('extension_root');
		$this->ext_name = (string)$parent->get('manifest')->name;
	}

	/**
	 * Called before any type of action
	 *
	 * @param   string  $type  Which action is happening (install|uninstall|discover_install)
	 * @param   object  $parent  The object responsible for running this script
	 *
	 * @return  boolean  True on success
	 */
	public function preflight($type, $parent){}

	/**
	 * Called on installation
	 *
	 * @param   object  $parent  The object responsible for running this script
	 *
	 * @return  boolean  True on success
	 */
	public function install($parent){}

	/**
	 * Called on update
	 *
	 * @return void
	 */
	public function update($parent){}

	/**
	 * Called on uninstallation
	 *
	 * @param   object  $parent  The object responsible for running this script
	 *
	 * @return  boolean  True on success
	 */
	public function uninstall($parent){}

	/**
	 * Called after all actions
	 *
	 * @param   string  $type  Which action is happening (install|uninstall|discover_install)
	 * @param   object  $parent  The object responsible for running this script
	 *
	 * @return  boolean  True on success
	 */
	public function postflight($type, $parent)
	{
		// init vars
		$new_version = (string)$parent->get('manifest')->version;

		// after install
		if($type == 'install')
		{
			// set ext version
			$this->setVersion();

			// show
			echo JText::sprintf('PLG_ZLFRAMEWORK_SYS_INSTALL', $this->ext_name, $new_version);
		}

		// after update
		if($this->type == 'update')
		{
			// set ext version
			$this->setVersion();

			// show
			echo JText::sprintf('PLG_ZLFRAMEWORK_SYS_UPDATE', $this->ext_name, $new_version);
		}

		// after uninstall
		if($type == 'uninstall')
		{
			// remove version from schema table
			$this->cleanVersion();
		}
	}

	/**
	 * Check dependencies
	 *
	 * @return  boolean  True on success
	 */
	protected function checkDependencies($parent)
	{
		// init vars
		$dependencies = $parent->get( "manifest" )->dependencies->attributes();	

		// check Joomla
		if ($min_v = (string)$dependencies->joomla) 
		{
			// if up to date
			$joomla_release = new JVersion();
			$joomla_release = $joomla_release->getShortVersion();
			if( version_compare( (string)$joomla_release, $min_v, '<' ) ) {
				$this->_error = JText::sprintf($this->langString('_DEPENDENCY_OUTDATED'), $this->ext_name, 'http://www.joomla.org', 'Joomla!', $min_v);
				return false;
			}
		}

		// check ZOO
		if ($min_v = (string)$dependencies->zoo) 
		{
			// if installed and enabled
			if (!JFile::exists(JPATH_ADMINISTRATOR.'/components/com_zoo/config.php')
				|| !JComponentHelper::getComponent('com_zoo', true)->enabled) {
				$this->_error = JText::sprintf($this->langString('_DEPENDENCY_MISSING'), $this->ext_name, 'http://www.yootheme.com/zoo', 'ZOO');
				return false;
			}

			// if up to date
			$zoo_manifest = simplexml_load_file(JPATH_ADMINISTRATOR.'/components/com_zoo/zoo.xml');

			if( version_compare((string)$zoo_manifest->version, $min_v, '<') ) {
				$this->_error = JText::sprintf($this->langString('_DEPENDENCY_OUTDATED'), $this->ext_name, 'http://www.yootheme.com/zoo', 'ZOO', $min_v);
				return false;
			}
		}

		// check ZL
		if ($min_v = (string)$dependencies->zl) 
		{
			// if installed and enabled
			if (!JFile::exists(JPATH_ADMINISTRATOR.'/components/com_zoolanders/zoolanders.php')
				|| !JComponentHelper::getComponent('com_zoolanders', true)->enabled) {
				$this->_error = JText::sprintf($this->langString('_DEPENDENCY_MISSING'), $this->ext_name, 'https://www.zoolanders.com/extensions/zoolanders', 'ZOOlanders Component');
				return false;
			}

			// if up to date
			$zl_manifest = simplexml_load_file(JPATH_ADMINISTRATOR.'/components/com_zoolanders/zoolanders.xml');

			if( version_compare((string)$zl_manifest->version, $min_v, '<') ) {
				$this->_error = JText::sprintf($this->langString('_DEPENDENCY_OUTDATED'), $this->ext_name, 'https://www.zoolanders.com/extensions/zoolanders', 'ZOOlanders Component', $min_v);
				return false;
			}
		}

		// check ZLFW
		if ($min_v = (string)$dependencies->zlfw) 
		{
			// if installed and enabled
			if (!JPluginHelper::getPlugin('system', 'zlframework')) {
				$this->_error = JText::sprintf($this->langString('_DEPENDENCY_MISSING'), $this->ext_name, 'https://www.zoolanders.com/extensions/zl-framework', 'ZL Framework');
				return false;
			}

			// if up to date
			$zlfw_manifest = simplexml_load_file(JPATH_ROOT.'/plugins/system/zlframework/zlframework.xml');

			if( version_compare((string)$zlfw_manifest->version, $min_v, '<') ) {
				$this->_error = JText::sprintf($this->langString('_DEPENDENCY_OUTDATED'), $this->ext_name, 'https://www.zoolanders.com/extensions/zl-framework', 'ZL Framework', $min_v);
				return false;
			}
		}

		return true;
	}

	/**
	 * creates the lang string
	 *
	 * @return  string
	 */
	protected function langString($string)
	{
		return $this->lng_prefix.$string;
	}

	/**
	 * creates the lang string
	 *
	 * @return  string
	 */
	protected function getExtID()
	{
		if(!$this->_ext_id) {
			$this->db->setQuery("SELECT `extension_id` FROM `#__extensions` WHERE `element` = '{$this->_ext}'");
			if ($plg = $this->db->loadObject()) 
				$this->_ext_id = (int)$plg->extension_id;
		}

		return $this->_ext_id;
	}

	/**
	 * Gets the current version from schema table
	 */
	public function getVersion()
	{
		// set query
		$this->db->setQuery("SELECT `version_id` FROM `#__schemas` WHERE `extension_id` = '{$this->getExtID()}'");

		// load and return
		if($obj = $this->db->loadObject()) {
			return $obj->version_id;
		}

		return null;
	}

	/**
	 * Sets the version in schemas table.
	 *
	 * @param string $version
	 */
	public function setVersion($version = null)
	{
		// init vars
		$version = $version ? $version : (string)$this->parent->get('manifest')->version;
		$version = str_replace(array(' ', '_'), '', $version);
		$ext_id = $this->getExtID();

		// insert row
		$this->db->setQuery("SELECT * FROM `#__schemas` WHERE `extension_id` = '{$ext_id}'");
		if (!$this->db->loadObject()) {
			$query = $this->db->getQuery(true);
			$query->clear()
				->insert($this->db->quoteName('#__schemas'))
				->columns(array($this->db->quoteName('extension_id'), $this->db->quoteName('version_id')))
				->values($ext_id . ', ' . $this->db->quote($version));
			$this->db->setQuery($query)->execute();

		// of update if exists
		} else {
			$query = $this->db->getQuery(true);
			$query->clear()
				->update($this->db->quoteName('#__schemas'))
				->set($this->db->quoteName('version_id') . ' = ' . $this->db->quote($version))
				->where($this->db->quoteName('extension_id').' = '. $ext_id);
			$this->db->setQuery($query)->execute();
		}
	}

	/**
	 * Removes the version from schema table
	 */
	protected function cleanVersion(){
		$this->db->setQuery("DELETE FROM `#__schemas` WHERE `extension_id` = '{$this->getExtID()}'")->query();
	}

}