<?php
/*
* @package		ZL Framework
* @author    	JOOlanders, SL http://www.zoolanders.com
* @copyright 	Copyright (C) JOOlanders, SL
* @license   	http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
*/

/*
	Class: ElementStaticContent
		The Static Content element class
*/
class ElementStaticContent extends ElementPro {

	protected $_rendered_values = array();

	/*
		Function: hasValue
			Override. Checks if the element's value is set.

	   Parameters:
			$params - render parameter

		Returns:
			Boolean - true, on success
	*/
	public function hasValue($params = array()) {
		$value = $this->getRenderedValues( $this->app->data->create($params) );
		return !empty($value['result']);
	}
	
	/*
		Function: getRenderedValues
			renders the element content

		Returns:
			array
	*/
	protected function getRenderedValues($params = array())
	{
		$hash = md5( serialize(array($params->get('layout'), $params->get('specific'), $params->get('separator'))) );
		if (!array_key_exists ($hash, $this->_rendered_values))
		{
			// init vars
			$result = '';
			$sp 	=  $this->app->data->create($params->get('specific'));
			
			switch ($sp->get('_render', 'text')) 
			{
				case 'text':
					if ($sp->get('_loadplugins', '')){
						$result = $this->app->zoo->triggerContentPlugins($sp->get('_text'));
					} else {
						$result = $sp->get('_text');
					}
					$result = $result ? $result : ' '; // if no text, render space (possible qTip integration)
					break;
					
				case 'link':
					$title  = $sp->get('_linktitle', '') ? ' title="'.$sp->get('_linktitle').'"' : '';
					$target = $sp->get('_linktarget', '') ? ' target="_blank"' : '';
					$rel 	= $sp->get('_rel', '') ? ' rel="'.$sp->get('_rel').'"' : '';
					$text	= $sp->get('_linktext', '');
					$url 	= '';
					
					switch ($sp->get('_linktype', 'item')) 
					{
						case 'item':
							$item = ($sp->get('_linkitemsource') == 'specified' && $sp->get('_linkitemid', '')) ? $this->app->table->item->get($sp->get('_linkitemid')) : $this->_item;
							$text = $text ? $text : $item->name;
							if (!$item->getState()) return array($text); // if item no published return name
							$item_layout = $sp->get('_linkitemlayout', 'full') != 'full' ? '&item_layout='.$sp->get('_linkitemlayout', '') : false;
							$url  = $item_layout ? 'index.php?option='.$this->app->component->self->name.'&task=item&item_id='.$item->id.$item_layout : $this->app->route->item($item);
							break;
							
						case 'category':
							$cat  = $this->app->table->category->get($sp->get('_linkcatid'));
							$text = $text ? $text : $cat->name;
							if (!$cat->isPublished()) return array($text); // if cat no published return name
							$url  = $this->app->route->category($cat);
							break;
							
						case 'custom':
							$url	= $sp->get('_linkurl', '');
							$text	= $text ? $text : 'link';
							break;
					}
					
					$result = '<a href="'.JRoute::_($url).'"'.$title.$target.$rel.' >'.$text.'</a>';
					break;
					
				case 'item':
					$item_id = $sp->get('_itemsource') == 'current' ? $this->_item->id : $sp->get('_itemid');
					$result = $this->app->zlfw->renderView($item_id, $sp->get('_itemlayout'));
					break;
					
				case 'module':
					// get modules
					$result = $this->app->zlfw->renderModule($sp->get('_module'));
					
					break;

				case 'iframe':
					// render iframe
					$item_id = $params->find('specific._itemsource') == 'current' ? $this->_item->id : $params->find('specific._itemid');
					$iframelink = $params->find('specific._iframe_custom_url') ? $params->find('specific._iframe_custom_url') : $this->app->link(array('controller' => 'zlframework', 'task' => 'renderview', 'tmpl' => 'component', 'item_id' => $item_id, 'item_layout' => $params->find('specific._itemlayout', 'related')), false);
					$result = '<iframe class="qtip-iframe" src="'.$iframelink.'" width="100%" height="100%"><p>'.JText::_('QT_IFRAME_NOT_SUPPORTED').'</p></iframe>';
				break;
			}
			
			$result = $result ? array($result) : null; // if there is value, set the array
			$this->_rendered_values[$hash] = compact('result');			
		}
		
		return $this->_rendered_values[$hash];
	}
	
	/*
	   Function: edit
	       Renders the edit form field.
	*/
	public function edit() {
		return null;
	}

}