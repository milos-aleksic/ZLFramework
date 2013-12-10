<?php
/**
* @package		ZL Framework
* @author    	JOOlanders, SL http://www.zoolanders.com
* @copyright 	Copyright (C) JOOlanders, SL
* @license   	http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

	// return json string
	return 
	'{"fields": {
		
		"type":{
			"type":"select",
			"label":"PLG_ZLFRAMEWORK_IFT_FILTER_TYPE",
			"help":"PLG_ZLFRAMEWORK_IFT_FILTER_TYPE_DESC",
			"specific":{
				"options":{
					"PLG_ZLFRAMEWORK_EXACT":"exact",
					"PLG_ZLFRAMEWORK_FROM":"from",
					"PLG_ZLFRAMEWORK_TO":"to",
					"PLG_ZLFRAMEWORK_PERIOD":"period"
				}
			},
			"dependents":"period_mode > period"
		},
		"period_mode":{
			"type":"select",
			"label":"PLG_ZLFRAMEWORK_IFT_MODE",
			"help":"PLG_ZLFRAMEWORK_IFT_PERIOD_MODE_DESC",
			"specific":{
				"options":{
					"PLG_ZLFRAMEWORK_IFT_STATIC":"static",
					"PLG_ZLFRAMEWORK_IFT_DYNAMIC":"dynamic"
				}
			}
		},
		"value":{
			"type":"date",
			"label":"PLG_ZLFRAMEWORK_IFT_VALUE",
			"help":"PLG_ZLFRAMEWORK_IFT_VALUE_DESC",
			"dependent":"type != period OR period_mode == static"
		},
		"value_to":{
			"type":"date",
			"label":"PLG_ZLFRAMEWORK_IFT_VALUE_TO",
			"help":"PLG_ZLFRAMEWORK_IFT_VALUE_TO_DESC",
			"dependent":"type == period AND period_mode == static"
		},
		"dynamic_period_wrapper":{
			"type":"wrapper",
			"dependent":"type == period AND period_mode == dynamic",
			"fields": {
				"interval":{
					"type":"text",
					"label":"PLG_ZLFRAMEWORK_IFT_INTERVAL",
					"help":"PLG_ZLFRAMEWORK_IFT_INTERVAL_DESC"
				},
				"interval_unit":{
					"type":"select",
					"label":"PLG_ZLFRAMEWORK_IFT_INTERVAL_UNIT",
					"specific":{
						"options":{
							"Year":"YEAR",
							"Month":"MONTH",
							"Week":"WEEK",
							"Day":"DAY",
							"Hour":"HOUR",
							"Minute":"MINUTE",
							"Second":"SECOND"
						}
					}
				}
			}
		}
							
	}}';

?>