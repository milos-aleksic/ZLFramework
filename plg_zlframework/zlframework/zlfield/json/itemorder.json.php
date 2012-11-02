<?php
/**
* @package		ZL Framework
* @author    	JOOlanders, SL http://www.zoolanders.com
* @copyright 	Copyright (C) JOOlanders, SL
* @license   	http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

	$node = $this->data->create($node);
	$psv  = $this->data->create($psv);

	// init vars
	$json = array();
	$allowed_types = (array)$psv->get('_chosentypes', array());

	// get parent apps value
	$apps = $psv->get('_chosenapps', array());

	// get apps
	$applications = array();
	if (!empty($apps)) {
		$applications = $this->app->zlfw->getApplications($apps);
	} else if ($group = $this->app->request->getString('group', false)) {
		$applications = array($this->app->object->create('Application')->setGroup($group));
	} else {
		// get all apps then
		foreach ($this->app->table->application->all(array('order' => 'name')) as $app){
			$applications[] = $app;
		}
	}

	// depricated method beacuse in modules for ex will not work
	// $applications = array($this->app->zoo->getApplication()); 

	// check if at least one app is loaded
	if (empty($applications)) return;

	// add core elements
	$elements = $this->app->object->create('Type', array('_core', $applications[0]))->getCoreElements();
	
		// filter orderable elements
		$elements = array_filter($elements, create_function('$element', 'return $element->getMetaData("orderable") == "true";'));

		$options = array();
		foreach ($elements as $element) {
			$options[$element->config->name ? $element->config->name : $element->getMetaData('name')] = $element->identifier;
		}
		
		if ($node->get('add_default')) {
			array_unshift($options, array(JText::_('default') => ''));
		}

		$json[] =
		'"_core":{
			"type":"select",
			"label":"'.JText::_('Core').'",
			"specific": {
				"options":'.json_encode($options).'
			}
		}';


	// add type elements
	foreach ($applications as $application)
	{
		// get types
		$types = $application->getTypes();

		// filter types
		$types = !empty($allowed_types) ? array_filter($types, create_function('$type', 'return in_array($type->id, array(\''.implode('\', \'', $allowed_types).'\'));')) : $types;

		if(!empty($types)) foreach ($types as $type)
		{
			$elements = $type->getElements();
			$options = array('- '.JText::_('Select Element').' -' => false);
			
			// filter orderable elements
			$elements = array_filter($elements, create_function('$element', 'return $element->getMetaData("orderable") == "true";'));

			if(!empty($elements))
			{
				// create element options
				foreach ($elements as $element) {
					$options[$element->config->name ? $element->config->name : $element->getMetaData('name')] = $element->identifier;
				}

				// app separator
				$json[] =
				'"_'.$application->id.'_separator":{
					"type":"separator",
					"text":"'.$application->name.' App"
				}';

				// elements
				$json[] =
				'"_'.$type->id.'":{
					"type":"select",
					"label":"'.$type->name.'",
					"specific": {
						"options":'.json_encode($options).'
					}
				}';
			}
		}
	}

	// app separator
	$json[] =
	'"_general_separator":{
		"type":"separator",
		"text":"General"
	}';
	
	// reversed
	$json[] =
	'"_reversed":{
		"type":"checkbox",
		"label":"'.JText::_('Reverse').'",
		"specific": {
			"label": "JYES",
			"value":"_reversed"
		}
	}';

	// priority
	$json[] =
	'"_priority":{
		"type":"checkbox",
		"label":"'.JText::_('Priority').'",
		"specific": {
			"label": "JYES",
			"value":"_priority"
		}
	}';


	// return json string
	return 
	'{"fields": {
		
		'. /* random */ '
		"_random":{
			"type":"checkbox",
			"label":"'.JText::_('Random').'",
			"specific": {
				"label": "JYES",
				"value":"_random"
			},
			"dependents":"_options_wrapper !> 1"
		},

		'. /* options */ '
		"_options_wrapper": {
			"type":"wrapper",
			"fields": {'.implode(",", $json).'}
		}

	}}';
?>