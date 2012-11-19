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
   Class: ZlfieldHelper
   	  ZL Field class for all params fields around Joomla!
*/
class ZlfieldHelper extends AppHelper {

	protected $layout;
	protected $path;
	protected $params;
	protected $config;

	public function __construct($default = array())
	{
		parent::__construct($default);

		// get joomla and application table
		$this->joomla   = $this->app->system->application;
		$this->appTable = $this->app->table->application;
		
		// set data shortcut
		$this->data = $this->app->data;

		// set request shortcut
		$this->req = $this->app->request;

		// get task
		$this->task = $this->req->getVar('parent_task') ? $this->req->getVar('parent_task') : $this->req->getVar('task');

		// get application group
		$this->group = $this->req->getString('group');

		// get type
		$cid  = $this->req->get('cid.0', 'string', ''); // edit view
		$type = $cid ? $cid : $this->req->getString('type'); // assign view
		if(!empty($type)){
			$this->type = $type;
			$this->joomla->setUserState('plg_zlfw_zlfieldtype', $type);
		} else {
			// get type from user session
			$this->type = $this->joomla->getUserState('plg_zlfw_zlfieldtype', '');
		}

		// create application object
		$this->application = $this->app->object->create('Application');
		$this->application->setGroup($this->group);

		// get url params
		$this->controller = $this->req->getString('controller');
		$this->option = $this->req->getString('option');
		$this->view   = $this->req->getString('view');

		// set the params mode - edit, config, positions, module, plugin
		$this->mode = $this->req->getString('zlfieldmode');
		if(empty($this->mode)){
			if($this->task == 'assignelements' || $this->task == 'assignsubmission')
				$this->mode = 'positions';
			else if($this->task == 'editelements' || $this->task == 'addelement')
				$this->mode = 'config';
			else if($this->task == 'edit')
				$this->mode = 'edit';
			else if(($this->option == 'com_modules' || $this->option == 'com_advancedmodules') && $this->view == 'module')
				$this->mode = 'module';
			else if($this->controller == 'configuration')
				$this->mode = 'appconfig'; // App Config
		}
		
		// get params
		if($this->mode == 'edit')
			$this->initEditMode();
		else if($this->mode == 'positions')
			$this->initPositionsMode();
		else if($this->mode == 'config')
			$this->initConfigMode();
		else if($this->mode == 'module')
			$this->initModuleMode();
		else if($this->mode == 'appconfig')
			$this->initAppConfigMode();
		else {
			$this->params = $this->data->create(array());
		}

		// set cache var
		$this->cache = $this->data->create(array());
		
		// dump($this->params, 'params');
		// dump($this->config, 'config');

		// load assets
		$this->loadAssets();
	}

	public function _($type)
	{
		// get arguments
		$args = func_get_args();

		// Check to see if we need to load a helper file
		$parts = explode('.', $type);

		if (count($parts) >= 2) {
			$func = array_pop($parts);
			$file = array_pop($parts);

			if (in_array($file, array('zoo', 'control')) && method_exists($this, $func)) {
				array_shift($args);
				return call_user_func_array(array($this, $func), $args);
			}
		}

		return call_user_func_array(array('JHTML', '_'), $args);
	}

	protected function initEditMode()
	{
		// get application
		$this->application = $this->app->zoo->getApplication();

		// get item
		$item_id = $this->req->get('cid.0', 'int');
		$item = $item_id ? $this->app->table->item->get($item_id) : null;
		$data = $item ? $item->elements : array();

		// get params
		$this->params = $this->data->create($data);

		// init config
		$this->initConfigMode();
	}
	
	protected function initConfigMode()
	{
		$this->config = array();
		if(!empty($this->type) && $type = $this->application->getType($this->type))
		{
			// get params from type.config file
			$config = json_decode(file_get_contents($type->getConfigFile()), true);
			$this->config = isset($config['elements']) ? $config['elements'] : $this->config;
		} 
		
		$this->config = $this->data->create($this->config);

		// use as params in config mode
		if($this->mode == 'config') $this->params = $this->config;
	}

