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
	$id   = $params->find('layout.id', '_layout');
	$mode = $params->find('layout.mode', 'files');
	$path = $params->find('layout.path');
	$regex = $params->find('layout.regex');

	// set default regex
	if(empty($regex)){
		$regex = $mode == 'files' ? '^([^_][_A-Za-z0-9]*)\.php$' : '';
	}

	// set static layouts
	$static_main_layouts = array();
	foreach ($params->find('layout.options', array()) as $name => $value){
		$static_main_layouts[$name] = $value;
	}

	// set default
	$default = $params->find('layout.default', '');
	if(empty($default)){
		$func = $mode == 'files' ? 'files' : 'dirs';
		$default = $this->app->path->$func($path, false, "/$regex/");
		$default = array_shift($default);
	}

	// encode path
	$path = json_encode($path);

	// json
	return
	'{
		"'.$id.'":{
			"type":"layout",
			"label":"'.$params->find('layout.label').'",
			"help":"'.$params->find('layout.help').'",
			"default":"'.$default.'",
			"specific":{
				"mode":"'.$mode.'",
				"path":'.$path.',
				"regex":'.json_encode($regex).', 
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