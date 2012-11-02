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
	$i = 0;
	
	// get lightbox settings and remove them
	$lbsettings	= $settings['lightbox_settings'];
	unset($settings['lightbox_settings']);

	// create the settings object
	$settings = $this->app->data->create($settings);

	// ZL integration
	$bigimg = $this->getRenderedValues($params, $widget->mode, array('file2' => true, 'width' => $settings->get('width'), 'height' => $settings->get('height'), 'avoid_cropping' => $settings->get('avoid_cropping')));
	$bigimg = $bigimg['result'];
	$images = $this->getRenderedValues($params, $widget->mode);
	$images = $images['result'];

	// workaround for the main dimensions
	$width = $params->find('specific._width');
	$settings->set('width', empty($width) ? 'auto' : $width);
	$height = $params->find('specific._height');
	$settings->set('height', empty($height) ? 'auto' : $height);
	
	$lightbox_options = '';
	if(isset($lbsettings['lightbox_overide'])) foreach($lbsettings as $name => $value){
		$lightbox_options .= "$name:$value;";
	};
?>

<?php if (count($images)) : ?>
<div id="gallery-<?php echo $widget_id; ?>" class="yoo-wk wk-slideshow wk-slideshow-default" data-widgetkit="slideshow" data-options='<?php echo json_encode($settings); ?>'>
	<div>
		<ul class="slides">

			<?php foreach ($images as $key => $image) : ?>
            
				<?php
				
					$navigation[] = '<li><span></span></li>';

					/* Prepare Captions */
					$caption = '';
					if($settings->get('zl_captions') == 2 && $title = $settings->get('_custom_caption')){
						$caption = $title;
					} elseif(strlen($image['caption'])){
						$caption = strip_tags($image['caption']);
					} else {
						$caption = $image['filename'];
					}
					$captions[]   = "<li>$caption</li>";
					
					/* Prepare Image */
					$content = '<img src="'.$image['fileurl'].'" width="'.$image['width'].'" height="'.$image['height'].'" alt="'.$image['filename'].'" />';
					
					/* Lazy Loading */				
					$content = ($i==$settings['index']) ? $content : $widgetkit['image']->prepareLazyload($content);

					/* Separator - if separator tag present wrap each item */
					if(preg_match('/(^tag|\stag)=\[(.*)\]/U', $separator, $separated_by)){
						$content = $this->app->zlfw->applySeparators($separated_by[0], $content, $params->find('separator._class'), $params->find('separator._fixhtml'));
					}

					/* Prepare Link */
					$rel  = $this->config->find('specific._custom_link') ? '' : 'rel="nofollow" ';
					$link = $this->config->find('specific._custom_link') && $image['link'] ? $image['link'] : $bigimg[$key]['fileurl'];
					if (strlen($image['link']))
					{
						$content = '<a '.$rel.'href="'.$link.'"'.($image['target'] ? ' target="_blank"' : '').'>'.$content.'</a>';
					}

					// lightbox integration
					elseif ($settings->get('lightbox')) 
					{
						// set dimensions
						$lightbox_options .= $settings->get('width') && $settings->get('width') != 'auto' ? 'width:'.$settings->get('width').';' : '';
						$lightbox_options .= $settings->get('height') && $settings->get('width') != 'auto' ? 'height:'.$settings->get('height').';' : '';

						// override caption
						if($settings->get('lightbox_caption') == 0){
							$caption = '';
						} if($settings->get('lightbox_caption') == 2 && $title = $settings->get('_lightbox_custom_title')){
							$caption = $title;
						}

						$caption = strlen($caption) ? ' title="'.$caption.'"' : '';
						$content = '<a '.$rel.'href="'.$link.'" data-lightbox="group:'.$widget_id.';'.$lightbox_options.'"'.$caption.'>'.$content.'</a>';
					}
					
				?>

				<li><?php echo $content; ?></li>
				
				<?php $i=$i+1;?>
			<?php endforeach; ?>
			
		</ul>
        <?php if ($settings['buttons']): ?><div class="next"></div><div class="prev"></div><?php endif; ?>
		<?php if ($settings->get('zl_captions')) : ?><div class="caption"></div><ul class="captions"><?php echo implode('', $captions);?></ul><?php endif; ?>
	</div>
	<?php echo ($settings['navigation'] && count($navigation)) ? '<ul class="nav">'.implode('', $navigation).'</ul>' : '';?>
</div>
	
<?php else : ?>
	<?php echo "No images found."; ?>
<?php endif; ?>