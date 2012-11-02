<?php
/**
* @package		ZL Framework
* @author    	JOOlanders, SL http://www.zoolanders.com
* @copyright 	Copyright (C) JOOlanders, SL
* @license   	http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

// load config
require_once(JPATH_ADMINISTRATOR . '/components/com_zoo/config.php');

	return 
	'{
		"_render":{
			"type": "select",
			"label": "Render",
			"specific":{
				"options":{
					"Text/Plugin":"text",
					"Link":"link",
					"Item":"item",
					"Module":"module",
					"Iframe":"iframe"
				}
			},
			"dependents": "text_wrapper > text | link_wrapper > link | item_wrapper > item | _module > module | iframe_wrapper > iframe"
		},
		"_options_sep":{
			"type": "separator",
			"text": "Options"
		},
		
		'./* Text */'
		"text_wrapper":{
			"type": "wrapper",
			"fields": {
				"_text":{
					"type": "textarea",
					"label": "Text"
				},
				"_loadplugins":{
					"type": "checkbox",
					"label": "Plugins",
					"specific":{
						"label": "Evaluate on render"
					}
				}
			}
		},
		
		'./* Link */'
		"link_wrapper":{
			"type": "wrapper",
			"fields": {
				"_linktype":{
					"type": "select",
					"label": "Type",
					"specific":{
						"options":{
							"Item":"item",
							"Category":"category",
							"Custom":"custom"
						}
					},
					"dependents": "linkitem_wrapper > item | linkcat_wrapper > category | linkcustom_wrapper > custom"
				},
				
				'./* Link common Options */'
				"_linktext":{
					"type": "text",
					"label": "Text"
				},
				"_linktitle":{
					"type": "text",
					"label": "Title"
				},						
				"_linktarget":{
					"type": "checkbox",
					"label": "New Window",
					"specific":{
						"label": "JYES"
					}
				},
				"_rel":{
					"type": "text",
					"label": "Rel"
				},

				'./* Item Link */'
				"linkitem_wrapper":{
					"type": "wrapper",
					"fields": {
						"_linkitem_sep":{
							"type": "separator",
							"text": "Item Options"
						},
						"_linkitemsource":{
							"type": "select",
							"label": "Item Source",
							"default": "current",
							"specific":{
								"options":{
									"Current":"current",
									"Specified":"specified"
								}
							},
							"dependents": "_linkitemid > specified"
						},
						"_linkitemid":{
							"type": "text",
							"label": "Item ID"
						},
						"_linkitemlayout":{
							"type": "itemLayoutList",
							"label": "Layout",
							"help":"PLG_ZLFRAMEWORK_SC_ITEM_LINK_LAYOUT",
							"default":"full"
						}
					}
				},
				
				'./* Category Link */'
				"linkcat_wrapper":{
					"type": "wrapper",
					"fields": {
						"_linkitem_sep":{
							"type": "separator",
							"text": "Category Options"
						},
						"_linkcatid":{
							"type": "text",
							"label": "Category ID"
						}
					}
				},
				
				'./* Link Custom */'
				"linkcustom_wrapper":{
					"type": "wrapper",
					"fields": {
						"_linkurl":{
							"type": "text",
							"label": "URL"
						}
					}
				}
				
			}
		},
		
		'./* Item */'
		"item_wrapper":{
			"type": "wrapper",
			"fields": {
				"_itemlayout":{
					"type": "itemLayoutList",
					"label": "Item Layout"
				},
				"_itemsource":{
					"type": "select",
					"label": "Item Source",
					"default": "current",
					"specific":{
						"options":{
							"Current":"current",
							"Specified":"specified"
						}
					},
					"dependents": "_itemid > specified"
				},
				"_itemid":{
					"type": "text",
					"label": "Item ID"
				}
			}
		},
		
		
		'./* Module */'
		"_module":{
			"type": "modulelist",
			"label": "Module"
		},
		
		
		'./* Iframe */'
		"iframe_wrapper":{
			"type": "wrapper",
			"fields": {
				"_iframe_content":{
					"type": "select",
					"label": "Render",
					"specific":{
						"options":{
							"Item":"item",
							"Custom URL":"custom_url"
						}
					},
					"dependents": "item_iframe_wrapper > item | _iframe_custom_url > custom_url"
				},
				
				'./* Item Iframe */'
				"item_iframe_wrapper":{
					"type": "wrapper",
					"fields": {
						"_iframe_itemlayout":{
							"type": "itemLayoutList",
							"label": "Item Layout"
						},
						"_iframe_itemsource":{
							"type": "select",
							"label": "Item Source",
							"default": "current",
							"specific":{
								"options":{
									"Current":"current",
									"Specified":"specified"
								}
							},
							"dependents": "_iframe_itemid > specified"
						},
						"_iframe_itemid":{
							"type": "text",
							"label": "Item ID"
						}
					}
				},
				
				'./* Iframe URL */'
				"_iframe_custom_url":{
					"type": "text",
					"label": "URL"
				}
			}
		}
		
	}';