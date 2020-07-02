<?php

defined( '_JEXEC' ) or die();

// $privacy_link = JText::sprintf(('MOD_FLXMLFORMS_FORM_NOTICE_DEFAULT'), JRoute::_('index.php?Itemid='. $params->get('privacy')));

?>
<div class="uk-container uk-container-center">
	<div class="uk-block" id="<?php echo $module->id; ?>">
		<form class="uk-form fl-formajax form-validate fl-form-overlay" method="post" name="<?php echo $module->id; ?>">
			<h2 class="uk-h3 uk-text-center uk-margin-bottom-remove"><?php echo $form->label; ?></h2>
			<p class="legend"><?php echo JText::_('MOD_FLXMLFORMS_FORM_NOTICE_REQUIRED_STAR'); ?></p>
			<?php foreach ($form->getFieldsets() as $fieldset) : ?>
				<?php if ($fieldset->name === 'captcha' && !$captcha) : ?>
					<?php continue; ?>
				<?php endif; ?>
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
			
			<div class="uk-form-row uk-margin uk-margin-bottom-remove">
				<div class="uk-form-controls uk-text-center">
					<button type="submit" class="uk-button uk-button-primary validate"><?php echo $form->submit; ?></button>
				</div>
			</div>
			
			<?php echo JHtml::_('form.token'); ?>
			
			<div class="fl-form-loading" hidden></div>
		</form>
	</div>
</div>