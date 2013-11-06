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
	Class: DependencyHelper
		The general helper Class for dependencies
*/
class ZLDependencyHelper extends AppHelper {

    /*
		Function: check
			Checks if ZOO extensions meet the required version

		Returns:
			bool - true if all requirements are met
	*/
	public function check($file, $extension = 'ZL Framework')
	{
		// init vars
		$pass = true;
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
				if ($version && is_file($manifest) && is_readable($manifest)) {
					if ($xml = simplexml_load_file($manifest)) {
						
						if (version_compare($version, (string) $xml->version, 'g')) {
							$name = isset($dependency->url) ? "<a href=\"{$dependency->url}\" target=\"_blank\">{$name}</a>" : (string) $xml->name;
							$message = isset($dependency->message) ? JText::sprintf((string)$dependency->message, $extension, $name): JText::sprintf('PLG_ZLFRAMEWORK_UPDATE_EXTENSION', $extension, $name);
							$this->app->error->raiseNotice(0, $message);
							$pass = false;
						}
					}
				}
			}
		}
		
		return $pass;
	}

}