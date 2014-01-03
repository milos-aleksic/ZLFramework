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

	/**
	 * Method to get the field label markup.
	 *
	 * @return  string  The field label markup.
	 *
	 * @since   11.1
	 */
	protected function getLabel()
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
		if ($node->attributes()->addcontrol) $ctrl .= "[".$node->attributes()->addcontrol.']'; 

		// set control
		$engine->setControl($ctrl);

		// render

		return $app->joomla->isVersion('2.5') ? $engine->render($node) : '</div><div>' . $engine->render($node);
	}

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   11.1
	 */
	protected function getInput()
	{
		return '';
	}

	// when j3.2 wide supported this method should be used to totally remove the control layout
	// public function getControlGroup()
	// {
	// 	return $this->getInput();
	// }
}