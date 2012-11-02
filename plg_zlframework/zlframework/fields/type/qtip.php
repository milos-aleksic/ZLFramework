<?php
/**
* @package		ZL Framework
* @author    YOOtheme http://www.yootheme.com
* @copyright Copyright (C) YOOtheme GmbH
* @license   	http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

// load config
require_once(JPATH_ADMINISTRATOR . '/components/com_zoo/config.php');

class JElementQtip extends JElement {

	var $_name = 'Qtip';

	function fetchElement($name, $value, $node, $control_name) {
		
		// get app
		// $app = App::getInstance('zoo');
		// $params = $app->data->create($value);
		
		// $styles = array('Cream' => '', 'Light' => 'light', 'Dark' => 'dark', 'Red' => 'red', 'Green' => 'green', 'Blue' => 'blue', 'YouTube' => 'youtube', 'jTools' => 'jtools', 'ClueTip' => 'cluetip', 'Tipped' => 'tipped', 'Tipsy' => 'tipsy');
		
		// $html = array();
		// $html[] = '<div class="zl-fields qtip-options placeholder">';
		
		// $json = '{"fields": {
		// 			"_style":{
		// 				"type": "select",
		// 				"label": "Style",
		// 				"specific": {
		// 					"options": '.json_encode($styles).'
		// 				}
		// 			},
		// 			"_shadow":{
		// 				"type": "checkbox",
		// 				"label": "Shadow",
		// 				"specific":{
		// 					"label": "Yes"
		// 				}
		// 			},
		// 			"_rounded":{
		// 				"type": "checkbox",
		// 				"label": "Rounded",
		// 				"specific":{
		// 					"label": "Yes"
		// 				}
		// 			}
		// 		},
		// 		"control": "qtip"}';
		
		// 	$html[] = $app->zlfwhtml->JSONtoFields($json, $control_name.'[qtip]', null, '', '');
		// $html[] = '</div>';
		
		//return implode("\n", $html);
		return 'Depricated feature: will be removed soon';
	
	}
}