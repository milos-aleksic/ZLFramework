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

	$positions = array('Top left' => 'top left', 'Top center' => 'top center', 'Top right' => 'top right', 'Right top' => 'right top', 'Right center' => 'right center', 'Right bottom' => 'right bottom', 'Bottom right' => 'bottom right', 'Bottom center' => 'bottom center', 'Bottom left' => 'bottom left', 'Left bottom' => 'left bottom', 'Left center' => 'left center', 'Left top' => 'left top', 'Center' => 'center');
	
	return
	'{"fields": {

		"_mode":{
			"type": "select",
			"label": "PLG_ZLFRAMEWORK_MODE",
			"help": "PLG_ZLFRAMEWORK_QTIP_MODE_DESC",
			"dependents": "qtip_positions, wrapper_hide > tooltip",
			"specific": {
				"options": {
					"Tooltip":"tooltip",
					"Modal":"modal"
				}
			}
		},

		"qtip_positions":{
			"type": "wrapper",
			"fields": {
				"qtip-sep":{
					"type": "separator",
					"text": "PLG_ZLFRAMEWORK_QTIP_POSITION"
				},
				"_my":{
					"type": "select",
					"label": "PLG_ZLFRAMEWORK_QTIP_MY",
					"help": "PLG_ZLFRAMEWORK_QTIP_MY_DESC",
					"default":"top left",
					"specific": {
						"options": '.json_encode($positions).'
					}
				},
				"_at":{
					"type": "select",
					"label": "PLG_ZLFRAMEWORK_QTIP_AT",
					"help": "PLG_ZLFRAMEWORK_QTIP_AT_DESC",
					"default":"right top",
					"specific": {
						"options": '.json_encode($positions).'
					}
				}
			}
		},

		"sep-show":{
			"type": "separator",
			"text": "PLG_ZLFRAMEWORK_SHOW"
		},
			"_show_event":{
				"type": "text",
				"label": "PLG_ZLFRAMEWORK_QTIP_EVENT",
				"help": "PLG_ZLFRAMEWORK_QTIP_SHOW_HIDE_EVENT_DESC",
				"default": "mouseenter"
			},
			"_show_delay":{
				"type": "text",
				"label": "PLG_ZLFRAMEWORK_QTIP_DELAY",
				"help": "PLG_ZLFRAMEWORK_QTIP_SHOW_HIDE_DELAY_DESC"
			},
			"_show_solo":{
				"type": "radio",
				"label": "PLG_ZLFRAMEWORK_QTIP_SOLO",
				"help": "PLG_ZLFRAMEWORK_QTIP_SHOW_SOLO_DESC",
				"default":"0"
			},
			
			"wrapper_hide":{
				"type": "wrapper",
				"fields": {
					"sep-hide":{
						"type": "separator",
						"text": "PLG_ZLFRAMEWORK_HIDE"
					},
					"_hide_event":{
						"type": "text",
						"label": "PLG_ZLFRAMEWORK_QTIP_EVENT",
						"help": "PLG_ZLFRAMEWORK_QTIP_SHOW_HIDE_EVENT_DESC",
						"default": "mouseleave"
					},
					"_hide_delay":{
						"type": "text",
						"label": "PLG_ZLFRAMEWORK_QTIP_DELAY",
						"help": "PLG_ZLFRAMEWORK_QTIP_SHOW_HIDE_DELAY_DESC"
					},
					"_hide_fixed":{
						"type": "radio",
						"label": "PLG_ZLFRAMEWORK_QTIP_FIXED",
						"help": "PLG_ZLFRAMEWORK_QTIP_HIDE_FIXED_DESC",
						"default":"0"
					}
				}
			},
	
		"sep-content":{
			"type": "separator",
			"text": "PLG_ZLFRAMEWORK_QTIP_CONTENT"
		},				
			"_class":{
				"type": "text",
				"label": "PLG_ZLFRAMEWORK_QTIP_CUSTOM_CLASS",
				"help": "PLG_ZLFRAMEWORK_QTIP_CUSTOM_CLASS_DESC"
			},
			"_title":{
				"type": "select",
				"label": "PLG_ZLFRAMEWORK_QTIP_TITLE",
				"help": "PLG_ZLFRAMEWORK_QTIP_TITLE_DESC",
				"specific": {
					"options": {
						"None":"",
						"Label":"label",
						"Item Name":"itemname",
						"Loaded Item Name":"loadeditemname",
						"Custom":"custom"
					}
				},
				"dependents":"_customtitle > custom"
			},
			"_customtitle":{
				"type": "text",
				"label": "PLG_ZLFRAMEWORK_QTIP_SET_TITLE"
			},
			"_button":{
				"type": "radio",
				"label": "PLG_ZLFRAMEWORK_QTIP_BUTTON",
				"help": "PLG_ZLFRAMEWORK_QTIP_BUTTON_DESC",
				"default":"0"
			},
			"_width":{
				"type": "text",
				"label": "PLG_ZLFRAMEWORK_WIDTH"
			},
			"_height":{
				"type": "text",
				"label": "PLG_ZLFRAMEWORK_HEIGHT"
			},
			
		
		"sep_trigger":{
			"type": "separator",
			"text": "Trigger"
		},
			
			"_trigger_render":{
				"type": "select",
				"label": "Render",
				"specific": {
					"options": {
						"Yes":"1",
						"No":"4",
						"If main content is limited":"2",
						"Alone":"3"
					}
				},
				"default": "1"
			},
			
			"_trigger_content":{
				"type": "select",
				"label": "PLG_ZLFRAMEWORK_QTIP_TRIGGER",
				"help": "PLG_ZLFRAMEWORK_QTIP_TRIGGER_DESC",
				"specific": {
					"options": {
						"Read More":"read_more",
						"Label":"label",
						"Item Name":"itemname",
						"Custom Text":"custom",
						"Specified DOM":"customdom"
					}
				},
				"dependents":"_cus_tg_text > custom | _tg_dom > customdom"
			},
			"_tg_dom":{
				"type": "text",
				"label": "PLG_ZLFRAMEWORK_QTIP_TRIGGER_DOM",
				"help": "PLG_ZLFRAMEWORK_QTIP_TRIGGER_DOM_DESC"
			},
			"_cus_tg_text":{
				"type": "text",
				"label": "PLG_ZLFRAMEWORK_QTIP_TRIGGER_TEXT",
				"help": "PLG_ZLFRAMEWORK_QTIP_TRIGGER_TEXT_DESC"
			},
			
			"_trigger_title":{
				"type": "select",
				"label": "Title",
				"specific": {
					"options": {
						"None":"",
						"Label":"label",
						"Item Name":"itemname",
						"Custom Text":"custom"
					}
				},
				"dependents":"_trigger_title_custom > custom"
			},
			"_trigger_title_custom":{
				"type": "text",
				"label": "Custom Title"
			}

	}}';