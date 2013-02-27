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
	'"date": {
		"type":"subfield",
		"path":"zlfield:json/itemfilter/_date.json.php"
	},
	"is_date":{
		"type":"hidden",
		"specific":{
			"value":"1"
		}
	}';

?>