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
		"effect":{
			"type": "select",
			"label": "Effect",
			"default": "slide",
			"specific": {
				"options": {
					"Slide":"slide",
					"Zoom":"zoom",
					"Deck":"deck"
				}
			}
		},
		"index":{
			"type": "text",
			"label": "Start Index",
			"default": "0"
		},
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

		"items_per_set":{
			"type": "select",
			"label": "Items Per Set",
			"default": "3",
			"specific":{
				"options":{
					" 1 ":"1",
					" 2 ":"2",
					" 3 ":"3",
					" 4 ":"4",
					" 5 ":"5",
					" 6 ":"6",
					" 7 ":"7",
					" 8 ":"8",
					" 9 ":"9",
					" 10 ":"10",
					"Use Set Name":"set"
				}
			}
		},
		"navigation":{
			"type": "radio",
			"label": "Navigation",
			"default": "1",
			"specific":{
				"options":{
					"show":"1",
					"hide":"0"
				}
			}
		},
		"buttons":{
			"type": "radio",
			"label": "Buttons",
			"default": "1",
			"specific":{
				"options":{
					"show":"1",
					"hide":"0"
				}
			}
		},
		"title":{
			"type": "radio",
			"label": "Title",
			"default": "0",
			"specific":{
				"options":{
					"show":"1",
					"hide":"0"
				}
			}
		},
		"duration":{
			"type": "text",
			"label": "Effect Duration (ms)",
			"default": "300"
		}
		
	}';