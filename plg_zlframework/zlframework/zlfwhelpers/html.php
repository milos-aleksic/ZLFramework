<?php
/**
* @package		ZL Framework
* @author    	ZOOlanders http://www.zoolanders.com
* @copyright 	Copyright (C) JOOlanders SL
* @license   	http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

/*
   Class: zlfwHelperHTML
   	  A class that contains zoo html functions
*/
class zlfwHelperHTML extends AppHelper {

	public function _($type) {

		// get arguments
		$args = func_get_args();

		// Check to see if we need to load a helper file
		$parts = explode('.', $type);

		if (count($parts) >= 2) {
			$func = array_pop($parts);
			$file = array_pop($parts);

			if (in_array($file, array('zoo', 'control')) && method_exists($this, $func)) {
				array_shift($args);
				return call_user_func_array(array($this, $func), $args);
			}
		}

		return call_user_func_array(array('JHTML', '_'), $args);

	}

	/**
	 * Returns category select list html string.
	 *
	 * @param Application $application The application object
	 * @param array $options The options
	 * @param string $name The hmtl name
	 * @param string|array $attribs The html attributes
	 * @param string $key
	 * @param string $text
	 * @param string $selected The selected value
	 * @param string $idtag
	 * @param boolean $translate
	 * @param string $category The category id to build the select list for
	 *
	 * @return string category select list html
	 * @since 3.0.13
	 */
	public function categoryList($application, $type=null, $root_cat=0, $maxLevel=9999, $hide_empty=false, $prefix='-&nbsp;', $spacer='.&nbsp;&nbsp;&nbsp;')
	{
		// get category tree list
		$list = $this->app->tree->buildList($root_cat, $application->getCategoryTree(true, null, false), array(), $prefix, $spacer, '', 0, $maxLevel);

		// create options
		$categories = array();
		foreach ($list as $category)
		{
			if ($hide_empty && $type) 
			{
				// set query
				$model = $this->app->zlmodel->getNew('item');
				$model	->setState('select', 'DISTINCT a.*')
						->application(array('value' => $application->id))
						->type(array('value'  => $type))
						->categories(array('value' => array($category->id)));

				// retrieve items
				$items = $model->getList();

				// if no items skip category
				if (empty($items)) continue;
			}
			
			// save category
			$categories[] = $category;
		}

		return $categories;
	}
}