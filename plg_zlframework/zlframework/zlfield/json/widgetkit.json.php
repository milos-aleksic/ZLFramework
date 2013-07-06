<?php
/**
* @package		ZL Framework
* @author    	JOOlanders, SL http://www.zoolanders.com
* @copyright 	Copyright (C) JOOlanders, SL
* @license   	http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

// load config
require_once(JPATH_ADMINISTRATOR . '/components/com_zoo/config.php');

	$widgets = $params->get('widgets', array());

	return
	'{"fields": {
		"_wkcheck_info":{
			"type": "info",
			"specific":{
				"text": "PLG_ZLFRAMEWORK_WK_NOT_PRESENT"
			},
			"label": "PLG_ZLFRAMEWORK_WARNING",
			"renderif":{
				"com_widgetkit":"0"
			}
		},

		"_widget":{
			"type": "select",
			"label": "PLG_ZLFRAMEWORK_WIDGET",
			"specific": {
				"options":'. json_encode($widgets) .'
			},
			"childs": {
				"loadfields":{

					"subfield": {
						"type":"subfield",
						"path":"zlfield:json\/widgetkit\/style.json.php"
					}
					
				}
			}
		}	

	},
	"control": "widgetkit"}';