<?php
/**
* @package		ZL Framework
* @author    	JOOlanders, SL http://www.zoolanders.com
* @copyright 	Copyright (C) JOOlanders, SL
* @license   	http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
*/

// no direct access
defined('_JEXEC') or die('Restricted access');
	
	// prepare label
	if($label = $fld->find('specific.toggle.label'))
	{
		$vars = explode('||', $label);
		$text = JText::_($vars[0]);
		unset($vars[0]);

		$label = count($vars) ? $this->app->zlfield->replaceVars($vars, $text) : $text;
	}

?>

	<?php if ($fld->find('specific.toggle')) : ?>

	<fieldset class="wrapper" data-layout="fieldset-toggle" data-id="<?php echo $id ?>">

		<div class="zl-toggle open">
			<div class="btn-close">
				<?php echo $label ?>
				<span class="sign"></span>
			</div>
			<div class="btn-open">
				<?php echo $label ?>
				<span class="sign"></span>
			</div>
		</div>

		<div class="zl-toggle-content">
			<?php echo $content ?>
		</div>

	</fieldset>

	<?php else : ?>

	<fieldset class="wrapper" data-id="<?php echo $id ?>">
		<?php echo $content ?>
	</fieldset>

	<?php endif; ?>