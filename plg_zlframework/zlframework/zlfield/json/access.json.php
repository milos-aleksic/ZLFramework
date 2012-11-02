<?php
/**
* @package		ZL Framework
* @author    	JOOlanders, SL http://www.zoolanders.com
* @copyright 	Copyright (C) JOOlanders, SL
* @license   	http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

	// init vars
	$options = array();

	$groups = $this->app->zoo->getGroups();
	foreach ($groups as $group) {
		$options[JText::_($group->name)] = $group->id;
	};

	return
	'{
		"access":{
			"type": "select",
			"label": "'.$params->find('access.label').'",
			"help": "'.$params->find('access.help').'",
			"default":"'.$this->app->joomla->getDefaultAccess().'",
			"specific": {
				"options":'.json_encode($options).'
			}
		}
	}';
?>