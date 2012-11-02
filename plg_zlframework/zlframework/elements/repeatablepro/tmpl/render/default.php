<?php
/*
* @package		ZL Framework
* @author    ZOOlanders
* @copyright Copyright (C) 2011 ZOOlanders.com
* @license   	http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

	// render values
	$result = $this->getRenderedValues($params);

	$separator = $params->find('separator._by_custom') != '' ? $params->find('separator._by_custom') : $params->find('separator._by');

	echo $this->app->zlfw->applySeparators($separator, $result['result'], $params->find('separator._class'), $params->find('separator._fixhtml'));
?>