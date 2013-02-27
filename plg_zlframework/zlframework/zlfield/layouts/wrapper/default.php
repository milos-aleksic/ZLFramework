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
	$attrs .= $fld->get('dependent') ? " data-dependent='{$fld->get('dependent')}'" : '';
	
?>

	<div class="wrapper" data-id="<?php echo $id ?>" <?php echo $attrs ?>>
		<?php echo $content ?>
	</div>