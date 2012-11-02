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

		"widget_separator":{
			"type": "separator",
			"text": "Gallery Widget",
			"big":"1"
		},
		"_style":{
			"type": "layout",
			"label": "Style",
			"default": "default",
			"specific": {
				"path":"elements:pro\/tmpl\/render\/widgetkit\/gallery",
				"mode":"folders"
			},
			"childs":{
				"loadfields": {

					"_style_settings": {
						"type":"subfield",
						"path":"elements:'.$element->getElementType().'\/tmpl\/render\/widgetkit\/gallery\/{value}\/params.php"
					}

				}
			}
		}
		
	}}';