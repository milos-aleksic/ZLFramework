<?php
/**
* @package		ZL Framework
* @author    	JOOlanders, SL http://www.zoolanders.com
* @copyright 	Copyright (C) JOOlanders, SL
* @license   	http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
*/

defined('JPATH_BASE') or die;

jimport('joomla.form.formfield');

// load config
require_once(JPATH_ADMINISTRATOR.'/components/com_zoo/config.php');

class JFormFieldZldummy extends JFormField {

	protected $type = 'Zldummy';

	// usin Zl Field on modules it's not possible to save the values with no additinal CTRL because Joomla! checks the XML before saving them.
	// an workaround is to just create an dummy xml with same name as the value wonted to be saved.

	public function getInput() {
		return;
	}

	// avoid rendering the title
	public function setup(&$element, $value, $group = null){}

}