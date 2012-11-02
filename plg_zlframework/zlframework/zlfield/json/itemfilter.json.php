<?php
/**
* @package		ZL Framework
* @author    	JOOlanders, SL http://www.zoolanders.com
* @copyright 	Copyright (C) JOOlanders, SL
* @license   	http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

	// init vars
	$childs = array();

	// Elements
	$childs[] = isset($params['elements']) ? '"_chosenelms":{
		"type": "elements",
		"label": "'.$params->find('elements.label').'",
		"help": "'.$params->find('elements.help').'",
		"specific":{
			'.($params->find('elements.multi') ? '"multi":"1",' : '').'
			"value_map":{
				"apps":"_chosenapps",
				"types":"_chosentypes"
			}
			'.($params->find('elements.constraint') ? ',"constraint":'.json_encode($params->find('elements.constraint')) : '').'
		}
	}' : '';

	// Item Filter
	$childs[] = isset($params['itemfilter']) ? '"_filter_wrapper":{
		"type":"control_wrapper",
		"fields": {
			"_offset":{
				"type": "text",
				"label": "PLG_ZLFRAMEWORK_FT_OFFSET",
				"help": "PLG_ZLFRAMEWORK_FT_OFFSET_DESC"
			},
			"_limit":{
				"type": "text",
				"label": "PLG_ZLFRAMEWORK_FT_LIMIT",
				"help": "PLG_ZLFRAMEWORK_FT_LIMIT_DESC"
			}
		},
		"control":"itemfilter"
	}' : '';

	// Item Order
	$childs[] = isset($params['itemorder']) ? '"_order_wrapper":{
		"type":"wrapper",
		"fields": {
			"_order_separator":{
				"type":"separator",
				"text":"PLG_ZLFRAMEWORK_ORDER",
				"big":"1"
			},
			"_order_subfield": {
				"type":"subfield",
				"path":"zlfield:json/itemorder.json.php"
			}
		},
		"control":"itemorder"
	}' : '';

	// remove empty values
	$childs = array_filter($childs);
	

	// return json string
	return
	'{
		'.(isset($params['itemorder']) ? '"_filter_separator":{
			"type":"separator",
			"text":"PLG_ZLFRAMEWORK_FILTER",
			"big":"1"
		},' : '').'

		"_chosenapps":{
			"type": "apps",
			"label": "'.$params->find('apps.label').'",
			"help": "'.$params->find('apps.help').'",
			"specific":{
				'.($params->find('apps.multi') ? '"multi":"1"' : '').'
			},
			"childs":{
				"loadfields":{

					'./* Categories */ '
					'.(isset($params['categories']) ? '"_chosencats":{
						"type": "cats",
						"label": "'.$params->find('categories.label').'",
						"help": "'.$params->find('categories.help').'",
						"specific": {
							'.($params->find('categories.multi') ? '"multi":"1",' : '').'
							"value_map":{
								"apps":"_chosenapps"
							}
						},
						"old_id":"_chosencat"
					}' : '').'

					'./* Set a comma if necesary */ '
					'.(isset($params['categories']) && isset($params['types']) ? ',' : '').'

					'./* Types */ '
					'.(isset($params['types']) ? '"_chosentypes":{
						"type":"types",
						"label":"'.$params->find('types.label').'",
						"help":"'.$params->find('types.help').'",
						"help":"PLG_ZLFRAMEWORK_APP_TYPES_DESC",
						"specific":{
							'.($params->find('types.multi') ? '"multi":"1",' : '').'
							"value_map":{
								"apps":"_chosenapps"
							}
						},
						"childs":{
							"loadfields": {'.implode(",", $childs).'}
						}
					}' : '').'

					'./* If no type, render Childs as App ones */'
					'.(!isset($params['types']) ? implode(",", $childs) : "").'

				}
			}
		}
	}';
?>