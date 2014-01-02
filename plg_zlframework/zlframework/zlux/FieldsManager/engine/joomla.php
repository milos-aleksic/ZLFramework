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
   Class: zluxFieldsEngineJoomla
*/
class zluxFieldsEngineJoomla extends zluxFieldsEngine
{
	/**
	 * Class constructor
	 *
	 * @param string $app App instance.
	 */
	public function __construct($app)
	{
		parent::__construct($app);
	}

	/*
		Function: row - Returns row html string
	*/
	public function renderField($fld, $id, $value, $args = array())
	{
		$field = parent::renderField($fld, $id, $value, $args);

		if ($fld->get('layout')) {

			$html = array();
			$html[] = '<div class="control-group">';
				$html[] = '<div class="control-label">';
					$html[] = $fld->get('label');
				$html[] = '</div>';
				$html[] = '<div class="controls">';
					$html[] = $field;
				$html[] = '</div>';
			$html[] = '</div>';

			return implode("\n", $html);
		}

		return $field;
	}
}