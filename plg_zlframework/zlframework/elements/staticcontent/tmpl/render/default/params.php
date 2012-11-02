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

		"layout_wrapper":{
			"type": "fieldset",
			"fields": {
	
				"layout_sep":{
					"type": "separator",
					"text": "Default Layout",
					"big": "1"
				},

				"render_options": {
					"type": "subfield",
					"path":"elements:'.$element->getElementType().'\/params\/render.php",
					"adjust_ctrl":{
						"pattern":'.json_encode('/\[layout\]/').',
						"replacement":"[specific]"
					}
				}

			}
		}

	}}';