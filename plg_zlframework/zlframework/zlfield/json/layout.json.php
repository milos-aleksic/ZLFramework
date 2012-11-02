<?php
/**
* @package		ZL Framework
* @author    	JOOlanders, SL http://www.zoolanders.com
* @copyright 	Copyright (C) JOOlanders, SL
* @license   	http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

	$static_main_layouts = array();
	foreach ($params->find('layout.options', array()) as $name => $value){
		$static_main_layouts[$name] = $value;
	}

	// set default
	$default = $params->find('layout.default', 'default.php');
	$default = $params->find('layout.mode') == 'folders' ? 'default' : $default;

	// create path to params
	// $path 	  = (string)$node->attributes()->path_overrided ? (string)$node->attributes()->path_overrided : (string)$node->attributes()->path;

	// init vars
	$path = json_encode($params->find('layout.path'));

	/* regext - ^([^_][_A-Za-z0-9]*)\.php$ - will show only php files that don't start with "_" */

	return
	'{
		"_layout":{
			"type":"layout",
			"label":"'.$params->find('layout.label').'",
			"help":"'.$params->find('layout.help').'",
			"default":"'.$default.'",
			"specific":{
				"mode":"'.$params->find('layout.mode', 'files').'",
				"path":'.$path.',
				"regex":'.json_encode($params->find('layout.mode') == 'folders' ? '' : $params->find('layout.regex', '^([^_][_A-Za-z0-9]*)\.php$')).', 
				"options":'.json_encode($static_main_layouts).'
			},
			"childs":{
				"loadfields":{
					"layout_options":{
						"type":"wrapper",
						"fields":{
							"subfield":{
								"type":"subfield",
								"path":"'.trim($path, '"').'\/{value}\/params.php"
							}
						}
					}
				}
			}
		}
	}';
?>