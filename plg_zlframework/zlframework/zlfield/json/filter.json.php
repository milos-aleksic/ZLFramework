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
	'{'
		.(isset($params['types']) ?
		'"_types":{
			"type": "types",
			"label": "PLG_ZLFRAMEWORK_FT_TYPE",
			"help": "PLG_ZLFRAMEWORK_FT_TYPE_DESC",
			"specific":{
				"apps": '.json_encode($element->config->find('application._chosenapps')).',
				"multi":"1"
			}
		},' : '').'
		"_offset":{
			"type": "text",
			"label": "PLG_ZLFRAMEWORK_FT_OFFSET",
			"help": "PLG_ZLFRAMEWORK_FT_OFFSET_DESC"
		},
		"_limit":{
			"type": "text",
			"label": "PLG_ZLFRAMEWORK_FT_LIMIT",
			"help": "PLG_ZLFRAMEWORK_FT_LIMIT_DESC"
		}
	}';