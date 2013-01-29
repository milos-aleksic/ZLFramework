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
	// $elements = array_filter($elements, create_function('$element', 'return $element->getMetaData("orderable") == "true";'));

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
			$type_json = array();
			
			// filter orderable elements
			// $elements = array_filter($elements, create_function('$element', 'return $element->getMetaData("orderable") == "true";'));

			if(!empty($elements))
			{
				// process elements
				foreach ($elements as $element) 
				{
					$element_json = array();
					$name = $element->config->name ? $element->config->name : $element->getMetaData('name');

					// custom elements can add an "group" information on the Element XML and the element will be threated correspondly
					$group = $element->loadXML()->attributes()->zl_filter_group;
					$group = $group ? $group : $element->getElementType();

					switch($group) {
						case 'input':
						case 'itemname': case 'text': case 'textpro': case 'textarea': case 'textareapro':
							$json_path = 'zlfield:json/itemfilter/input.json.php';
							$element_json[] = include($this->app->path->path($json_path));
							break;

						case 'option':
						case 'select': case 'selectpro': case 'checkbox': case 'radio': case 'country':
							$json_path = 'zlfield:json/itemfilter/option.json.php';
							$element_json[] = include($this->app->path->path($json_path));
							break;
							
						case 'category':
						case 'itemcategory': case 'itemcategorypro': case 'relatedcategories':
						case 'relatedcategoriespro':
							// ignore as it's filtered by general Category filter
							break;
							
						case 'date':
						case 'datepro':
							$json_path = 'zlfield:json/itemfilter/date.json.php';
							$element_json[] = include($this->app->path->path($json_path));
							break;

						// Elements without group or not supported, ignore
						case 'staticcontent':
						default:
							break;
					}

					if (!empty($element_json)) {
						$type_json[] =
						'"'.$element->identifier.'_wrapper":{
							"type":"control_wrapper",
							"fields": {
								"_filter":{
									"type": "checkbox",
									"label": "'.$name.' - '.ucfirst($element->getElementType()).' element",
									"specific":{
										"label":"PLG_ZLFRAMEWORK_FILTER"
									},
									"dependents":"'.$element->identifier.'_wrapper > 1",
									"layout":"separator"
								},
								"'.$element->identifier.'_wrapper":{
									"type":"wrapper",
									"fields": {
										'.implode(',', $element_json).',
										"logic":{
											"type":"select",
											"label":"PLG_ZLFRAMEWORK_IFT_LOGIC",
											"help":"PLG_ZLFRAMEWORK_IFT_LOGIC_DESC",
											"specific":{
												"options":{
													"PLG_ZLFRAMEWORK_AND":"AND",
													"PLG_ZLFRAMEWORK_OR":"OR"
												}
											},
											"default":"AND"
										}
									}
								}
							},
							"control":"'.$element->identifier.'"
						}';
					}
					
				} // end elements foreach

				$json[] =
				'"_'.$application->id.'_'.$type->id.'_fieldset":{
					"type":"wrapper",
					"fields": {'.implode(",", $type_json).'},
					"control":"elements",
					"specific":{
						"toggle":{
							"label":"'.$application->name.' App / '.$type->name.' Type"
						}
					},
					"layout":"fieldset"
				}';

			} // end elements

		} // end Type foreach
	}

	// return json string
	return 
	'{"fields": {

		'. /* options */ '
		"_options_wrapper": {
			"type":"wrapper",
			"fields": {'.implode(",", $json).'}
		}

	}}';
?>