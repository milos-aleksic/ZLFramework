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

		// init fields engine
		$engine = $app->zlfw->zlux->fields->create('joomla');

		// set field values
		$engine->setValues($this->value);

		// prepare control
		$ctrl = count($node->children()) > 1 ? $this->name : $this->formControl.'['.$this->group.']';

		// allow additional control
		if ($node->attributes()->addctrl) $ctrl .= "[".$node->attributes()->addctrl.']'; 

		// set control
		$engine->setControl($ctrl);

		// render
		return $engine->render($node);
	}
}