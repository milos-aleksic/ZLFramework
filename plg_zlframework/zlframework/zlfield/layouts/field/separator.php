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
	$final_ctrl = $params->get('final_ctrl');
	$init_state = true;
	$help = false;
	$attrs = '';
	$id = $params->get('id');
	
	$class = ($params->get('class') ? " {$params->get('class')}" : '').(!$init_state ? ' zl-disabled' : '');

	if($params->get('state') != null) {
		$init_state = $params->find('state.init_state') == '0' ? false : true;
		$attrs .= !$init_state ? ' disabled="disabled"' : '';
	}

	// attributes
	$attrs .= $params->get('type') ? " data-type='{$params->get('type')}'" : '';
	$attrs .= $params->get('dependent') ? " data-dependent='{$params->get('dependent')}'" : '';
	$attrs .= $params->get('dependents') ? " data-dependents='{$params->get('dependents')}'" : '';

	// state
	$state = null;
	if($state = $params->find('field.state', array())) {
		$state['init_state'] = $this->app->zlfield->getFieldValue($id.'_state', $params->find('field.state.init_state'), $final_ctrl);
		$state['field'] = $this->app->zlfieldhtml->_('zlf.checkboxField', $id, "{$final_ctrl}[{$id}_state]", $state['init_state'], $this->app->data->create(array()), array(), false);
	}
	$state_tooltip = $params->find('state.label') ? ' tooltip="'.JText::_($params->find('state.label')).'"' : '';

	// prepare help
	if($help = $params->find('field.help'))
	{
		$help = explode('||', $help);
		$text = JText::_($help[0]);
		unset($help[0]);

		$help = count($help) ? $this->app->zlfield->replaceVars($help[1], $text) : $text;
		//$help = $fld->get('default') ? $help.='<div class="default-value">'.strtolower(JText::_('PLG_ZLFRAMEWORK_DEFAULT')).': '.$fld->get('default').'</div>' : $help;
	}

	// prepare label
	if($label = $params->find('field.label'))
	{
		$vars = explode('||', $label);
		$text = JText::_($vars[0]);
		unset($vars[0]);

		$label = count($vars) ? $this->app->zlfield->replaceVars($vars, $text) : $text;
	}

?>

<div data-id="<?php echo $id ?>" data-layout="separator" class="zl-row<?php echo $class ?>" <?php echo $attrs ?>>

	<?php if ($label) : ?>
	<span class="zl-label">
		<?php echo $label ?>
	</span>
	<?php endif; ?>

	<div class="zl-field">
		<?php echo $field ?>
	</div>

	<?php if ($help) : ?>
	<span class="zl-help qTipHelp">
		?
		<span class="qtip-content">
			<?php echo $help ?>
		</span>
	</span>
	<?php endif; ?>
	
</div>