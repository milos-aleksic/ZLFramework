<?php
/**
* @package		ZL Framework
* @author    	JOOlanders, SL http://www.zoolanders.com
* @copyright 	Copyright (C) JOOlanders, SL
* @license   	http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

	// get options
	$element_options = array();
	if ($element->getElementType() == 'country'){
		$element_options = $this->app->zlfw->getCountryOptions($element->config->get('selectable_country', array()));
	} else {
		$element_options = $element->config->get('option', array());
	}

	// format options
	$options = array();
	foreach ($element_options as $opt){
		$options[$opt['name']] = $opt['name'];
	}
	
	// JSON
	return 
	'"value":{
		"type":"select",
		"label":"PLG_ZLFRAMEWORK_IFT_VALUE",
		"help":"PLG_ZLFRAMEWORK_IFT_VALUE_DESC",
		"specific":{
			"options":'.json_encode($options).',
			"multi":"true"
		}
	},
	"mode":{
		"type":"select",
		"label":"PLG_ZLFRAMEWORK_IFT_MODE",
		"help":"PLG_ZLFRAMEWORK_IFT_MODE_DESC",
		"default":"AND",
		"specific":{
			"options":{
				"PLG_ZLFRAMEWORK_AND":"AND",
				"PLG_ZLFRAMEWORK_OR":"OR"
			}
		}
	},
	"is_select":{
		"type":"hidden",
		"specific":{
			"value":"1"
		}
	}';

?>