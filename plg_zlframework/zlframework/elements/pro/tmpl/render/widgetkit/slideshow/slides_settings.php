<?php
/**
* @package		ZL Elements
* @author    	JOOlanders, SL http://www.zoolanders.com
* @copyright 	Copyright (C) JOOlanders, SL
* @license   	http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

// load config
require_once(JPATH_ADMINISTRATOR . '/components/com_zoo/config.php');

	return 
	'{
		"autoplay":{
			"type": "radio",
			"label": "Autoplay",
			"default": "1"
		},
		"interval":{
			"type": "text",
			"label": "Autoplay Interval (ms)",
			"default": "5000"
		},
		"width":{
			"type": "text",
			"label": "Width",
			"default": "auto"
		},
		"height":{
			"type": "text",
			"label": "Height",
			"default": "auto"
		},
		"duration":{
			"type": "text",
			"label": "Effect Duration (ms)",
			"default": "500"
		},
		"index":{
			"type": "text",
			"label": "Start Index",
			"default": "0"
		},
		"order":{
			"type": "select",
			"label": "Order",
			"default": "default",
			"specific": {
				"options": {
					"Default":"default",
					"Random":"random"
				}
			}
		}
	}';