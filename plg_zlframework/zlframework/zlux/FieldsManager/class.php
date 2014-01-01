<?php
/**
* @package		ZL Framework
* @author    	JOOlanders, SL http://www.zoolanders.com
* @copyright 	Copyright (C) JOOlanders, SL
* @license   	http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

/*
   Class: zluxFields
*/
class zluxFields extends zlux
{
	/**
	 * The list of loaded fields renderers
	 * 
	 * @var array
	 */
	protected $_engines = array();

	/**
	 * Get a fields engine
	 * 
	 * @param string $name The name of the engine to retrieve
	 * 
	 * @return object The zlux class
	 */
	public function get($name)
	{	
		// load zlux class
		$class = 'zluxFieldsEngine'.ucfirst($name);
		$this->app->loader->register($class, 'zlfw:zlux/FieldsManager/engine/'.strtolower($name).'.php');
		
		// add class, if not exists
		if (!isset($this->_engines[$name])) {
			$this->_engines[$name] = new $class($this->app);
		}

		return $this->_engines[$name];
	}
	
	/**
	 * Magic method to get a fields engine
	 * 
	 * @param string $name The name of the engine
	 * 
	 * @return zluxFieldsEngine The engine object
	 */
	public function __get($name) {
		return $this->get($name);
	}

	/**
	 * Class constructor
	 *
	 * @param string $app App instance.
	 */
	public function __construct($app){
		parent::__construct($app);

		// register paths
		$this->app->path->register(dirname(__FILE__), 'zlux.fields');
	}
}

/*
   Class: zluxFieldsEngine
*/
abstract class zluxFieldsEngine
{
	/**
	 * Reference to the global App class
	 *
	 * @var App
	 */
	public $app;

	/**
	 * Reference to the request Helper
	 *
	 * @var RequestHelper
	 */
	public $request;

	/**
	 * Reference to the field values
	 *
	 * @var array
	 */
	public $values;

	/**
	 * Class constructor
	 *
	 * @param string $app App instance.
	 */
	public function __construct($app)
	{
		// init vars
		$this->app		= $app;
		$this->request	= $app->request;
	}

	/*
		Function: setValues
	*/
	/**
	 * setValues
	 *
	 * @param array $values Field values
	 */
	public function setValues($values)
	{
		$this->values = $this->app->data->create($values);
	}

	/*
		Function: render - Returns the result from _parseJSON wrapped with main html dom
	*/
	public function render($parseJSONargs, $ajaxargs=array(), $class='', $ajaxLoading=false)
	{
		// load assets
		$this->loadAssets();

		// init vars
		$html = array();
		$ajaxargs = !empty($ajaxargs) ? json_encode($ajaxargs) : false;
		$class = $class ? ' '.$class : '';

		return $parseJSONargs ? call_user_func_array(array($this, "parseJSON"), $parseJSONargs) : '';
	}
	
	/*
		Function: parseJSON - Returns result html string from fields declared in json string/arrat format
		Params: 
			$json String		- path to json file or a json formated string
			$ctrl String 		- control
			$psv Array			- All Parent Fields Values
			$pid String			- Parent Field ID
			$arguments Array	- additional arguments the fields could need -> $ajaxargs var will be passed trough ajax call
	*/
	public function parseJSON($json, $ctrl, $psv=array(), $pid='', $returnArray=false, $arguments=array())
	{
		// extract the arguments
		extract($arguments, EXTR_OVERWRITE);

		/* update params if provided */
		// if(isset($addparams)){
		// 	$this->params = $this->app->data->create( $addparams );
		// }

		// convert to array
		settype($json, 'array');

		// if paths provided retrieve json and convert to array
		if (isset($json['paths'])){
			foreach (array_map('trim', explode(',', $json['paths'])) as $pt) if ($path = $this->app->path->path($pt)) // php files only
			{
				if(is_file($path)){
					/* IMPORTANT - this vars are necesary for include function */
					$subloaded = true; // important to let know it's subloaded
					$psv = $this->app->data->create($psv);
					$json = json_decode(include($path), true);
					break;
				}
			}
		}
		else if (!isset($json['fields'])) // is raw json string then
		{
			$json = json_decode($json[0], true);
		}

		// let's be sure is well formated
		$json = isset($json['fields']) ? $json : array('fields' => $json);

		// process fields if any
		if (isset($json['fields']))
		{			
			$ctrl = $ctrl.(isset($json['control']) ? "[".$json['control']."]" : ''); // ctrl could grow on each iterate
			
			// iterate fields
			$result = $this->_parseFields($json['fields'], $ctrl, $psv, $pid, false, $arguments);

			return $returnArray ? $result : implode("\n", $result);
		} 
		else if($json && false)
		{
			JFactory::getApplication()->enqueueMessage( JText::_('JSON string with bad format or file not found - ') . implode(' | ', $json) );
		}

		return null;
	}
	
