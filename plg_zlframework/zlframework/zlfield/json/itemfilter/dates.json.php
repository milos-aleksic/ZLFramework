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

								"date": {
									"type":"subfield",
									"path":"zlfield:json/itemfilter/_date.json.php"
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

								"date": {
									"type":"subfield",
									"path":"zlfield:json/itemfilter/_date.json.php"
								}
								
							}
						}
					}
				},

				'/* Published up */.'
				"published_up_wrapper":{
					"type":"control_wrapper",
					"control":"published",
					"fields": {
						"_filter":{
							"type":"checkbox",
							"label":"PLG_ZLFRAMEWORK_PUBLISHED_UP",
							"specific":{
								"label":"PLG_ZLFRAMEWORK_FILTER"
							},
							"dependents":"published_wrapper > 1",
							"layout":"separator"
						},
						"published_wrapper":{
							"type":"wrapper",
							"fields": {

								"date": {
									"type":"subfield",
									"path":"zlfield:json/itemfilter/_date.json.php"
								}
								
							}
						}
					}
				},

				'/* Published down */.'
				"published_down_wrapper":{
					"type":"control_wrapper",
					"control":"published_down",
					"fields": {
						"_filter":{
							"type":"checkbox",
							"label":"PLG_ZLFRAMEWORK_PUBLISHED_DOWN",
							"specific":{
								"label":"PLG_ZLFRAMEWORK_FILTER"
							},
							"dependents":"published_down_wrapper > 1",
							"layout":"separator"
						},
						"published_down_wrapper":{
							"type":"wrapper",
							"fields": {

								"date": {
									"type":"subfield",
									"path":"zlfield:json/itemfilter/_date.json.php"
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