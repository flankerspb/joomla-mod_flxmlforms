<?php

defined( '_JEXEC' ) or die();

$privacy_link = JHTML::link(JRoute::_('index.php?Itemid='. '129'), JText::_('MOD_FLXMLFORMS_FORM_NOTICE_DEFAULT'),'target="_blank"');
$privacy_link = JText::sprintf(('MOD_FLXMLFORMS_FORM_NOTICE_DEFAULT'), JRoute::_('index.php?Itemid='. $params->get('privacy')));

?>
<div id="flmodalform<?php echo $module->id; ?>" class="uk-modal">
	<div class="uk-modal-dialog">
		<a class="uk-modal-close uk-close"></a>
		<input hidden>
		<div class="uk-overlay fl-form-overlay" id="<?php echo $module->id; ?>">
			<form class="uk-form fl-formajax form-validate" method="post" name="<?php echo $module->id; ?>">
				<h3 class="uk-text-center uk-margin-bottom-remove"><?php echo $form->label; ?></h3>
				<p class="legend"><?php echo JText::_('MOD_FLXMLFORMS_FORM_NOTICE_REQUIRED_STAR'); ?></p>
				<?php foreach ($form->getFieldsets() as $fieldset) : ?>
					<?php if($fieldset->name === 'captcha' && !$captcha) continue; ?>
					<?php $fields = $form->getFieldset($fieldset->name); ?>
					<?php if (count($fields)) : ?>
						<div class="<?php echo $fieldset->class; ?>" <?php echo $fieldset->attribs; ?>>
						<?php foreach ($fields as $field) : ?>
							<?php if($field->type === 'captcha' && !$captcha) continue; ?>
							<?php echo $field->renderField(); ?>
						<?php endforeach; ?>
						</div>
					<?php endif; ?>
				<?php endforeach; ?>
				
				<div class="control-group uk-margin">
					<label id="privacy-lbl" for="privacy" class="required" required hidden><?php echo JText::_('MOD_FLXMLFORMS_FORM_LABEL_PRIVACY_CONFIRM'); ?></label>
					<span class="controls">
						<input type="checkbox" name="privacy" id="privacy" class="required" required>
					</span> 
					<span class="control-label"> <span class="required"><?php echo $privacy_link; ?></span><span class="star">&nbsp;*</span>
					</span>
				</div>
				
				<div class="control-group uk-margin uk-margin-bottom-remove">
					<div class="controls uk-text-center">
						<button type="submit" class="uk-button uk-button-primary validate"><?php echo $form->submit; ?></button>
					</div>
				</div>
				
				<?php echo JHtml::_('form.token'); ?>
				
			</form>
			<div class="fl-form-loading uk-overlay-panel uk-overlay-background uk-overlay-icon" hidden></div>
		</div>
	</div>
</div>
