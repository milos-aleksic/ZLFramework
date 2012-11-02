<?php
/**
* @package		ZL Framework
* @author    	JOOlanders, SL http://www.zoolanders.com
* @copyright 	Copyright (C) JOOlanders, SL
* @license   	http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

	return
	'{
		"_by":{
			"type":"separatedby",
			"label":"PLG_ZLFRAMEWORK_SP_BY",
			"help":"PLG_ZLFRAMEWORK_SP_BY_DESC",
			"specific":{
				"repeatable":"'.$element->config->get('repeatable').'"
			},
			"default":"separator=[ ]",
			"dependents":"_by_custom > custom"
		},
		"_by_custom":{
			"type":"textarea",
			"label":"PLG_ZLFRAMEWORK_SP_BY_CUSTOM",
			"help":"PLG_ZLFRAMEWORK_SP_BY_CUSTOM_DESC"
		},
		"_class":{
			"type":"text",
			"label":"PLG_ZLFRAMEWORK_SP_CLASS",
			"help":"PLG_ZLFRAMEWORK_SP_CLASS_DESC"
		},
		"_fixhtml":{
			"type":"checkbox",
			"label":"PLG_ZLFRAMEWORK_SP_FIX_HTML",
			"help":"PLG_ZLFRAMEWORK_SP_FIX_HTML_DESC",
			"default":"0",
			"specific":{
				"label":"Yes"
			},
			"dependents": "_fixhtml_warning > 1"
		},
		"_fixhtml_warning":{
			"type":"info",
			"specific":{
				"text":"PLG_ZLFRAMEWORK_SP_FIX_HTML_WARNING_DESC"
			}
		}
	}';