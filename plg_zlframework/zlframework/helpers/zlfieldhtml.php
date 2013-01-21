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
   Class: HTMLHelper
	  A class that contains zlfield html functions
*/
class ZLFieldHTMLHelper extends AppHelper {

	public function _($type) {

		// get arguments
		$args = func_get_args();

		// Check to see if we need to load a helper file
		$parts = explode('.', $type);

		if (count($parts) >= 2) {
			$func = array_pop($parts);
			$file = array_pop($parts);

			if (in_array($file, array('zlf', 'control')) && method_exists($this, $func)) {
				array_shift($args);
				return call_user_func_array(array($this, $func), $args);
			}
		}

		return call_user_func_array(array('JHTML', '_'), $args);

	}

	public function cmp($a, $b){
		// sets the default.php allways first
		if(stripos('default.php', $a) === 0) return -1;
		if(stripos('default.php', $b) === 0) return 1;
		return ($a < $b) ? -1 : 1;
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

	/**
	 * Retrun an HTML separator
	 *
	 * @param   array	$data	An array of objects
	 *
	 * @return  string HTML for the select list
	 *
	 * @since  3.0.8
	 */
	public function separator($text, $id, $big=false){
		return '<div class="row '.($big ? 'big' : '').'section-title" data-type="separator" data-id="'.$id.'" >'.JText::_($text).'</div>';
	}
	
	/**
	 * Return an HTML string
	 *
	 * @param   string    $id      Field ID
	 * @param   string    $name    Field form name
	 * @param   mixed     $value   Field value
	 * @param   object    $spec    Field specific params
	 * @param   string    $attrs   Attributes
	 *
	 * @return  string HTML
	 *
	 * @since  3.0.8
	 */
	public function info($id, $name, $value, $spec, $attrs){
		return '<div class="info">'.$this->app->zlfield->replaceVars($spec->get('var'), JText::_($spec->get('text'))).'</div>';
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
		$value = $this->app->zlfw->decryptPassword($value);
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

	/*
		Function: apps - Returns zoo apps html string
	*/
	public function apps($id, $name, $value, $spec, $attrs)
	{
		// init vars
		$group   = $spec->get('group', ''); // filter apps
		$apps    = (array)$spec->get('options', array());

		foreach ($this->app->table->application->all(array('order' => 'name')) as $app) {
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
		$pv	 	 = $this->app->data->create( $this->trslValues($spec->get('parents_val'), $spec->get('value_map')) );
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
		$pv = $this->app->data->create( $this->trslValues($spec->get('parents_val'), $spec->get('value_map')) );

		// apps
		$apps = (array)$pv->get('apps'); // from parent value
		$apps = array_merge($apps, explode(' ', $spec->get('apps', '')));
		// convert apps id to group
		foreach ($apps as &$app) if(is_numeric($app)) {
			$app = $this->app->table->application->get($app);
			$app = (is_object($app)) ? $app->getGroup() : null;
		}
		// clean duplicates
		$apps = array_unique($apps);

		// types
		$types = (array)$pv->get('types'); // from parent value
		$types = array_merge($types, explode(' ', (string)$spec->get('types', '')));

		// elements
		$element_type = explode(' ', $spec->get('elements', ''));
		
		// get elements list
		$elements = $this->elementsList($apps, $element_type, $types);
		
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
		$pv	  = $this->app->data->create( $this->trslValues($spec->get('parents_val'), $spec->get('value_map')) );
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
		Function: modulelist - Returns module list
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
		foreach($this->app->table->application->all(array('order' => 'name')) as $application) if ($template = $application->getTemplate())
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
	protected $_elements_list = array();
	public function elementsList($groups_filter = array(), $elements_filter = array(), $filter_types = array())
	{
		$groups_filter 		= array_filter((array)($groups_filter));
		$elements_filter 	= array_filter((array)($elements_filter));
		$filter_types 		= array_filter((array)($filter_types));

		$hash = md5(serialize( array($groups_filter, $elements_filter, $filter_types) ));
		if (!array_key_exists($hash, $this->_elements_list))
		{
			// get apps
			$apps = $this->app->table->application->all(array('order' => 'name'));
			
			// prepare types and filter app group
			$types = array();
			foreach ($apps as $app){
				if (empty($groups_filter) || in_array($app->getGroup(), $groups_filter)) {
					$types = array_merge($types, $app->getTypes());
				}
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
				// include only desired element type
				if (empty($elements_filter) || in_array($element->getElementType(), $elements_filter)) {
					$options[$element->getType()->name.' > '.$element->config->get('name')] = $element->identifier;
				}
			}

			$this->_elements_list[$hash] = $options;
		}

		// return elements array
		return @$this->_elements_list[$hash];
	}
}