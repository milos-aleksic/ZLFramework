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

	$widget_id      = $widget->id.'-'.uniqid();
	$settings       = $widget->settings;
	$captions       = array();
	$i = 0;

	// create the settings object
	$settings = $this->app->data->create($settings);
	
	$images         = $this->getRenderedValues($params, $widget->mode);
	$images 		= $images['result'];
	$thumbs         = $this->getRenderedValues($params, $widget->mode, array('width' => $settings->get('thumb_width'), 'height' => $settings->get('thumb_height')));
	$thumbs 		= $thumbs['result'];
	
	$sets           = array_chunk($thumbs, $settings->get('items_per_set'));

	foreach (array_keys($sets) as $s) {
		$nav[] = '<li><span></span></li>';
	}
	
	// workaround for the main dimensions
	$settings->set('width', $params->find('specific._width', 'auto'));
	$settings->get('height', $params->find('specific._height', 'auto'));
?>

<?php if (count($images)) : ?>
<div id="gallery-<?php echo $widget_id; ?>" class="yoo-wk wk-gallery-showcase" data-widgetkit="showcase" data-options='<?php echo json_encode($settings); ?>'>

	<div id="slideshow-<?php echo $widget_id; ?>" class="wk-slideshow">
		<div class="slides-container">
			<ul class="slides">

				<?php foreach ($images as $image) : ?>
				
					<?php
					
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
						$link = $this->config->find('specific._custom_link') && $image['link'] ? $image['link'] : '';
						if (strlen($image['link']))
						{
							$content = '<a '.$rel.'href="'.$link.'"'.($image['target'] ? ' target="_blank"' : '').'>'.$content.'</a>';
						}
					?>

					<li><?php echo $content; ?></li>
					
					<?php $i=$i+1;?>
				<?php endforeach; ?>

			</ul>
			<?php if ($settings['buttons']): ?><div class="next"></div><div class="prev"></div><?php endif; ?>
			<?php if ($settings->get('zl_captions')) : ?><div class="caption"></div><ul class="captions"><?php echo implode('', $captions);?></ul><?php endif; ?>
		</div>
	</div>

	<div id="slideset-<?php echo $widget_id;?>" class="wk-slideset <?php if (!$settings['slideset_buttons']) echo 'no-buttons'; ?>">
		<div>
			<div class="sets">
				<?php foreach ($sets as $set => $items) : ?>
				<ul class="set">
					<?php foreach ($items as $thumb) : ?>
					
					<?php
						/* Prepare Image */
						$content = '<img src="'.$thumb['fileurl'].'" width="'.$thumb['width'].'" height="'.$thumb['height'].'" alt="'.$thumb['filename'].'" />';
					?>
					
					<li><div><?php echo $content; ?></div></li>
					<?php endforeach; ?>
				</ul>
				<?php endforeach; ?>
			</div>
			<?php if ($settings['slideset_buttons'] && count($sets) > 1) : ?><div class="next"></div><div class="prev"></div><?php endif; ?>
		</div>
	</div>
	
</div>

<?php else : ?>
	<?php echo "No images found."; ?>
<?php endif; ?>