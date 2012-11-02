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
	
	return 
	'{"fields": {

		"main_layout_wrapper":{
			"type": "fieldset",
			"fields": {

				"image_separator":{
					"type": "separator",
					"text": "PLG_ZLFRAMEWORK_LAYOUT",
					"big":"1"
				},
				"render_options": {
					"type": "subfield",
					"path":"elements:'.$element->getElementType().'\/params\/render.php",
					"adjust_ctrl":{
						"pattern":'.json_encode('/\[layout\]/').',
						"replacement":"[specific]"
					}
				},

				"qtip_layout_wrapper":{
					"type": "control_wrapper",
					"fields": {
						
						"layout_sep":{
							"type": "separator",
							"text": "PLG_ZLFRAMEWORK_QTIP_DISPLAY",
							"big": "1"
						},
						"qtip_options":{
							"type": "wrapper",
							"fields": {
								"_subfield": {
									"type": "subfield",
									"path":"zlfw:elements\/pro\/tmpl\/render\/qtip\/qtip_options.php"
								}
								
							}
						},
						
						"qtip_layout":{
							"type": "wrapper",
							"fields": {
								"sep-specific":{
									"type": "separator",
									"text": "PLG_ZLFRAMEWORK_QTIP_LAYOUT",
									"big":"true"
								},
								"render_options": {
									"type": "subfield",
									"path":"elements:'.$element->getElementType().'\/params\/render.php",
									"control":"specific"
								},
								
								"sep_filter":{
									"type": "separator",
									"text": "PLG_ZLFRAMEWORK_FILTER"
								},
								"filter_options": {
									"type": "subfield",
									"path":"zlfield:json\/filter.json.php",
									"control":"filter"
								},
								
								"sep_separator":{
									"type": "separator",
									"text": "PLG_ZLFRAMEWORK_SP_SEPARATOR"
								},
								"separator_options": {
									"type": "subfield",
									"path":"zlfield:json\/separator.json.php",
									"control":"separator"
								}
							}
						}

					},
					"control": "qtip"
				}
			}
		}
	}}';
		