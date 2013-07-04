<?php
/**
* @package		ZL Elements
* @author    	JOOlanders, SL http://www.zoolanders.com
* @copyright 	Copyright (C) JOOlanders, SL
* @license   	http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

// load config
require_once(JPATH_ADMINISTRATOR . '/components/com_zoo/config.php');

	// load WK settings
	$widget_xml = simplexml_load_file($this->app->path->path('media:widgetkit/widgets/slideset/slideset.xml'));
	$style_xml = simplexml_load_file($this->app->path->path('media:widgetkit/widgets/slideset/styles/default/config.xml'));

	return 
	'{"fields": {

		"_style":{
			"type":"layout",
			"label":"Style",
			"default":"default",
			"specific": {
				"path":"zlfw:widgetkit\/widgets\/slideset",
				"mode":"folders"
			},
			"childs":{
				"loadfields": {

					"widget_separator":{
						"type":"separator",
						"text":"Settings",
						"layout":"subsection"
					},
					"_widget_settings": {
						"type":"wrapper",
						"fields": {' . $this->app->zlfw->widgetkit->fromSettingsToZLfield($widget_xml->xpath('settings/setting')) . '},
						"control":"settings"
					},
					"_widget_style_settings": {
						"type":"wrapper",
						"fields": {' . $this->app->zlfw->widgetkit->fromSettingsToZLfield($style_xml->xpath('settings/setting')) . '},
						"control":"settings"
					}

				}
			}
		}

	}}';