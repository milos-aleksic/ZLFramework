<?php
/**
* @package		ZL Framework
* @author    	JOOlanders, SL http://www.zoolanders.com
* @copyright 	Copyright (C) JOOlanders, SL
* @license   	http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

// get the list of extensions
$list = $this->app->event->dispatcher->notify($this->app->event->create(null, 'zoolanders:joomlaMenuItems'))->getReturnValue();

// get the path to sub fields
$path = count($list) == 1 ? $list[0]['path'] : $this->values->get($id);

// if only one option or there was an selection, proceede
if (!empty($path)) {
	echo implode("\n", $this->renderFields($path));

} else if (!empty($list)) {

	// set the list options
	$options = array();
	foreach ($list as $ext) {
		$options[] = $this->app->html->_('select.option', $ext['path'], $ext['name']);
	}

	$field = $this->app->html->_('select.genericlist', $options, $name, '', 'value', 'text', $this->values->get($id), $name);

	// render the select
	echo '<div class="control-group">'
		. '<div class="control-label">' . $this->getLabel($fld) . '</div>'
		. '<div class="controls">' . $field . '</div>'
		.'</div>';

	echo JText::_('Choose the extension and save');
} else {
	echo JText::_('There are no views available');
}