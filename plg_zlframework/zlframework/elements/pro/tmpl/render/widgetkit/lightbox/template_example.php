<?php
/**
* @package		ZL Elements
* @author    	ZOOlanders http://www.zoolanders.com
* @copyright 	Copyright (C) JOOlanders, SL
* extends
* @package   Widgetkit
* @author    YOOtheme http://www.yootheme.com
* @copyright Copyright (C) YOOtheme GmbH
* @license   YOOtheme Proprietary Use License (http://www.yootheme.com/license)
*/

	$widget_id  = $widget->id.'-'.uniqid();
	$settings  	= $widget->settings;
	
	// get elements values
	$links = $this->getRenderedValues($params, $widget->mode);

	// get lightbox settings and remove them from widget ones
	$lbsettings	= $settings['lightbox_settings'];
	unset($settings['lightbox_settings']);
	
	// set custom lightbox options
	$lightbox_options = '';
	if(isset($lbsettings['lightbox_overide']))
	{
		isset($settings['lightbox_caption']) && !$settings['lightbox_caption'] && $lbsettings['titlePosition'] = 'none'; // depricated option, keep it during transition (18.05.2012)
		$settings['width'] != 'auto' && $lbsettings['width'] = $settings['width'];
		$settings['height'] != 'auto' && $lbsettings['height'] = $settings['height'];

		foreach($lbsettings as $name => $value){
			$lightbox_options .= "$name:$value;";
		}
	}

	// custom lightbox title from position
	$title = $lbsettings['_custom_title'] ? 'title="'.$lbsettings['_custom_title'].'"' : '';

	$result = array();
?>

<?php if (count($links['result'])) : ?>

	<?php foreach ($links['result'] as $key => $link) : ?>
	
		<?php

			$title 		= $link['title'] && empty($title) ? 'title="'.$link['title'].'"' : $title;
			$rel	 	= $link['rel'] ? 'rel="'.$link['rel'].'"' : '';
			$class	 	= $link['class'] ? 'class="'.$link['class'].'"' : '';

			/* Prepare Lightbox */
			$lightbox = 'data-lightbox="group:'.$widget_id.';'.$lightbox_options.'"';

			// link
			if (empty($link['url']) && $params->find('layout._if_no_url') == '1'){
				$result[] = $link['text'];
			} else if ($link['url']){
				$result[] = '<a href="'.JRoute::_($link['url']).'" '.$title.' '. $rel .' '.$class.' '.$lightbox.'>'.$link['text'].'</a>';
			}

		?>
	
	<?php endforeach; ?>
	
	<?php echo $this->app->zlfw->applySeparators($params->find('separator._by'), $result, $params->find('separator._class')); ?>
	
<?php else : ?>
	<?php echo "No links found."; ?>
<?php endif; ?>