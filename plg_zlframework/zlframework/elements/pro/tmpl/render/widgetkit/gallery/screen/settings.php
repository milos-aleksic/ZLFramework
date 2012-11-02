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
	'{"fields": {

		"autoplay":{
			"type": "radio",
			"label": "Autoplay",
			"default": "1"
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
		},
		"interval":{
			"type": "text",
			"label": "Autoplay Interval (ms)",
			"default": "5000"
		},
		"duration":{
			"type": "text",
			"label": "Effect Duration (ms)",
			"default": "500"
		},
		"animated":{
			"type": "select",
			"label": "Effect",
			"default": "randomSimple",
			"specific":{
				"options":{
					"Top":"top",
					"Bottom":"bottom",
					"Left":"left",
					"Right":"right",
					"Fade":"fade",
					"ScrollLeft":"scrollLeft",
					"ScrollRight":"scrollRight",
					"Scroll":"scroll",
					"SliceUp":"sliceUp",
					"SliceDown":"sliceDown",
					"SliceUpDown":"sliceUpDown",
					"Swipe":"swipe",
					"Fold":"fold",
					"Puzzle":"puzzle",
					"Boxes":"boxes",
					"BoxesRtl":"boxesRtl",
					"KenBurns":"kenburns",
					"RandomSimple":"randomSimple",
					"RandomFx":"randomFx"
				}
			}
		},
		"index":{
			"type": "text",
			"label": "Start Index",
			"default": "0"
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
		"slices":{
			"type": "text",
			"label": "Slices",
			"default": "20"
		},
		"zl_captions":{
			"type": "select",
			"label": "Captions",
			"default": "1",
			"specific":{
				"options":{
					"PLG_ZLFRAMEWORK_DISABLED":"0",
					"PLG_ZLFRAMEWORK_DEFAULT":"1",
					"PLG_ZLFRAMEWORK_CUSTOM":"2"
				}
			},
			"dependents":"_custom_caption > 2 | caption_animation_duration !> 0"
		},
		"_custom_caption":{
			"label":"Custom Caption",
			"type":"text"
		},
		"caption_animation_duration":{
			"type": "text",
			"label": "Caption Animation Duration",
			"default": "500"
		}
		
	}}';