	// $fields, $control, $parentsValue, $parentID
	private function _parseFields($fields, $ctrl, $psv, $pid, $returnArray, $arguments)
	{
		$result = array();
		foreach ($fields as $id => $fld) {
			$fld = $this->app->data->create($fld);

			// adjust ctrl
			if($adjust = $fld->get('adjust_ctrl')){
				$final_ctrl = preg_replace($adjust['pattern'], $adjust['replacement'], $ctrl);
			} else {
				$final_ctrl = $ctrl;
			}

			// wrapper control if any
			$final_ctrl = $fld->get('control') ? $final_ctrl.'['.$fld->get('control', '').']' : $final_ctrl;

			$field_type = $fld->get('type', '');
			switch ($field_type)
			{
				case 'separator':
					// set layout
					$layout = $fld->get('layout', 'default');

					// backward compatibility
					if ($fld->get('text')) {
						$layout = $fld->get('big') ? 'section' : 'subsection';
						$fld->set('specific', array('title' => $fld->get('text')));
					}

					// render layout
					$field = $fld;
					if ($layout = $this->getLayout("separator/{$layout}.php")) {
						$result[] = $this->app->zlfw->renderLayout($layout, compact('id', 'field'));
					}
					break;

				case 'wrapper':
				case 'control_wrapper': case 'toggle': case 'fieldset': // backward compatibility

					// get content
					$content = array_filter($this->parseJSON(json_encode(array('fields' => $fld->get('fields'))), $final_ctrl, $psv, $pid, true, $arguments));
					
					// abort if no minimum fields reached
					if (count($content) == 0 || count($content) < $fld->get('min_count', 0)) continue;

					// init vars
					$layout = $fld->get('layout', 'default');
					$content = implode("\n", $content);

					// backward compatibility
					if ($field_type == 'control_wrapper') {
						$result[] = $content;
						continue;
					} else if ($field_type == 'fieldset'){
						$layout = 'fieldset';
					} else if ($field_type == 'toggle'){
						$layout = 'toggle';
					}

					// render layout
					if ($this->renderIf($fld->get('renderif')) // render check
							&& $layout = $this->getLayout("wrapper/{$layout}.php")) {

						$result[] = $this->app->zlfw->renderLayout($layout, compact('id', 'content', 'fld'));
					}
					
					break;
				case 'subfield':
					// get parent fields data
					$psv = $this->app->data->create($psv);

					// replace path {value} if it's string
					$paths = is_string($psv->get($pid)) ? str_replace('{value}', basename($psv->get($pid), '.php'), $fld->get('path')) : $fld->get('path');

					// replace parent values in paths
					foreach ((array)$psv as $key => $pvalue) {
						$paths = str_replace('{'.$key.'}', basename(@(string)$pvalue, '.php'), $paths);
					}

					// build json paths
					$json = array('paths' => $paths);

					// create possible arguments objects
					if($field_args = $fld->get('arguments')) foreach($field_args as $name => $args){
						$arguments[$name] = $this->app->data->create($args);
					}

					// parse fields
					if($res = $this->parseJSON($json, $final_ctrl, $psv, $pid, false, $arguments)){
						$result[] = $res;
					}

					break;
				default:
					// init vars
					$value = null;

					// check old values
					if($fld->get('check_old_value'))
					{
						// adjust ctrl for old value
						$old_value_ctrl = $final_ctrl;
						if($adjust = $fld->find('check_old_value.adjust_ctrl')) $old_value_ctrl = preg_replace($adjust['pattern'], $adjust['replacement'], $old_value_ctrl);
						// get old value
						// $value = $this->getFieldValue($fld->find('check_old_value.id'), null, $old_value_ctrl);
						// translate old value
						if($translations = $fld->find('check_old_value.translate_value')){
							foreach($translations as $key => $trans) if($value == $key){
								if($trans == '_SKIPIT_'){
									$value = null;
									break;
								} else {
									$value = $trans;
									break;
								}
							}
						}
					}

					// get value from config instead
					if($fld->get('data_from_config'))
					{
						$path = preg_replace( // create equivalent path to the config values
							array('/^('.$this->element->identifier.')/', '/(positions\[\S+\])\[(\d+)\]|elements\[[^\]]+\]|\]$/', '/(\]\[|\[|\])/', '/^\./'),
							array('', '', '.', ''),
							$final_ctrl
						);
						$path = "{$this->element->identifier}.{$path}";
						$value = $this->config->find($path.".$id", $value);
					}
					else
					{
						// get value
						// $value = strlen($value) ? $value : $this->getFieldValue($id, $fld->get('default'), $final_ctrl, $fld->get('old_id', false));
					}

					// get inital value dinamicly
					if (empty($value) && $fld->get('request_value')) {

						// from url
						if ($fld->find('request_value.from') == 'url') {
							$value = $this->request->get($fld->find('request_value.param'), $fld->find('request_value.type'), $fld->find('request_value.default'));
						}
					}
					
					// set specific
					$specific = $fld->get('specific', array()); 

					 // if ($psv) $specific['parents_val'] = $psv;
									
					// render field
					$result[] = $this->renderField($field_type, $id, $final_ctrl.'['.$id.']', $value, $specific);

					// load childs
					if($childs = $fld->find('childs.loadfields'))
					{
						// create parent values
						$pid = $id;

						// add current value to parents array, if empty calculate it
						// $psv[$id] = $value ? $value : $this->field($params, $value, true); 
						$psv[$id] = $value ? $value : null; 

						$p_task = $this->request->getVar('parent_task') ? $this->request->getVar('parent_task') : $this->request->getVar('task'); // parent task necesary if double field load ex: layout / sublayout
						$url = $this->app->link(array('controller' => 'zlframework', 'format' => 'raw', 'type' => $this->type, 'layout' => $this->layout, 'group' => $this->group, 'path' => $this->request->getVar('path'), 'parent_task' => $p_task, 'zlfieldmode' => $this->mode), false);

						// rely options to be used by JS later on
						$json = $fld->find('childs.loadfields.subfield', '') ? array('paths' => $fld->find('childs.loadfields.subfield.path')) : array('fields' => $childs);
						
						$pr_opts = json_encode(array('id' => $id, 'url' => $url, 'psv' => $psv, 'json' => json_encode($json)));
						
						// all options are stored as data on DOM so can be used from JS
						$loaded_fields = $this->parseJSON(array('fields' => $childs), $final_ctrl, $psv, $pid, false, $arguments);
						$result[] = '<div class="placeholder" data-relieson-type="'.$field_type.'"'.($pr_opts ? " data-relieson='{$pr_opts}'" : '').' data-control="'.$final_ctrl.'" >';
						$result[] = $loaded_fields ? '<div class="loaded-fields">'.$loaded_fields.'</div>' : '';
						$result[] = '</div>';
					}
			}
		}
		return $result;
	}

