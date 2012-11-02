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
		"lightbox_overide":{
			"type": "checkbox",
			"label": "Lightbox Defaults",
			"default": "0",
			"dependents":"wrapper_lightbox_defaults > 1",
			"specific":{
				"label": "PLG_ZLFRAMEWORK_OVERRIDE"
			}
		},
		"wrapper_lightbox_defaults":{
			"type": "wrapper",
			"fields": {
				"titlePosition":{
					"type": "select",
					"label": "Title position",
					"default": "float",
					"specific":{
						"options":{
							"Float":"float",
							"Outside":"outside",
							"Inside":"inside",
							"Over":"over"
						}
					}
				},
				"transitionIn":{
					"type": "select",
					"label": "Transition In",
					"default": "fade",
					"specific":{
						"options":{
							"Fade":"fade",
							"Elastic":"elastic",
							"None":"none"
						}
					}
				},
				"transitionOut":{
					"type": "select",
					"label": "Transition Out",
					"default": "fade",
					"specific":{
						"options":{
							"Fade":"fade",
							"Elastic":"elastic",
							"None":"none"
						}
					}
				},
				"overlayShow":{
					"type": "radio",
					"label": "Show Overlay",
					"default": "true",
					"specific":{
						"options":{
							"JYES":"true",
							"JNO":"false"
						}
					},
					"dependents":"_wrapper_overlay > true"
				},
				"_wrapper_overlay":{
					"type": "wrapper",
					"fields": {
						"overlayColor":{
							"type": "text",
							"label": "Overlay Color",
							"specific":{
								"placeholder":"#777"
							}
						},
						"overlayOpacity":{
							"type": "text",
							"label": "Overlay Opacity",
							"specific":{
								"placeholder":"0.7"
							}
						}
					}
				},
				"padding":{
					"type": "text",
					"label": "Padding",
					"specific":{
						"placeholder":"5"
					}
				}
			}
		}
	},
	"control": "lightbox_settings"}';