	protected function initPositionsMode()
	{
		// init config
		$this->initConfigMode();

		// get layout
		$this->layout = $this->req->getString('layout');

		// get path
		$this->path = $this->task == 'assignelements' ? JPATH_ROOT.'/'.urldecode($this->req->getVar('path')) : '';
		$this->path = $this->task == 'assignsubmission' ? $this->application->getPath().'/templates/'.$this->req->getString('template') : $this->path;

		// get params from position.config file
		$renderer = $this->app->renderer->create('item')->addPath($this->path);
		$this->params = $this->data->create($renderer->getConfig('item')->get($this->group.'.'.$this->application->getType($this->type)->id.'.'.$this->layout));

		// submissions workaround
		if($this->task == 'assignsubmission')
		{
			/* rearrange and give the arrays a name in order to work well with getParams() */
			$data = array();
			foreach($this->params as $position) foreach($position as $element){
				$data[$element['element']] = $element;
			}
			$this->params = $this->data->create($data);
		}
	}

	protected function initModuleMode()
	{
		// get module params
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('m.params');
		$query->from('#__modules AS m');
		$query->where('m.id = '.$this->req->getVar('id'));

		$db->setQuery($query);
		$result = $db->loadResult();

		// create the necesay array path
		$this->params = $this->data->create( array('jform' => array('params' => json_decode($result, true))) );
	}

	protected function initAppConfigMode()
	{
		// get application
		$this->application = $this->app->zoo->getApplication();

		// set params
		$this->params = $this->application->getParams();
	}

