<?php
/**
* @package		ZL Framework
* @author    	JOOlanders, SL http://www.zoolanders.com
* @copyright 	Copyright (C) JOOlanders, SL
* @license   	http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

	// init vars
	$attrs = '';
	$id = $params->get('id');
	
	$class = ($params->get('class') ? " {$params->get('class')}" : '');

	// attributes
	$attrs .= $params->get('type') ? " data-type='hidden'" : '';

?>

<div data-id="<?php echo $id ?>" data-layout="hidden" class="zl-row<?php echo $class ?>" <?php echo $attrs ?>>
	<?php echo $field ?>
</div>