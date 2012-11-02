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

		"layout_wrapper":{
			"type": "fieldset",
			"fields": {
	
				"layout_sep":{
					"type": "separator",
					"text": "qTip",
					"big": "1"
				},
				
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

				"qtip_options":{
					"type": "wrapper",
					"toggle": "PLG_ZLFRAMEWORK_QTIP_OPTIONS",
					"fields": {
					
						"_subfield": {
							"type": "subfield",
							"path":"zlfw:elements\/pro\/tmpl\/render\/qtip\/qtip_options.php"
						}
						
					}
				},
				
				"wrapper_render":{
					"type": "wrapper",
					"toggle": "PLG_ZLFRAMEWORK_QTIP_RENDER_OPTIONS",
					"fields": {
						"sep-specific":{
							"type": "separator",
							"text": "PLG_ZLFRAMEWORK_QTIP_SPECIFIC",
							"big":"1"
						},
						"sub_params": {
							"type": "subfield",
							"path":"elements:'.$element->getElementType().'\/params\/render.php",
							"control":"specific"
						},
						
						"sep_filter":{
							"type": "separator",
							"text": "PLG_ZLFRAMEWORK_QTIP_FILTER",
							"big":"1"
						},
						"sub_filter": {
							"type": "subfield",
							"path":"zlfield:json\/filter.json.php",
							"control":"filter"
						},
						
						"sep_separator":{
							"type": "separator",
							"text": "PLG_ZLFRAMEWORK_QTIP_SEPARATOR",
							"big":"1"
						},
						"sub_separator": {
							"type": "subfield",
							"path":"zlfield:json\/separator.json.php",
							"control":"separator"
						}
					}
				}

			}
		}
	},
	"control": "qtip"}';
		