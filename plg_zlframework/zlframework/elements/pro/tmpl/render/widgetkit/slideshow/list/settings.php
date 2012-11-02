<?php
/**
* @package		ZL FrameWork
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
		"slides_settings": {
			"type":"subfield",
			"path":"elements:pro\/tmpl\/render\/widgetkit\/slideshow\/slides_settings.php"
		},
		"navigation":{
			"type": "select",
			"label": "Navigation Width",
			"default": "200",
			"specific":{
				"options":{
					"100px":"100",
					"150px":"150",
					"200px":"200",
					"250px":"250"
				}
			}
		},
		"animated":{
			"type": "select",
			"label": "Effect",
			"default": "fade",
			"specific":{
				"options":{
					"Fade":"fade",
					"Scroll":"scroll"
				}
			}
		}
	}';