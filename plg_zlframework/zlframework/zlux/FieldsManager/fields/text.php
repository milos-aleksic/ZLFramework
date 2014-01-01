<?php
/**
* @package		ZL Framework
* @author    	JOOlanders, SL http://www.zoolanders.com
* @copyright 	Copyright (C) JOOlanders, SL
* @license   	http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

// initialize some field attributes.
$size = @$size ? ' size="' . (int) $size . '"' : '';
$maxLength = @$maxlength ? ' maxlength="' . (int) $maxlength . '"' : '';
$class = @$class ? ' class="' . (string) $class . '"' : '';
$readonly = ((string) @$readonly == 'true') ? ' readonly="readonly"' : '';
$disabled = ((string) @$disabled == 'true') ? ' disabled="disabled"' : '';
$placeholder = ((string) @$placeholder == 'true') ? ' placeholder="'.JText::_($placeholder).'"' : '';

// Initialize JavaScript field attributes.
$onchange = @$onchange ? ' onchange="' . (string) $onchange . '"' : '';

$tag_id = preg_replace(array('/\W+/', '/_$/'), array('_', ''), $name);

echo '<input type="text" name="' . $name . '" id="' . $tag_id . '"' . ' value="'
	. htmlspecialchars($this->values->get($id), ENT_COMPAT, 'UTF-8') . '"' . $class . $size . $disabled . $readonly . $onchange . $maxLength . $placeholder . '/>';