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
	Class: zlfwHelperXml
		The ZLFW xml helper class
*/
class zlfwHelperXml extends AppHelper {

	/**
	 * This method adds parmeters to the elements on the fly, reading them from an xml file
	 * 
	 * @param	$config 	AppXmlElement	The xml config of the element
	 * @param	$file		array			The file where the extra params are stored (absolute path)
	 * @param	$group		string			The group we wonna edit
	 * @param	$empty		boolean			If true the params will be deleted before adding new ones
	 */
	public static function addElementParams(&$config, $files, $group='render')
	{
		if(!empty($files))
		{
			$zoo = App::getInstance('zoo');

			// save current parameters
			$old_params = $config->getXML('render') ? $config->getXML('render') : array();
			$main_params = $last_params = '';
			for($i=0; $i < count($old_params->param); $i++){
				$name = (string)$old_params->param[$i]->attributes()->name;
				if($name == 'zoolingual' || $name == 'zooaksubs' || $name == 'zooaccess'){
					$last_params .= $old_params->param[$i]->asXML();
				} else {
					$main_params .= $old_params->param[$i]->asXML();
				}
			}

			// empty all params
			$xml = simplexml_load_string('<?xml version="1.0" encoding="utf-8"?><wrapper><params group="render"></params></wrapper>');
			$config->loadXML($xml);

			// set main params
			$new_param = simplexml_load_string('<?xml version="1.0" encoding="utf-8"?><wrapper><params group="render">'.$main_params.'</params></wrapper>');
			$new_param->addAttribute('group', 'render');
			$config->addXML($new_param);

			// add new params
			foreach($files as $file){
				$xml = simplexml_load_file($file);
				$config->addXML($xml);
			}

			// set integration params
			$new_param = simplexml_load_string('<?xml version="1.0" encoding="utf-8"?><wrapper><params group="render">'.$last_params.'</params></wrapper>');
			$new_param->addAttribute('group', 'render');
			$config->addXML($new_param);
		}
	}

	/**
	 * This method adds parmeters to the elements on the fly, reading them from an xml file
	 * 
	 * @param	$config 	AppXmlElement	The xml config of the element
	 * @param	$file		array			The file where the extra params are stored (absolute path)
	 * @param	$group		string			The group we wonna edit
	 * @param	$empty		boolean			If true the params will be deleted before adding new ones
	 */
	public static function loadElementParams(&$config, $files, $group='render')
	{
		if(!empty($files))
		{
			$zoo = App::getInstance('zoo');
			
			// save current parameters
			$old_params = $config->getXML('render') ? $config->getXML('render') : array();

			// empty all params
			$xml = simplexml_load_string('<?xml version="1.0" encoding="utf-8"?><wrapper><params group="render"></params></wrapper>');
			$config->loadXML($xml);

			// add zoofilter params
			foreach($files as $file){
				$xml = simplexml_load_file($file);
				$config->loadXML($xml);
			}

			// recover integration params
			$params = '';
			for($i=0; $i < count($old_params->param); $i++){
				$name = (string)$old_params->param[$i]->attributes()->name;
				if($name == 'zoolingual' || $name == 'zooaksubs' || $name == 'zooaccess'){
					$params .= $old_params->param[$i]->asXML();
				}
			}

			$new_param = simplexml_load_string('<?xml version="1.0" encoding="utf-8"?><wrapper><params group="render">'.$params.'</params></wrapper>');
			$new_param->addAttribute('group', 'render');
			$config->addXML($new_param);
		}
	}

	/**
	 * Convert XML node to array data
	*/
	public function XMLtoArray($node, $isOption=false)
	{ 
		$fields = array(); $i = 0;
		if(count($node->children())) foreach($node->children() as $child)
		{
			// get field atributes
			$attrs = (array)$child->attributes();
			$attrs = !empty($attrs) ? array_shift($attrs) : $attrs;

			if($child->getName() == 'options')
			{
				$fields[$i]['name'] =  $child->getName();
				$fields[$i]['attributes'] = $this->XMLtoArray($child, true);
			}
			else if($isOption)
			{
				$fields[(string)$child] = (string)$child->attributes()->value;
			}
			else {
				$fields[$i]['name'] = $child->getName();
				$fields[$i]['attributes'] = $attrs;
				$fields[$i]['childs'] = $this->XMLtoArray($child);
			}

			$i++;
		}
		return $fields;
	}
}