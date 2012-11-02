<?php 
/**
* @package		ZL Framework
* @author    	JOOlanders, SL http://www.zoolanders.com
* @copyright 	Copyright (C) JOOlanders, SL
* @license   	http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
*/

if($this->app->zlfw->checkExt('com_widgetkit'))
{
	// load widgetkit
	require_once(JPATH_ADMINISTRATOR.'/components/com_widgetkit/widgetkit.php');
	$widgetkit = Widgetkit::getInstance();

	// load assets
	$this->app->document->addStylesheet('elements:pro/tmpl/render/widgetkit/widgetkit.css');

	// init vars
	$lparams  = $this->app->data->create($params->get('layout'));
	$layout   = "render/widgetkit/{$lparams->find('widgetkit._widget')}/{$lparams->find('widgetkit._style')}/template.php";
	
	// prepare widget settings
	$settings = array();
	foreach ((array) $lparams->find('widgetkit.settings') as $key => $value) {
		$settings[$key] = is_numeric($value) ? (float) $value : $value;
	}
	
	// set widget object
	$widget 			= $this->app->data->create();
	$widget->id 		= $this->identifier;
	$widget->settings 	= $settings;
	$widget->mode 		= "{$lparams->find('widgetkit._widget')}.{$lparams->find('widgetkit._style')}";
	
	// render widget
	if ($layout = $this->getLayout($layout))
	{
		// set separator options
		$separator = $params->find('separator._by') == 'custom' ? $params->find('separator._by_custom') : $params->find('separator._by');

		$result = $this->renderLayout($layout, compact('widget', 'params', 'widgetkit', 'separator'));
	
		// if enclosing tag present wrap whole layout
		if(preg_match('/enclosing_tag=\[(.*)\]/U', $separator, $enclosed_by)){
			$result = $this->app->zlfw->applySeparators($enclosed_by[0], $result, $params->find('separator._class'), $params->find('separator._fixhtml'));
		}
		
		echo $result;
	}
}
else
{
	echo JText::_('PLG_ZLFRAMEWORK_WK_NOT_PRESENT');
}
	
?>