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

	// init vars
	$type = $psv->get('_widget');

	return
	'{"fields": {
		
		"style":{
			"type":"layout",
			"label":"Style",
			"default":"default",
			"specific": {
				"path":"media:widgetkit\/widgets\/' . $type . '\/styles",
				"mode":"folders"
			},
			"childs":{
				"loadfields": {

					"subfield": {
						"type":"subfield",
						"path":"zlfield:json\/widgetkit\/settings.json.php"
					}

				}
			}
		}

	},
	"control": "settings"}';