	/*
		Function: render - Returns the result from _parseJSON wrapped with main html dom

		Params
			$thl togglehiddenlabel
	*/
	public function render($parseJSONargs, $toggle=false, $thl='', $ajaxargs=array(), $class='', $ajaxLoading=false)
	{
		// init vars
		$html = array();
		$ajaxargs = !empty($ajaxargs) ? json_encode($ajaxargs) : false;
		$class = $class ? ' '.$class : '';

		$parsedFields = $parseJSONargs ? call_user_func_array(array($this, "parseJSON"), $parseJSONargs) : '';

		$html[] = '<div class="zlfield zlfield-main placeholder'.$class.'"'.($ajaxargs ? " data-ajaxargs='{$ajaxargs}'" : '').'>';

		if($ajaxLoading)
		{
			$html[] = '<div class="load-field-btn">';
				$html[] = '<span>'.strtolower(JText::sprintf('PLG_ZLFRAMEWORK_EDIT_THIS_PARAMS', $thl)).'</span>';
			$html[] = '</div>';
		}
		else if(!$toggle)
		{
			$html[] = $parsedFields;
		} 
		else 
		{
			$hidden = $toggle == 'starthidden' ? true : false;
			$html[] = '<div class="zltoggle-btn '.($hidden ? '' : 'open').'">';
				$html[] = '<span class="tg-close">- '.JText::_('PLG_ZLFRAMEWORK_TOGGLE').'</span>';
				$html[] = '<div class="tg-open"><span>'.strtolower(JText::sprintf('PLG_ZLFRAMEWORK_EDIT_THIS_PARAMS', $thl)).'</span></div>';
			$html[] = '</div>';
			$html[] = '<div class="zltoggle"'.($hidden ? ' style=" display: none;"' : '').'>'.$parsedFields.'</div>';
		}

		$html[] = '</div>';

		return implode("\n", $html);
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

		// load element - $element is extracted from $arguments
		if($this->mode == 'positions' || $this->mode == 'config' && $this->req->getVar('ajaxcall')){
			if(!isset($element) || !is_object($element)){
				$element = $this->app->element->create($element_type, $this->application);
				$element->identifier = $element_id;
				$element->config = $this->app->data->create($this->config->get($element_id));
			}
		}

		// update config if adding new element as it's values can be dynamic
		if($this->mode == 'config' && $this->task == 'addelement'){
			$this->config->set($element->identifier, (array)$element->config);
		}

		// save element object to reuse
		if(isset($element) && is_object($element)){
			$this->element = $element;
		}

		/* update params if provided */
		if(isset($addparams)){
			$this->params = $this->data->create( $addparams );
		}

		// convert to array
		settype($json, 'array');
		
		// if paths provided retrieve json and convert to array
		if (isset($json['paths'])){
			foreach (array_map('trim', explode(',', $json['paths'])) as $pt) if ($path = $this->app->path->path($pt)) // php files only
			{
				if(is_file($path)){
					/* IMPORTANT - this vars are necesary for include function */
					$subloaded = true; // important to let know it's subloaded
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
			$fld = $this->data->create($fld);

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
					$result[] = $this->$fld['type']($fld->get('text'), $id, $fld->get('big'));
					break;
				case 'wrapper':
				case 'control_wrapper':
				case 'toggle':
				case 'fieldset':
					// result
					$res = array_filter($this->parseJSON(json_encode(array('fields' => $fld->get('fields'))), $final_ctrl, $psv, $pid, true, $arguments));
					
					// abort if no minimum fields reached
					if (count($res) == 0 || count($res) < $fld->get('min_count', 0)) continue;

					if($field_type == 'toggle')
					{
						$toggle = JText::_($fld->get('toggle'));
						$result[] = '<div class="zltoggle-btn open"><span>-</span>'.$toggle.'<div class="toggle-text">...<small>'.strtolower($toggle).'</small>...</div></div>';
						$result[] = '<div class="wrapper zltoggle" data-id="'.$id.'" >';
							$result = array_merge($result, $res);
						$result[] = '</div>';
					}
					elseif($field_type == 'fieldset')
					{
						$result[] = '<fieldset class="wrapper">';
							$result = array_merge($result, $res);
						$result[] = '</fieldset>';
					}
					elseif($field_type == 'wrapper')
					{
						$result[] = '<div class="wrapper" data-id="'.$id.'" >';
							$result = array_merge($result, $res);
						$result[] = '</div>';
					}
					else
					{
						$result = array_merge($result, $res);
					}
					
					break;
				case 'subfield':
					// get parent fields data
					$psv = $this->data->create($psv);

					// replace path {value} if it's string
					$paths = is_string($psv->get($pid)) ? str_replace('{value}', basename($psv->get($pid), '.php'), $fld->get('path')) : $fld->get('path');

					// replace parent values in paths
					foreach ((array)$psv as $key => $pvalue) {
						$paths = str_replace('{'.$key.'}', basename((string)$pvalue, '.php'), $paths);
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
						$value = $this->_getParam($fld->find('check_old_value.id'), null, $old_value_ctrl);
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

					// get value
					$value = $value ? $value : $this->_getParam($id, $fld->get('default'), $final_ctrl, $fld->get('old_id', false));

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
					
					$specific = $fld->get('specific', array()); /**/ if ($psv) $specific['parents_val'] = $psv;
					
					// state
					$state = null;
					if($state = $fld->get('state', array())){
						$state['init_state'] = $this->_getParam($id.'_state', $state['init_state'], $final_ctrl);
						$state['field'] = $this->checkbox($id, "{$final_ctrl}[{$id}_state]", $state['init_state'], $this->app->data->create(array()), array());
					}
					
					// prepare help
					if($help = $fld->get('help'))
					{
						$help = explode('||', $help);
						$text = JText::_($help[0]);
						unset($help[0]);

						$help = count($help) ? $this->replaceVars($help[1], $text) : $text;
						//$help = $fld->get('default') ? $help.='<div class="default-value">'.strtolower(JText::_('PLG_ZLFRAMEWORK_DEFAULT')).': '.$fld->get('default').'</div>' : $help;
					}

					// render individual field row
					if($res = $this->row($field_type, $id, "{$final_ctrl}[{$id}]", $value, $specific, $state, $fld->get('label'), $fld->get('class'), $help, $fld->get('dependents'), $fld->get('renderif'), $fld->get('render', 1), $fld->get('layout', 'default'))) $result[] = $res;
					
					// load childs
					if($childs = $fld->find('childs.loadfields'))
					{
						// create parent values
						$pid = $id;
						$psv[$id] = $value; // add current value to parents array

						$p_task = $this->req->getVar('parent_task') ? $this->req->getVar('parent_task') : $this->req->getVar('task'); // parent task necesary if double field load ex: layout / sublayout
						$url = $this->app->link(array('controller' => 'zlframework', 'format' => 'raw', 'type' => $this->type, 'layout' => $this->layout, 'group' => $this->group, 'path' => $this->req->getVar('path'), 'parent_task' => $p_task, 'zlfieldmode' => $this->mode), false);
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
		Function: _getParam - retrieves the field stored value from the $params
		$params, $fieldID, $fieldControl, $defaultValue
	*/
	private function _getParam($id, $default, $ctrl, $old_id=false)
	{
		$path = preg_replace( // create path to the params from control
		array('/(^positions\[|^elements\[|^addons\[|\]$)/', '/(\]\[|\[|\])/'),
		array('', '.'),
		$ctrl);

		// dump($path, $id);
		$value = null;
		if ($this->controller == 'configuration') // if App Config Params
		{
			$path = "global.$path";
			$param = $this->params->get($path);

			if(is_array($param) && isset($param[$id])){
				$value = $param[$id];
			} else {
				$value = $param;
			}
		}
		else if(is_array($id))
		{
			$params = array();
			foreach ((array) $id as $key => $id) {
				$params[$key] = $this->params->find("$path.$id", $default);
			} $value = $params;
		}
		else // default
		{
			// if FIND miss value use GET, if NO apply default
			$value = $this->params->find("$path.$id");
			if(empty($value) && $old_id){
				$value = $this->params->find("$path.$old_id"); // try with old id
			}
			$value = !isset($value) && isset($default) ? $default : $value; // use default if any
		}
		return $value;
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

	// convert an xml ready for parseArray()
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
		$vars = explode(',', trim($vars, ' '));
		
		$pattern = $replace = array(); $i=1;
		foreach((array)$vars as $var){
			$pattern[] = "/%s$i/"; $i++;
			$replace[] = preg_match('/{ZL_/', $var) ? $this->app->zlfw->shortCode($var) : JText::_($var);
		}

		return preg_replace($pattern, $replace, $string);
	}

	/**
	 * getTheEnviroment
	 *
	 * @return @string item-edit, type-config, type-position
	 *
	 * @since 3.0
	 */
	public function getTheEnviroment()
	{
		switch ($this->req->getVar('task')) {
			case 'editelements':
				return 'type-edit';
				break;

			case 'assignelements':
			case 'assignsubmission':
				return 'type-position';
				break;

			case 'edit':
				return 'item-edit';
				break;
		}
	}

	/*
		Function: loadAssets - Load the necesary assets
	*/
	protected function loadAssets()
	{
		// init vars
		$url = $this->app->link(array('controller' => 'zlframework', 'format' => 'raw', 'type' => $this->type), false);

		// load zlfield assets
		$this->app->document->addStylesheet('zlfield:zlfield.css');
		$this->app->document->addScript('zlfield:zlfield.min.js');

		// load libraries
		$this->app->zlfw->loadLibrary('qtip');
		$this->app->zlfw->loadLibrary('zlux');

		// init scripts
		$javascript = "jQuery(function($){ $('body').ZLfield({ url: '{$url}', type: '{$this->type}' }) });";
		$this->app->document->addScriptDeclaration($javascript);
	}

	/*
		Function: _rowLayout
			Renders the row layout using template layout file

	   Parameters:
            $__layout - layouts template file
	        $__args - layouts template file args

		Returns:
			String - html
	*/
	private function _rowLayout($__layout, $__args = array())
	{
		// init vars
		if (is_array($__args)) {
			foreach ($__args as $__var => $__value) {
				$$__var = $__value;
			}
		}

		// render layout
		$__html = '';
		ob_start();

		// include($__layout); // when using external files
		switch ($__layout) {
			case 'separator':
				$label = $label ? '<span class="zl-label">'.JText::_($label).'</span>' : '';
				$class = 'class="row layout-separator'.($class ? " {$class}" : '').(!$init_state ? ' zl-disabled' : '').'"';

				// attributes
				$attrs = '';
				$attrs .= $type ? " data-type='{$type}'" : '';
				$attrs .= $dependents ? " data-dependents='{$dependents}'" : '';

				echo '<div data-id="'.$id.'" '.$class.$attrs.'>'.$label.'<span class="zl-field">'.$field.'</span>'.$help.'</div>';
				break;
			
			default:
				$help = $help ? '<span class="zl-help qTipHelp">?<span class="qtip-content">'.$help.'</span></span>' : '';
				$label = $label ? '<div class="zl-label"><span class="active">»</span>'.JText::_($label).'</div>' : '';
				$class = 'class="row layout-default'.($class ? " {$class}" : '').(!$init_state ? ' zl-disabled' : '').'"';

				// attributes
				$attrs = '';
				$attrs .= $type ? " data-type='{$type}'" : '';
				$attrs .= $dependents ? " data-dependents='{$dependents}'" : '';

				// state
				$tooltip = @$state['label'] ? ' tooltip="'.JText::_($state['label']).'"' : '';
				echo '<div data-id="'.$id.'" '.$class.$attrs.'>'
					.($state != null ? '<div '.$tooltip.'class="zl-state">'.$state['field'].'</div>' : '')
					.$label.'<div class="zl-field">'.$field.'</div>'.$help.'</div>';
				break;
		}

		$__html = ob_get_contents();
		ob_end_clean();

		return $__html;
	}


	/* HTML Fields
	--------------------------------------------------------------------------------------------------------------------------------------------*/

	/*
		Function: row - Returns row html string
	*/
	public function row($type, $id, $name, $value, $specific=array(), $state=null, $label=false, $class='', $help='', $dependents='', $renderif=array(), $render=1, $layout=false)
	{
		if ($type && $render && $this->renderIf($renderif))
		{
			$init_state = true; /**/ $attribs = '';
			if($state != null) {
				$init_state = @$state['init_state'] == '0' ? false : true;
				$attribs 	= !$init_state ? ' disabled="disabled"' : '';
			}
			$specific = $this->data->create($specific);

			if ($field = $this->$type($id, $name, $value, $specific, $attribs))
			{
				return $this->_rowLayout($layout, compact('type', 'field', 'id', 'state', 'label', 'class', 'help', 'dependents', 'init_state', 'name'));
			}
		}
		return null;
	}

	

	/*
		Function: separator - Returns html string
	*/
	public function separator($text, $id, $big=false){
		return '<div class="row '.($big ? 'big' : '').'section-title" data-type="separator" data-id="'.$id.'" >'.JText::_($text).'</div>';
	}
	
	/*
		Function: info - Returns html string
	*/
	public function info($id, $name, $value, $spec, $attrs)
	{
		return '<div class="info">'.$this->replaceVars($spec->get('var'), JText::_($spec->get('text'))).'</div>';
	}
	
	/*
		Function: text - Returns text input html string
	*/
	public function text($id, $name, $value, $spec, $attrs){
		$attrs .= $spec->get('placeholder') ? ' placeholder="'.JText::_($spec->get('placeholder')).'"' : '';
		return $this->app->html->_('control.text', $name, (string)$value, 'size="60" maxlength="255"'.$attrs);
	}

	/*
		Function: textarea - Returns textarea input html string
	*/
	public function textarea($id, $name, $value, $spec, $attrs){
		return '<textarea '.$attrs.' name="'.$name.'" >'.$value.'</textarea>';
	}
	
	/*
		Function: hidden - Returns hidden input html string
	*/
	public function hidden($id, $name, $value, $spec, $attrs){
		return '<input type="hidden" name="'.$name.'" value="'.$spec->get('value').'" />';
	}
	
	/*
		Function: password - Returns password input html string
	*/
	public function password($id, $name, $value, $spec, $attrs){
		return '<input type="password" '.$attrs.' name="'.$name.'" value="'.$value.'">';
	}
	
	/*
		Function: checkbox - Returns checkbox input html string
	*/
	public function checkbox($id, $name, $value, $spec, $attrs){
		$extra_label = $spec->get('label');
		$input_value = $spec->get('value', 1);
		return '<input type="checkbox" '.$attrs.' name="'.$name.'" '.($value ? 'checked="checked"' : '').' value="'.$input_value.'" />'.($extra_label ? '<span>'.JText::_($extra_label).'</span>' : '');
	}
	
	/*
		Function: radio - Returns radio select html string
	*/
	public function radio($id, $name, $value, $spec, $attrs){
		$preoptions = $spec->get('options') ? $spec->get('options') : array('JYES' => '1', 'JNO' => '0');
		$options = array(); foreach ($preoptions as $text => $val) $options[] = $this->app->html->_('select.option', $val, $text);
		return $this->app->html->_('select.radiolist', $options, $name, $attrs, 'value', 'text', $value, $name, true);
	}
	
	/*
		Function: select - Returns select html string
	*/
	protected $_select_options = array();
	public function select($id, $name, $value, $spec, $attrs)
	{
		$name   = $spec->get('multi') ? $name.'[]' : $name;
		$attrs .= $spec->get('multi') ? ' multiple="multiple" size="'.$spec->get('size', 3).'"' : '';

		$hash = md5(serialize( $spec ));
		if (!array_key_exists($hash, $this->_select_options))
		{
			$hidden_opts = $spec->get('hidden_opts', 0) ? explode('|', $spec->get('hidden_opts', '')) : '';
			
			// options file
			$opt_file = str_replace('{value}', (string)$value, $spec->get('opt_file'));

			$preoptions = array_merge($spec->get('options', array()), $spec->get('fix_options', array()));
			if (!empty($opt_file) && $path = $this->app->path->path($opt_file))
			{	// get options from json file
				$preoptions = array_merge($preoptions, json_decode(file_get_contents($path), true));
			}
			
			$options = array(); // prepare options
			foreach ($preoptions as $text => $val) {
				if (empty($hidden_opts) || !in_array($val, $hidden_opts)) {
					$options[] = $this->app->html->_('select.option', $val, JText::_($text));
				}
			}

			$this->_select_options[$hash] = $options;
		}
		
		// render if enaugh options
		$options = @$this->_select_options[$hash];

		
		if ($spec->get('min_opts') && count($options) < $spec->get('min_opts', 0)) {
			return;
		} elseif (!empty($options)){
			return $this->app->html->_('select.genericlist', $options, $name, $attrs, 'value', 'text', $value, $name)
				  .($spec->get('multi') && count($options) > 3 ? '<span class="zl-btn-small zl-btn-expand zl-select-expand" data-zl-qtip="'.JText::_('PLG_ZLFRAMEWORK_EXPAND').'"></span>' : '');
		} else {
			// render message instead
			$spec->set('text', JText::_('PLG_ZLFRAMEWORK_ZLFD_NO_OPTIONS'));
			return $this->info($id, $name, $value, $spec, $attrs);
		}
	}
	
	/*
		Function: layout - Returns select html string
			It list the files or folder of specified path as options
	*/
	public function layout($id, $name, $value, $spec, $attrs)
	{
		// if no path supplied abort
		if(!$spec->get('path')) return JText::_('PLG_ZLFRAMEWORK_ZLFD_NO_OPTIONS');

		$psv	 = $spec->get('parents_val');
		$mode	 = $spec->get('mode', 'files'); // OR folders
		$regex	 = $spec->get('regex', '^([_A-Za-z0-9]*)');
		$layouts = (array)$spec->get('options', array());

		// dynamic values {}
		$path = str_replace('{value}', (string)$value, $spec->get('path'));

		// replace parent values in path
		foreach ((array)$psv as $key => $pvalue) {
			$path = str_replace('{'.$key.'}', basename($pvalue, '.php'), $path);
		}

		// get all resources
		$resources = array();
		$paths = array_map('trim', explode(',',$path)); // multiple paths allowed with comma separator
		foreach($paths as $path) {
			if(preg_match('/(.*){subfolders}(.*)/', $path, $result)) { // process subfolders
				$path = trim(@$result[1], '/');
				$postpath = trim(@$result[2], '/');
				foreach ($this->app->path->dirs($path) as $dir) {
					$resources = array_merge($resources, $this->app->zlpath->resources("$path/$dir/$postpath"));
				}
			} else {
				$resources = array_merge($resources, $this->app->zlpath->resources($path));
			}
		}

		// get layout options from resources
		foreach($resources as $resource) {
			if(is_dir($resource)) foreach(JFolder::$mode($resource, $regex) as $tmpl) {
				$basename = basename($tmpl, '.php');
				$layouts[ucwords($basename)] = $tmpl;
			}
		}
		
		// sort letting default.php the first
		uasort($layouts, array($this, 'cmp'));
		
		$spec->set('options', $layouts);
		return $this->select($id, $name, $value, $spec, $attrs);
	}
	
	public function cmp($a, $b){
		// sets the default.php allways first
		if(stripos('default.php', $a) === 0) return -1;
		if(stripos('default.php', $b) === 0) return 1;
		return ($a < $b) ? -1 : 1;
	}
	
	/*
		Function: apps - Returns zoo apps html string
	*/
	public function apps($id, $name, $value, $spec, $attrs)
	{
		// init vars
		$group   = $spec->get('group', ''); // filter apps
		$apps    = (array)$spec->get('options', array());

		foreach ($this->appTable->all(array('order' => 'name')) as $app) {
			if (empty($group) || $app->getGroup() == $group){
				$apps[$app->name] = $app->id;
			}
		}

		// set options for select
		$spec->set('options', $apps);
		
		return $this->select($id, $name, $value, $spec, $attrs);
	}
	
	/*
		Function: types - Returns zoo types html string
	*/
	public function types($id, $name, $value, $spec, $attrs)
	{
		// init vars
		$pv	 	 = $this->data->create( $this->trslValues($spec->get('parents_val'), $spec->get('value_map')) );
		$group   = $spec->get('group', ''); // filter Types with app groups
		$apps    = (array)$spec->get('apps', $pv->get('apps', array())); // get static or parent app value
		$ft	     = (array)$spec->get('types', array()); // filterTypes
		
		$apps = $this->app->zlfw->getApplications($apps, true, $group); // if empty will return All apps
 		
		// prepare types avoiding duplicates
		$types = array();
		foreach ($apps as $app){
			$types = array_merge($types, $app->getTypes());
		}
		
		// create options
		$options = array();
		foreach ($types as $type) {
			if (empty($ft) || in_array($type->id, $ft)){ // direct filter
				$options[$type->name] = $type->id;
			}
		}

		// set options for select
		$spec->set('options', $options);
		
		return $this->select($id, $name, $value, $spec, $attrs);
	}
	
	/*
		Function: elements - Returns zoo elements html string
	*/
	public function elements($id, $name, $value, $spec, $attrs)
	{
		// init vars
		$pv	 	 = $this->data->create( $this->trslValues($spec->get('parents_val'), $spec->get('value_map')) );
		$group   = $spec->get('group', ''); // filter Types with app groups
		$apps    = (array)$spec->get('apps', $pv->get('apps', array())); // get static or relied app value
		$ft	     = (array)$spec->get('types', $pv->get('types', array())); // filterTypes
		
		$constraints = explode(',', $spec->get('constraint', ''));
		$elements = array();
		foreach($constraints as $const){
			$elements = array_merge($elements, $this->elementsList($apps, $const, $ft));
		}
		
		if(empty($elements)){
			// set text
			$spec->set('text', JText::_('PLG_ZLFRAMEWORK_APP_NO_ELEMENTS'));
			return $this->info($id, $name, $value, $spec, $attrs);
		} else {
			// merge predefined options
			$options = array_merge($spec->get('options', array()), $elements); 

			// set options for select
			$spec->set('options', $options);
			
			return $this->select($id, $name, $value, $spec, $attrs);
		}
	}
	
	/*
		Function: cats - Returns zoo cats html string
	*/
	public function cats($id, $name, $value, $spec, $attrs)
	{
		// init vars
		$pv	  = $this->data->create( $this->trslValues($spec->get('parents_val'), $spec->get('value_map')) );
		$apps = (array)$spec->get('apps', $pv->get('apps', array())); // get static or relied app value
		$apps = $this->app->zlfw->getApplications($apps);
		
		$categories = array();
 		if (!empty($apps)) foreach($apps as $app)
		{
			// get category tree list
			$list = $this->app->tree->buildList(0, $app->getCategoryTree(), array(), '- ', '.   ', '  ');

			// create options
			$categories['-- -- -- '.$app->name.' ROOT -- -- --'] = 0;
			foreach ($list as $category) {
				$categories[$category->treename] = $category->id;
			}
		}

		// set options for select
		$spec->set('options', $categories);
		
		return $this->select($id, $name, $value, $spec, $attrs);
	}
	
	/*
		Function: trslValues - Returns parent values array
			Used to translate the value_map to real values
	*/
	protected function trslValues($values, $map)
	{
		$pvs = array();
		if($map && is_array($map)){
			foreach ($map as $key => $parent) if (isset($values[$parent]) && $values[$parent] != 'null'){ // important
				$pvs[$key] = $values[$parent];
			}
		}
		return $pvs;
	}
	
	/*
		Function: apps - Returns zoo apps html string
	*/
	public function modulelist($id, $name, $value, $specific=array(), $attribs='') {
		return $this->app->html->_('zoo.modulelist', array(), $name, null, 'value', 'text', $value);
	}
	
	/*
		Function: separatedBy - Returns separated options for repeatable elements
	*/
	public function separatedby($id, $name, $value, $spec, $attrs)
	{
		// init vars
		$constraint = $spec->get('constraint', ''); // filter layouts by metadata
		$options    = (array)$spec->get('options', array());
		
		$options['None'] 							= '';
		$options['Space'] 							= 'separator=[ ]';
		$options['Span'] 							= 'tag=[<span>%s</span>]';
		$options['Paragraph']						= 'tag=[<p>%s</p>]';
		$options['Div'] 							= 'tag=[<div>%s</div>]';

		$options['Comma'] 							= 'separator=[, ]';
		$options['Hyphen'] 							= 'separator=[ - ]';
		$options['Pipe'] 							= 'separator=[ | ]';
		$options['Break'] 							= 'separator=[<br />]';
		$options['List Item'] 						= 'tag=[<li>%s</li>]';
		$options['Unordered List'] 					= 'tag=[<li>%s</li>] enclosing_tag=[<ul>%s</ul>]';
		$options['Ordered List'] 					= 'tag=[<li><div>%s</div></li>] enclosing_tag=[<ol>%s</ol>]';
		$options['PLG_ZLFRAMEWORK_CUSTOM'] 			= 'custom';

		// set options for select
		$spec->set('options', $options);
		
		return $this->select($id, $name, $value, $spec, $attrs);
	}


	/* HTML Fields Helpers
	--------------------------------------------------------------------------------------------------------------------------------------------*/
	
	/*
		Function: itemLayoutList - Returns related layouts list
	*/
	public function itemLayoutList($id, $name, $value, $spec, $attrs)
	{
		// init vars
		$constraint = $spec->get('constraint', ''); // filter layouts by metadata
		$options    = (array)$spec->get('options', array());
		$typeFilter = $spec->get('typefilter') ? explode(',', 'event') : null;
		
		// pass trough all apps
		$layouts = array();	
		foreach($this->appTable->all(array('order' => 'name')) as $application) if ($template = $application->getTemplate())
		{	
			$layout_path = str_replace("\\", "/", $template->getPath());
			
			// get renderer
			$renderer = $this->app->renderer->create('item')->addPath($layout_path);
			
			// get all types
			$folders = array();			
			foreach (JFolder::folders($layout_path.'/'.$renderer->getFolder().'/item') as $folder) {
				$folders[] = $folder;
			}
			
			// Check for root folder, in case app doesn't have type related layouts
			$layouts = array_merge($layouts, $this->_getLayouts(null, $constraint, $renderer));
			
			// Now in subfolders
			foreach ($folders as $type){
				if (empty($typeFilter) || in_array($type, $typeFilter)) {
					$layouts = array_merge($layouts, $this->_getLayouts($type, $constraint, $renderer));
				}
			}
		}
		
		// create layout options
		foreach ($layouts as $layout) $options[$layout['name']] = $layout['layout'];

		// set options for select
		$spec->set('options', $options);

		return $this->select($id, $name, $value, $spec, $attrs);
	}
	
	protected function _getLayouts($type = null, $constraint = null, $renderer = null)
	{
		$path   = 'item';
		$prefix = 'item.';
		if (!empty($type) && $renderer->pathExists($path.DIRECTORY_SEPARATOR.$type)) {
			$path   .= DIRECTORY_SEPARATOR.$type;
			$prefix .= $type.'.';
		}
		
		$layouts = array();
		foreach ($renderer->getLayouts($path) as $layout) {
	
			$metadata = $renderer->getLayoutMetaData($prefix.$layout);
			
			if (empty($constraint) || $metadata->get('type') == $constraint) {
				$name = $metadata->get('name') ? $metadata->get('name') : ucfirst($layout);
				$layouts[$layout] = array('name' => $name, 'layout' => $layout);
			}
		}
		return $layouts;
	}
	
	
	/*
		Function: elementsList - Returns element list
	*/
	/*
		Function: elementsList - Returns element list
	*/
	protected $_elements_list = array();
	public function elementsList($apps = null, $constraint = null, $filter_types = null)
	{
		$hash = md5(serialize( array($apps, $constraint, $filter_types) ));
		if (!array_key_exists($hash, $this->_elements_list))
		{
			$apps = $this->app->zlfw->getApplications($apps, true); // if empty will return All apps
			
			// prepare types avoiding duplicates
			$types = array();
			foreach ($apps as $app){
				$types = array_merge($types, $app->getTypes());
			}
			
			// filter types
			if (count($filter_types) && !empty($filter_types[0])){
				$filtered_types = array();
				foreach ($types as $type){
					if (in_array($type->id, $filter_types)){
						$filtered_types[] = $type;
					}
				}
				$types = $filtered_types;
			}
			
			// get all elements
			$elements = array();
			foreach($types as $type){
				$elements = array_merge( $elements, $type->getElements() );
			}
			
			// create options
			$options = array();			
			foreach ($elements as $element) {
				// include only desired element
				if (empty($constraint) || $element->getElementType() == $constraint) {
					$options[$element->getType()->name.' > '.$element->config->get('name')] = $element->identifier;
				}
			}

			$this->_elements_list[$hash] = $options;
		}

		// return elements array
		return @$this->_elements_list[$hash];
	}
	
}