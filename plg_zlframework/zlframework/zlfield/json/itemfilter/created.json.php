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
		"dates_wrapper":{
			"type":"wrapper",
			"fields": {

				'/* Created */.'
				"created_wrapper":{
					"type":"control_wrapper",
					"control":"created",
					"fields": {
						"_filter":{
							"type":"checkbox",
							"label":"Created",
							"specific":{
								"label":"PLG_ZLFRAMEWORK_FILTER"
							},
							"dependents":"created_wrapper > 1",
							"layout":"separator"
						},
						"created_wrapper":{
							"type":"wrapper",
							"fields": {

								"type":{
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
									"help":"PLG_ZLFRAMEWORK_IFT_VALUE_DESC"
								},
								"value_to":{
									"type":"date",
									"label":"PLG_ZLFRAMEWORK_IFT_VALUE_TO",
									"help":"PLG_ZLFRAMEWORK_IFT_VALUE_TO_DESC"
								}
								
							}
						}
					}
				},

				'/* Modified */.'
				"modified_wrapper":{
					"type":"control_wrapper",
					"control":"modified",
					"fields": {
						"_filter":{
							"type":"checkbox",
							"label":"Modified",
							"specific":{
								"label":"PLG_ZLFRAMEWORK_FILTER"
							},
							"dependents":"modified_wrapper > 1",
							"layout":"separator"
						},
						"modified_wrapper":{
							"type":"wrapper",
							"fields": {

								"type":{
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
									"help":"PLG_ZLFRAMEWORK_IFT_VALUE_DESC"
								},
								"value_to":{
									"type":"date",
									"label":"PLG_ZLFRAMEWORK_IFT_VALUE_TO",
									"help":"PLG_ZLFRAMEWORK_IFT_VALUE_TO_DESC"
								}
								
							}
						}
					}
				}
			
			},
			"control":"dates",
			"layout":"fieldset",
			"specific":{
				"toggle":{
					"label":"Dates"
				}
			}
		}
	}}';

?>