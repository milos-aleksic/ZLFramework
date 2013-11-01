<?php
/**
* @package		ZL Framework
* @author    	JOOlanders, SL http://www.zoolanders.com
* @copyright 	Copyright (C) JOOlanders, SL
* @license   	http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

	// JSON
	return
	'"type":{
		"type":"select",
		"label":"PLG_ZLFRAMEWORK_IFT_FILTER_TYPE",
		"help":"PLG_ZLFRAMEWORK_IFT_FILTER_TYPE_DESC",
		"specific":{
			"options":{
				"=":"exact",
				"&gt;":"from",
				"&lt;":"to",
				"&gt;=":"fromequal",
				"&lt;=":"toequal",
				"PLG_ZLFRAMEWORK_IFT_WITHIN_RANGE_EQUAL":"rangeequal",
				"PLG_ZLFRAMEWORK_IFT_WITHIN_RANGE":"range",
				"PLG_ZLFRAMEWORK_IFT_WITHIN_OUT_OF_RANGE_EQUAL":"outofrangeequal",
				"PLG_ZLFRAMEWORK_IFT_WITHIN_OUT_OF_RANGE":"outofrange"
			}
		},
		"default": "exact",
		"dependents":"value_to > rangeequal OR range OR outofrangeequal OR outofrange"
	},
	"value":{
		"type":"text",
		"label":"PLG_ZLFRAMEWORK_IFT_VALUE",
		"help":"PLG_ZLFRAMEWORK_IFT_VALUE_DESC"
	},
	"value_to":{
		"type":"text",
		"label":"PLG_ZLFRAMEWORK_IFT_VALUE_TO",
		"help":"PLG_ZLFRAMEWORK_IFT_VALUE_TO_DESC"
	}';

?>