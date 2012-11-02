<?php
/**
* @package		ZL Framework
* @author    	JOOlanders, SL http://www.zoolanders.com
* @copyright 	Copyright (C) JOOlanders, SL
* @license   	http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
*/

	echo '<div data-element-type="'.$parent->element->getElementType().'" class="zlinfo"><small>'.$parent->element->identifier.'</small>'
		.'<span><small>'.$parent->element->getMetaData('name').' '.$parent->element->getMetaData('version')
		.' <a href="https://www.zoolanders.com/" target="_blank" title="ZOOlanders">by ZL</a></small></span></div>';