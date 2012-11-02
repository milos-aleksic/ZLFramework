<?php
/**
* @package		ZL Framework
* @author    	YOOtheme http://www.yootheme.com
* @author    	JOOlanders, SL
* @original		zoo/admin/helpers/dependency.php
* @copyright 	Copyright (C) YOOtheme GmbH
* @copyright 	Copyright (C) JOOlanders, SL for any modification
* @license   	http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
*/

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
		$pass = true;
		if ($dependencies = $this->app->path->path($file)) {
			if ($dependencies = json_decode(JFile::read($dependencies))) {
				foreach ($dependencies as $key => $dependency) {
					$version  = $dependency->version;
					$manifest = $this->app->path->path('root:'.$dependency->manifest);
					if ($version && is_file($manifest) && is_readable($manifest)) {
						if ($xml = simplexml_load_file($manifest)) {
							if (version_compare($version, (string) $xml->version, 'g')) {
								$name = isset($dependency->url) ? "<a href=\"{$dependency->url}\" target=\"_blank\">{$key}</a>" : (string) $xml->name;
								$message = isset($dependency->message) ? JText::sprintf((string)$dependency->message, $extension, $name): JText::sprintf('PLG_ZLFRAMEWORK_UPDATE_EXTENSION', $extension, $name);
								$this->app->error->raiseNotice(0, $message);
								$pass = false;
							}
						}
					}
				}
			}
		}
		
		return $pass;
	}

}