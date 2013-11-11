<?php
/**
* @package		ZL Framework
* @author    	ZOOlanders http://www.zoolanders.com
* @copyright 	Copyright (C) JOOlanders SL
* @license   	http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

// load libraries
jimport( 'joomla.plugin.plugin' );

/*
	Class: zlfwHelperDependencies
		The ZLFW dependencies helper class
*/
class zlfwHelperDependencies extends AppHelper {

	/*
		Function: check
			Checks if ZOO extensions meet the required version

		Returns:
			bool - true if all requirements are met
	*/
	public function check($file)
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

	/*
		Function: warn
			Warn about outdated extensions
	*/
	public function warn($extensions, $extension = 'ZL Framework')
	{
		foreach ($extensions as $ext)
		{
			$dep_req = $ext['dependency']; // required
			$dep_inst = $ext['installed']; // installed

			// set name
			$name = isset($dep_req->url) ? "<a href=\"{$dep_req->url}\" target=\"_blank\">{$dep_inst->name}</a>" : (string)$dep_inst->name;

			// set message
			$message = isset($dep_req->message) ? JText::sprintf((string)$dep_req->message, $extension, $name): JText::sprintf('PLG_ZLFRAMEWORK_UPDATE_EXTENSION', $extension, $name);

			// raise notice
			$this->app->error->raiseNotice(0, $message);
		}

	}

}