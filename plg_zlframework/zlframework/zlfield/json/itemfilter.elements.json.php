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

			// custom elements can add an "group" information on the Element XML and the element will be threated correspondly

			// $xml = simplexml_load_file($this->app->path->path("elements:$el_type/$el_type.xml"));
			// $group = $xml->attributes()->zfgroup ? (string) $xml->attributes()->zfgroup : $el_type;

			// $files[] = $this->app->path->path('zoofilter:params/params.xml');

			// switch($group) {
			// 	case 'input':
			// 	case 'itemname': case 'text': case 'textpro': case 'textarea': case 'textareapro':
			// 		$files[] = $this->app->path->path('zoofilter:params/params-input.xml');
			// 		break;
				
			// 	case 'option':
			// 	case 'select': case 'selectpro': case 'checkbox': case 'radio': case 'country':
			// 		$files[] = $this->app->path->path('zoofilter:params/params-option.xml');
			// 		break;
					
			// 	case 'category':
			// 	case 'itemcategory': case 'itemcategorypro': case 'relatedcategoriespro':
			// 		$files[] = $this->app->path->path('zoofilter:params/params-category.xml');
			// 		break;
			// 	case 'relatedcategories': // not using search_index table, use Categories Element instead
			// 		$files = array($this->app->path->path('zoofilter:params/params-category-use-instead.xml'));
			// 		break;
					
			// 	case 'date':
			// 	case 'datepro':
			// 		$files[] = $this->app->path->path('zoofilter:params/params-date.xml');
			// 		break;

			// 	// Elements, without group
			// 	case 'staticcontent': // do nothing to allow using it as usual
			// 		$files = array();
			// 		break;

			// 	default: // all other elements deny setting
			// 		$files = array($this->app->path->path('zoofilter:params/params-nocompatible.xml'));
			// 		break;
			// }

			if(!empty($elements))
			{
				// process elements
				foreach ($elements as $element) {
					$name = $element->config->name ? $element->config->name : $element->getMetaData('name');
					$type_json[] =
					'"_'.$element->identifier.'_wrapper":{
						"type":"control_wrapper",
						"fields": {
							"name_separator":{
								"type":"separator",
								"layout":"section",
								"specific":{
									"title":"'.$name.' Element"
								}
							},
							"value":{
								"type":"text",
								"label":"Value"
							},
							"search_type":{
								"type":"select",
								"label":"Search Type",
								"specific":{
									"options":{
										"Exact":"exact",
										"Partial":"partial"
									}
								}
							}
						},
						"control":"'.$element->identifier.'"
					}';
				}

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
			}
		}
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