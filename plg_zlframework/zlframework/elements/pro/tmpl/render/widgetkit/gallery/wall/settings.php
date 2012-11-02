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

		"order":{
			"type":"select",
			"label":"Order",
			"default":"default",
			"specific":{
				"options":{
					"Default":"default",
					"Random":"random"
				}
			}
		},
		"effect":{
			"type": "select",
			"label": "Effect",
			"specific":{
				"options":{
					"None":"",
					"Spotlight":"spotlight",
					"Zoom":"zoom",
					"Polaroid":"polaroid"
				}
			},
			"dependents":"wrapper_spotlight > spotlight"
		},
		"margin":{
			"type": "select",
			"label": "Margin",
			"specific":{
				"options":{
					"No":"",
					"Yes":"margin"
				}
			}
		},
		"corners":{
			"type": "select",
			"label": "Corners",
			"specific":{
				"options":{
					"Square":"",
					"Round":"round"
				}
			}
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
		}

	}}';