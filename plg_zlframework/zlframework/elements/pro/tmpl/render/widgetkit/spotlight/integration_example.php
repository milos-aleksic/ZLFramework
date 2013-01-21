<?php
/**
* @package		ZL FrameWork
* @author    	JOOlanders, SL http://www.zoolanders.com
* @copyright 	Copyright (C) JOOlanders, SL
* @license   	http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

	/* Prepare Spotlight */
	if ($settings->get('spotlight')) 
	{
		// override caption
		if($settings->get('zl_captions') == 0) $caption = '';

		if ($settings['spotlight_effect'] && $caption) {
			$spotlight = 'data-spotlight="effect:left"';
			$overlay = '<div class="overlay">'.$caption.'</div>';
		} elseif (!$settings['spotlight_effect']) {
			$spotlight = 'data-spotlight="on"';
		}
	}

	$content = '<a style="width: '.$image['width'].'px;" href="'.$link.'" '.$spotlight.'>'.$content.'</a>';

?>