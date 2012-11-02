<?php
/**
* @package		ZL Framework
* @author    	JOOlanders, SL http://www.zoolanders.com
* @copyright 	Copyright (C) JOOlanders, SL
* extends
* @package   Widgetkit
* @author    YOOtheme http://www.yootheme.com
* @copyright Copyright (C) YOOtheme GmbH
* @license   YOOtheme Proprietary Use License (http://www.yootheme.com/license)
*/

	$widget_id = $widget->id.'-'.uniqid();
	$settings  = $widget->settings;
	$zoom      = in_array($settings['effect'], array('zoom', 'polaroid')) ? 1.4 : 1;

	// get lightbox settings and remove them
	$lbsettings	= $settings['lightbox_settings'];
	unset($settings['lightbox_settings']);

	// create the settings object
	$settings = $this->app->data->create($settings);

	// ZL integration
	$bigimg = $this->getRenderedValues($params, $widget->mode, array('file2' => true, 'width' => $settings->get('width') * $zoom, 'height' => $settings->get('height') * $zoom, 'avoid_cropping' => $settings->get('avoid_cropping')));
	$bigimg = $bigimg['result'];
	$images = $this->getRenderedValues($params, $widget->mode); // get size from element specific
	$images = $images['result'];

	$css_classes  = ($settings['corners'] == 'round') ? 'round ' : '';
	$css_classes .= ($settings['effect'] == 'zoom') ? 'zoom ' : '';
	$css_classes .= ($settings['effect'] == 'polaroid') ? 'polaroid ' : '';
	$css_classes .= ($settings['margin']) ? 'margin ' : '';

	// workaround for the main dimensions
	$settings->set('width', $params->find('specific._width', 'auto'));
	$settings->set('height', $params->find('specific._height', 'auto'));

	$lightbox_options = '';
	if(isset($lbsettings['lightbox_overide'])) foreach($lbsettings as $name => $value){
		$lightbox_options .= "$name:$value;";
	};
?>

<?php if (count($images)) : ?>
<div class="yoo-wk wk-gallery wk-gallery-wall clearfix <?php echo $css_classes; ?>">

	<?php foreach ($images as $key => $image) : ?>
	
		<?php
	
			$lightbox  = '';
			$spotlight = '';
			$overlay   = '';

			/* Prepare Caption */
			$caption = '';
			if($settings->get('zl_captions') == 2 && $title = $settings->get('_custom_caption')){
				$caption = $title;
			} elseif(strlen($image['caption'])){
				$caption = strip_tags($image['caption']);
			} else {
				$caption = $image['filename'];
			}

			/* Prepare Lightbox */
			if ($settings->get('lightbox') && !$params->find('specific._link_to_item')) // no compatible with item link
			{
				// set dimensions
				$lightbox_options .= $settings->get('width') && $settings->get('width') != 'auto' ? 'width:'.$settings->get('width').';' : '';
				$lightbox_options .= $settings->get('height') && $settings->get('width') != 'auto' ? 'height:'.$settings->get('height').';' : '';

				$lightbox = 'data-lightbox="group:'.$widget_id.';'.$lightbox_options.'"';

				// override caption options
				$lbc = $caption;
				if($settings->get('lightbox_caption') == 0){
					$lbc = '';
				} if($settings->get('lightbox_caption') == 2 && $title = $settings->get('_lightbox_custom_title')){
					$lbc = $title;
				}
	
				if (strlen($lbc)) $lightbox .= ' title="'.$lbc.'"';
			}

			/* Prepare Spotlight */
			if ($settings->get('spotlight')) 
			{
				// override caption
				if($settings->get('zl_captions') == 0) $caption = '';

				$spotlight_opts  = $settings->get('spotlight_effect') ? 'effect:'.$settings->get('spotlight_effect') : '';
				$spotlight_opts .= $settings->get('spotlight_duration') ? ';duration:'.$settings->get('spotlight_duration') : '';

				if (strlen($spotlight_opts) && strlen($caption)) {
					$spotlight = 'data-spotlight="'.$spotlight_opts.'"';
					$overlay = '<div class="overlay">'.$caption.'</div>';
				} elseif (!$settings->get('spotlight_effect')) {
					$spotlight = 'data-spotlight="on"';
				}
			}

			/* Prepare Polaroid */
			if ($settings->get('effect') == 'polaroid') {
				// it overides Spotlight
				(strlen($caption)) && $overlay = '<p class="title">'.$caption.'</p>';
			}

			/* Prepare Link */
			$rel  = $this->config->find('specific._custom_link') ? '' : 'rel="nofollow" ';
			$link = $this->config->find('specific._custom_link') && $image['link'] ? $image['link'] : $bigimg[$key]['fileurl'];
			$link = 'href="'.$link.'"'.($image['target'] ? ' target="_blank"' : '');
			
			/* Prepare Image */
			$content = '<img src="'.$image['fileurl'].'" width="'.$image['width'].'" height="'.$image['height'].'" alt="'.$image['filename'].'" />'.$overlay;	
			$content = ($settings->get('effect') == 'polaroid') ? '<div>'.$content.'</div>' : $content ;

			/* Separator - if separator tag present wrap each item */
			if(preg_match('/(^tag|\stag)=\[(.*)\]/U', $separator, $separated_by)){
				$content = $this->app->zlfw->applySeparators($separated_by[0], $content, $params->find('separator._class'), $params->find('separator._fixhtml'));
			}

		?>
	
		<?php if ($settings->get('lightbox') || $image['link']) : ?>
			<a <?php echo $rel ?>class="" <?php echo $link; ?> <?php echo $lightbox; ?> <?php echo $spotlight; ?>><?php echo $content; ?></a>
		<?php elseif ($settings->get('effect') == 'spotlight') : ?>
			<div <?php echo $spotlight; ?>><?php echo $content; ?></div>
		<?php else : ?>		
			<?php echo $content; ?>
		<?php endif; ?>
		
	<?php endforeach; ?>

</div>

<?php else : ?>
	<?php echo "No images found."; ?>
<?php endif; ?>