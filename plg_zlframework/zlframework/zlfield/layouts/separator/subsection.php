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
	$title = JText::_($field->find('specific.title'));
?>

	<div class="row subsection-title" data-type="separator" data-id="<?php echo $id ?>" >
		<?php echo $title ?>
	</div>