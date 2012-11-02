<?php
/**
* @package		ZL Elements
* @author    	ZOOlanders http://www.zoolanders.com
* @copyright 	Copyright (C) JOOlanders, SL
*/

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