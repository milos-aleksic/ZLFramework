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
			'.($params->find('elements.constraint') ? ',"elements":'.json_encode($params->find('elements.constraint')) : '').'
		}
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

	// get default App
	$applications = $this->app->table->application->all(array('order' => 'name'));
	$default_app = array_shift( $applications );

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
			"default":"'.$default_app->id.'",
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