	/*
		Function: parseArray - returns an json formated string from an array
			The array is the XML data standarized by the type inits
	*/
	function parseArray($master, $isChild=false, $arguments=array())
	{
		$fields = array();
		if(count($master)) foreach($master as $val)
		{
			// init vars
			$name   = $val['name'];
			$attrs  = $val['attributes'];
			$childs = isset($val['childs']) ? $val['childs'] : array();

			if($name == 'loadfield')
			{
				// get field from json
				if($json = $this->app->path->path("zlfield:json/{$attrs['type']}.json.php")){
					// extract the arguments
					extract($arguments, EXTR_OVERWRITE);

					// parse all subfiels and set as params
					$result = $this->parseArray($childs, true, $arguments);
					$params = $this->app->data->create($result);
					
					// remove the {} from json string and proceede
					$fields[] = preg_replace('(^{|}$)', '', include($json));
				} else {
					$fields[] = '"notice":{"type":"info","specific":{"text":"'.JText::_('PLG_ZLFRAMEWORK_ZLFD_FIELD_NOT_FOUND').'"}}';
				}
			}
			else if($isChild)
			{
				$fields = array_merge($fields, array($name => array_merge($attrs, $this->parseArray($childs, true, $arguments))));
			}
			else // setfield
			{
				// get field id and remove from attributes
				$id = $attrs['id'];
				unset($attrs['id']);

				// merge val attributes
				$field = array($id => array_merge($attrs, $this->parseArray($childs, true, $arguments)));

				// remove the {} created by the encode and proceede
				$fields[] = preg_replace('(^{|}$)', '', json_encode($field));
			}
		}
		return $fields;
	}

