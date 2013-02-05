<?php
/**
* @package		ZL Framework
* @author    	JOOlanders, SL http://www.zoolanders.com
* @copyright 	Copyright (C) JOOlanders, SL
* @license   	http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
*/

// no direct access
defined('_JEXEC') or die('Restricted access');
	
	$toggle = JText::_($fld->get('toggle'));
?>
	
	<div class="zltoggle-btn open">
		<span>-</span>
		<?php echo $toggle ?>
		<div class="toggle-text">...<small><?php echo strtolower($toggle) ?></small>...</div>
	</div>
	<div class="wrapper zltoggle" data-id="<?php echo $id ?>" >
		<?php echo $content ?>
	</div>