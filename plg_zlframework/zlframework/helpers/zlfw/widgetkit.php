<?php
/**
* @package		ZL Framework
* @author    	ZOOlanders http://www.zoolanders.com
* @copyright 	Copyright (C) JOOlanders SL
* @license   	http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

/*
	Class: zlfwHelperZLUX
		A class that contains ZLUX functions
*/
class zlfwHelperWidgetkit extends AppHelper {

	/**
	 * Load ZLUX Items Manager assets
	 *
	 * @param	$settings 	AppXmlElement	The xml settings array of widget style
	 */
	public function fromSettingsToZLfield($settings)
	{
		// convert settings to array
		$json_array = array();
		foreach ($settings as $setting) {
			$type = (string) $setting->attributes()->type;

			// skip style setting
			if ($type == 'style') continue;

			// get specific options
			$specific = array();
			switch ($type) {
				case 'list':
				case 'radio':
					$options = array();
					if ($opts = $setting->children()) {
						foreach ($opts as $opt) $options[(string)$opt] = (string)$opt->attributes()->value;
						$specific = array('options' => $options);
					}
					break;
			}
			
			// prepare the JSON array
			$json = json_encode(array((string)$setting->attributes()->name => array(
				'type' => $type,
				'label' => (string)$setting->attributes()->label,
				'default' => (string)$setting->attributes()->default,
				'specific' => $specific
			)));

			$json_array[] = preg_replace(array('/^\{/', '/\}$/'), '', $json);
		}

		return implode(',', $json_array);
	}
}