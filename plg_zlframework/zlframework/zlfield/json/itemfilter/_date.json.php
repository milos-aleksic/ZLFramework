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
				"PLG_ZLFRAMEWORK_IFT_EXACT":"exact",
				"&gt;=":"from",
				"&lt;=":"to",
				"PLG_ZLFRAMEWORK_IFT_WITHIN_RANGE_EQUAL":"range"
			}
		},
		"dependents":"value_to > range"
	},
	"value":{
		"type":"date",
		"label":"PLG_ZLFRAMEWORK_IFT_VALUE",
		"help":"PLG_ZLFRAMEWORK_IFT_VALUE_DESC",
		"specific":{
			"time":"1"
		}
	},
	"value_to":{
		"type":"date",
		"label":"PLG_ZLFRAMEWORK_IFT_VALUE_TO",
		"help":"PLG_ZLFRAMEWORK_IFT_VALUE_TO_DESC",
		"specific":{
			"time":"1"
		}
	},
	"is_date":{
		"type":"hidden",
		"specific":{
			"value":"1"
		}
	}';

?>