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
	$allowed_types = array_filter((array)$psv->get('_chosentypes', array()));

	// get parent apps value
	$apps = array_filter((array)$psv->get('_chosenapps', array()));

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


	/* add general options */
	$json[] =
	'"_reversed":{
		"type":"checkbox",
		"label":"PLG_ZLFRAMEWORK_REVERSE",
		"specific": {
			"label": "JYES",
			"value":"_reversed"
		}
	},
	"_priority":{
		"type":"checkbox",
		"label":"PLG_ZLFRAMEWORK_PRIORITY",
		"specific": {
			"label": "JYES",
			"value":"_priority"
		}
	}';


	/* add core elements */
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
		"label":"PLG_ZLFRAMEWORK_CORE",
		"specific": {
			"options":'.json_encode($options).'
		}
	}';


	/* add type elements */
	foreach ($applications as $application)
	{
		// get types
		$types = $application->getTypes();

		// filter types
		$types = !empty($allowed_types) ? array_filter($types, create_function('$type', 'return in_array($type->id, array(\''.implode('\', \'', $allowed_types).'\'));')) : $types;

		$type_json = array();
		if(!empty($types)) foreach ($types as $type)
		{
			$element_json = array();
			$elements = $type->getElements();
			$options = array('- '.JText::_('PLG_ZLFRAMEWORK_SELECT_ELEMENT').' -' => false);
			
			// filter orderable elements
			$elements = array_filter($elements, create_function('$element', 'return $element->getMetaData("orderable") == "true";'));

			if(!empty($elements))
			{
				// create element options
				foreach ($elements as $element) {
					$options[$element->config->name ? $element->config->name : $element->getMetaData('name')] = $element->identifier;
				}

				// elements
				$type_json[] =
				'"_'.$type->id.'":{
					"type":"select",
					"label":"'.$type->name.'",
					"specific": {
						"options":'.json_encode($options).'
					}
				}';

			} // end if Elements
		} // end Type foreach

		$json[] =
		'"_'.$application->id.'_fieldset":{
			"type":"wrapper",
			"fields": {'.implode(",", $type_json).'},
			"specific":{
				"toggle":{
					"label":"PLG_ZLFRAMEWORK_NAME_APP_ELEMENTS||'.$application->name.'"
				}
			},
			"layout":"fieldset"
		}';

	} // end App foreach

	// JSON
	return 
	'{"fields": {
		
		'. /* random */ '
		"_random":{
			"type":"checkbox",
			"label":"PLG_ZLFRAMEWORK_RANDOM",
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