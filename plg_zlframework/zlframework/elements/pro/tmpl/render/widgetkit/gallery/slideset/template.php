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

	$widget_id 		= $widget->id.'-'.uniqid();
	$settings  		= $widget->settings;
	$sets = $lbsets = array();
	$nav      		= array();
	$i = 0;

	// create the settings objects
	$settings  = $this->app->data->create($settings);
	$lbsettings = $this->app->data->create($params->find('layout.widgetkit.lightbox'));
	$slsettings = $this->app->data->create($params->find('layout.widgetkit.spotlight'));

	// create thumbs
	$thumbs		= $this->getRenderedValues($params, $widget->mode, array('width' => $params->find('layout._width'), 'height' => $params->find('layout._height'), 'avoid_cropping' => $params->find('layout._avoid_cropping')));
	$thumbs 	= $thumbs['result'];

	$sets 		= array_chunk($thumbs, $settings->get('items_per_set'));

	// create lightbox img
	if($lbsettings->get('lightbox')){
		$lbimgs 	= $this->getRenderedValues($params, $widget->mode, array('file2' => true, 'width' => $lbsettings->get('width'), 'height' => $lbsettings->get('height'), 'avoid_cropping' => $lbsettings->get('avoid_cropping')));
		$lbimgs 	= $lbimgs['result'];
		$lbsets 	= array_chunk($lbimgs, $settings->get('items_per_set'));
	}

	foreach (array_keys($sets) as $s) {
		$nav[] = '<li><span></span></li>';
	}

	$lightbox_options = '';
	if(isset($lbsettings['lightbox_overide'])) foreach($lightbox as $name => $value){
		$lightbox_options .= "$name:$value;";
	};
?>

<div id="slideset-<?php echo $widget_id;?>" class="yoo-wk wk-slideset wk-slideset-default" data-widgetkit="slideset" data-options='<?php echo json_encode($settings); ?>'>
	<div>
		<div class="sets">
			<?php foreach ($sets as $set => $items) : ?>
				<ul class="set">
					<?php foreach ($items as $key => $image) : 

						$lightbox = $spotlight = $overlay = '';

						/* Prepare Caption */
						$caption = '';
						if(strlen($image['caption'])){
							$caption = strip_tags($image['caption']);
						} else {
							$caption = $image['filename'];
						}

						/* Prepare Lightbox */
						if ($lbsettings->get('lightbox') && !$params->find('layout._link_to_item')) // no compatible with item link
						{
							// set dimensions
							$lightbox_options .= $settings->get('width') && $settings->get('width') != 'auto' ? 'width:'.$settings->get('width').';' : '';
							$lightbox_options .= $settings->get('height') && $settings->get('width') != 'auto' ? 'height:'.$settings->get('height').';' : '';

							$lightbox = 'data-lightbox="group:'.$widget_id.';'.$lightbox_options.'"';

							// override caption options
							$lbc = $caption;
							if($lbsettings->get('title') == 0){
								$lbc = '';
							} if($lbsettings->get('title') == 2 && $title = $lbsettings->get('custom_title')){
								$lbc = $title;
							}
				
							if (strlen($lbc)) $lightbox .= ' title="'.$lbc.'"';
						}

						/* Prepare Spotlight */
						if ($slsettings->get('spotlight')) 
						{
							$slcaption = $caption; // set lightbox caption
							
							// override caption
							if($slsettings->get('captions') == 2 && $title = $slsettings->get('custom_caption')){
								$slcaption = $title;
							}

							$spotlight_opts  = $slsettings->get('spotlight_effect') ? 'effect:'.$slsettings->get('spotlight_effect') : '';
							$spotlight_opts .= $slsettings->get('spotlight_duration') ? ';duration:'.$slsettings->get('spotlight_duration') : '';

							if (strlen($spotlight_opts) && strlen($slcaption)) {
								$spotlight = 'data-spotlight="'.$spotlight_opts.'"';
								$overlay = '<div class="overlay">'.$slcaption.'</div>';
							} elseif (!$slsettings->get('spotlight_effect')) {
								$spotlight = 'data-spotlight="on"';
							}
						}

						/* Prepare Image */
						$content = '<img src="'.$image['fileurl'].'" width="'.$image['width'].'" height="'.$image['height'].'" alt="'.$image['filename'].'" />'.$overlay;
					
						/* Lazy Loading */
						$content = ($i==0) ? $content : $widgetkit['image']->prepareLazyload($content);

						/* Separator - if separator tag present wrap each item */
						if(preg_match('/(^tag|\stag)=\[(.*)\]/U', $separator, $separated_by)){
							$content = $this->app->zlfw->applySeparators($separated_by[0], $content, $params->find('separator._class'), $params->find('separator._fixhtml'));
						}

						/* Prepare Link */
						$rel  = $this->config->find('specific._custom_link') ? '' : 'rel="nofollow" ';
						$url  = $lbsettings->get('lightbox') ? $lbsets[$set][$key]['fileurl'] : $image['fileurl'];
						$link = $this->config->find('specific._custom_link') && $image['link'] ? $image['link'] : $url;
						$link = 'href="'.$link.'"'.($image['target'] ? ' target="_blank"' : '');

					?>

					<?php if ($lbsettings->get('lightbox') || strlen($image['link'])) : ?>
						<li><article class="wk-content">
							<a <?php echo $rel ?>class="" style="<?php echo 'width: '.$image['width'].'px;'; ?>" <?php echo $link ?> <?php echo $lightbox; ?> <?php echo $spotlight; ?>><?php echo $content; ?></a>
						</article></li>
					<?php elseif ($slsettings->get('spotlight')) : ?>
						<li><article class="wk-content">
							<div style="<?php echo 'width: '.$image['width'].'px;'; ?>" <?php echo $spotlight; ?>><?php echo $content; ?></div>
						</article></li>
					<?php else : ?>		
						<li><article class="wk-content"><?php echo $content; ?></article></li>
					<?php endif; ?>

					<?php endforeach; ?>
				</ul>
				<?php $i=$i+1;?>
			<?php endforeach; ?>
		</div>
		<?php if ($settings->get('buttons')): ?><div class="next"></div><div class="prev"></div><?php endif; ?>
	</div>
	<?php if ($settings->get('navigation') && count($nav)) : ?>
	<ul class="nav <?php echo (is_numeric($settings->get('items_per_set'))) ? 'icon' : 'text'; ?>"><?php echo implode('', $nav); ?></ul>
	<?php endif; ?>
</div>