	/*
		Function: renderIf 
			Render or not depending if specified extension is instaled and enabled
		Params
			$extensions - array, Ex: [com_widgetkit, 0]
	*/
	public function renderIf($extensions)
	{
		$render = 1;
		if (!empty($extensions)) foreach ($extensions as $ext => $action)
		{
			if ($this->app->zlfw->checkExt($ext)){
				$render = $action;
			} else {
				$render = !$action;
			}
		}
		return $render; // if nothing to check, render as usual
	}
	
	/*
		Function: replaceVars - Returns html string with all variables replaced
	*/
	public function replaceVars($vars, $string)
	{
		$vars = is_string($vars) ? explode(',', trim($vars, ' ')) : $vars;
		
		$pattern = $replace = array(); $i=1;
		foreach((array)$vars as $var){
			$pattern[] = "/%s$i/"; $i++;
			$replace[] = preg_match('/^{/', $var) ? $this->app->zlfw->shortCode($var) : JText::_($var);
		}

		return preg_replace($pattern, $replace, $string);
	}

	/*
		Function: loadAssets - Load the necesary assets
	*/
	protected $loadedAssets = false;
	public function loadAssets()
	{
		if (!$this->loadedAssets) {
			// init vars
			// $url = $this->app->link(array('controller' => 'zlframework', 'format' => 'raw', 'type' => $this->type), false);
			// $enviroment_args = json_encode($this->enviroment_args);

			// load zlfield assets
			// $this->app->document->addStylesheet('zlfield:zlfield.css');
			// $this->app->document->addStylesheet('zlfield:layouts/field/style.css');
			// $this->app->document->addStylesheet('zlfield:layouts/separator/style.css');
			// $this->app->document->addStylesheet('zlfield:layouts/wrapper/style.css');
			// $this->app->document->addScript('zlfield:zlfield.min.js');

			// load libraries
			$this->app->zlfw->zlux->loadMainAssets();

			// init scripts
			// $javascript = "jQuery(function($){ $('body').ZLfield({ url: '{$url}', type: '{$this->type}', enviroment: '{$this->enviroment}', enviroment_args: '{$enviroment_args}' }); });";
			// $this->app->document->addScriptDeclaration($javascript);

			// don't load them twice
			$this->loadedAssets = true;
		}
	}

	/*
		Function: getLayout
			Get element layout path and use override if exists.

		Returns:
			String - Layout path
	*/
	public function getLayout($layout = null)
	{
		// find layout
		return $this->app->path->path("zlfield:layouts/{$layout}");
	}

	/*
		Function: field - Returns field html string
	*/
	public function field($params, $value, $getCurrentValue=false)
	{
		$type	= $params->get('type');

		if ($type && $params->get('render') && $this->renderIf($params->get('renderif')))
		{
			$id 		= $params->get('id');
			$name 		= $params->get('final_ctrl').'['.$id.']';
			$specific 	= $this->app->data->create((array)$params->get('specific'));
			$attrs		= '';

			// render field
			$field = $this->app->zlfieldhtml->_('zlf.'.$type.'Field', $id, $name, $value, $specific, $attrs, $getCurrentValue);

			if (!empty($field)) return $field;
		}

		return null;
	}

	/*
		Function: row - Returns row html string
	*/
	public function renderField($type, $id, $name, $value, $args = array())
	{
		if (empty($type)) return;

		// set vars
		// $args['id']		= $id;
		// $args['name']	= $name;
		// $args['value']	= $value;
		$field = $this->app->data->create($args);

		$__file = $this->app->path->path("zlux.fields:fields/$type.php");

		if ($__file != false) {
			// render the field
			// extract($args);
			ob_start();
			include($__file);
			$output = ob_get_contents();
			ob_end_clean();
			return $output;
		}

		return 'Field Layout "'.$type.'" not found. ('.$this->app->utility->debugInfo(debug_backtrace()).')';
	}
}