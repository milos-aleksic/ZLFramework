<?php
/**
* @package		ZL Framework
* @author    	JOOlanders, SL http://www.zoolanders.com
* @copyright 	Copyright (C) JOOlanders, SL
* @license   	http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.form.formfield');

// load config
require_once(JPATH_ADMINISTRATOR.'/components/com_zoo/config.php');

class JFormFieldZlux extends JFormField {

	protected $type = 'Zlux';

	public function getInput()
	{
		// get zoo app
		$app = App::getInstance('zoo');

 		// init var
 		$node 	  = $this->element;
		$node_atr = (array)$node->attributes();
		$node_atr = $node_atr['@attributes'];
		$class	  = $node->attributes()->class;

		// set values
		$app->zlfw->zlux->fields->joomla->setValues($this->value);

		// parse fields
		$fields = $app->zlfw->zlux->fields->joomla->parseArray($app->zlfw->xml->XMLtoArray($node));

		// set json
		$json = '{"fields": {'.implode(",", $fields).'}}';

		// set ctrl
		$ctrl = count($node->children()) > 1 ? $this->name : $this->formControl.'['.$this->group.']';
		
		// add aditional control if set
		if ($node->attributes()->addctrl) $ctrl .= "[".$node->attributes()->addctrl.']'; 

		// set arguments
		$ajaxargs  = array('node' => $node_atr);
		$arguments = array('node' => $node_atr);

		// render
		return $app->zlfw->zlux->fields->joomla->render(array($json, $ctrl, array(), '', false, $arguments), $ajaxargs, $class);
	}

}