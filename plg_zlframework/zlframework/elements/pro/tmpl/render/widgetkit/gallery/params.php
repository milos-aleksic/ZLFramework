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
	'{"fields": {
		"wrapper-filter":{
			"type": "wrapper",
			"toggle": "Toggle Style Options",
			"fields": {
				"_style_settings": {
					"type":"subfield",
					"path":"elements:pro\/tmpl\/render\/widgetkit\/gallery\/{value}\/settings.php"
				}
			}
		}
	},
	"control": "settings"}';