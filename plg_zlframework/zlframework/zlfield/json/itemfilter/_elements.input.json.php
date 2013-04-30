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
				"PLG_ZLFRAMEWORK_IFT_EXACT_MATCH":"exact",
				"PLG_ZLFRAMEWORK_IFT_EXACT_PHRASE":"exact_phrase",
				"PLG_ZLFRAMEWORK_IFT_ALL_WORDS":"all_words",
				"PLG_ZLFRAMEWORK_IFT_ANY_WORD":"any_word",
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
		"dependents":"convert !> partial OR exact_phrase OR all_words OR any_word | value_to > rangeequal OR range OR outofrangeequal OR outofrange"
	},
	"convert":{
		"type": "select",
		"label": "PLG_ZLFRAMEWORK_IFT_CONVERSION",
		"help": "PLG_ZLFRAMEWORK_IFT_CONVERSION_DESC",
		"specific": {
			"options": {
				"PLG_ZLFRAMEWORK_IFT_INTEGRER":"SIGNED",
				"PLG_ZLFRAMEWORK_IFT_DECIMAL":"DECIMAL",
				"PLG_ZLFRAMEWORK_IFT_DATE":"DATE",
				"PLG_ZLFRAMEWORK_IFT_DATE_TIME":"DATETIME"
			}
		}
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