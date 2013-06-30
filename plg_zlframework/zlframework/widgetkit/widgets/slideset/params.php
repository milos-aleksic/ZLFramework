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

		"_style":{
			"type":"layout",
			"label":"Style",
			"default":"default",
			"specific": {
				"path":"zlfw:widgetkit\/widgets\/slideset",
				"mode":"folders"
			},
			"childs":{
				"loadfields": {

					"widget_separator":{
						"type":"separator",
						"text":"Settings",
						"layout":"subsection"
					},
					"_style_settings": {
						"type":"subfield",
						"path":"zlfw:widgetkit\/widgets\/slideset\/{value}\/settings.php",
						"control":"settings"
					}

				}
			}
		}

	}}';