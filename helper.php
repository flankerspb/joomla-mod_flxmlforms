<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_flxmlforms
 *
 * @copyright   Copyright (C) 2017 - 2018 Vitaliy Moskalyuk. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

class ModflxmlformsHelper
{
	public function getAjax()
	{
		$error_msg = '';
		
		$app = JFactory::getApplication();
		$input = $app->input;
		$data = $input->get('jform', array(), 'array');
		$mid = $input->get('mid', 0);
		
		if(!$mid)
		{
			$error_msg .= JText::_('JLIB_RULES_REQUEST_FAILURE');
			return new InvalidArgumentException($error_msg, 404);
		}
		
		$module = JModuleHelper::getModuleById($mid);
		
		if(!$module->id || $module->name != 'flxmlforms')
		{
			$error_msg .= JText::_('JLIB_RULES_REQUEST_FAILURE');
			return new InvalidArgumentException($error_msg, 404);
		}
		
		$module->params = json_decode($module->params);
		
		// Check for request forgeries.
		$token = $app->getSession()->getFormToken();
		// JFactory::getSession()->isNew();
		
		if(!array_key_exists($token, $input->getArray()))
		{
			$error_msg .= JText::_('JERROR_SESSION_STARTUP');
			return new InvalidArgumentException($error_msg, 404);
		}
		
		// Check for a valid session cookie
		if ($module->params->validate_session)
		{
			if (JFactory::getSession()->getState() !== 'active')
			{
				// Save the data in the session.
				$app->setUserState('mod_flxmlforms.form_' . $mid . '.data', $data);
				$error_msg .= JText::_('JERROR_SESSION_STARTUP');
				return new RuntimeException($error_msg, 404);
			}
		}
		
		// Validate the posted data.
		$form = JForm::getInstance('mod_flxmlforms.form_' . $mid, __DIR__ .'/forms/' . $module->params->form . '.xml', array('control' => 'jform'));
		
		if(!$form)
		{
			$error_msg .= JText::_('JGLOBAL_VALIDATION_FORM_FAILED');
			return new InvalidArgumentException($error_msg, 404);
		}
		
		// Fix module Captcha
		$captcha = $module->params->captcha != '' ? $module->params->captcha : $app->get('captcha');
		$app->getParams()->set('captcha', $captcha);
		$app->set('captcha', $captcha);
		
		// Validate form
		if (!$form->validate($data))
		{
			$errors = $form->getErrors();
			
			$message = array();
			$message[] = $error_msg;
			
			foreach ($errors as $error)
			{
				if ($error instanceof Exception)
				{
					$message[] = $error->getMessage();
				}
				else
				{
					$message[] = $error;
				}
			}
			
			// Save the data in the session.
			$app->setUserState('mod_flxmlforms.form_' . $mid . '.data', $data);
			
			return new InvalidArgumentException(implode("\n", $message), 404);
		}
		
		// Flush the data from the session
		$app->setUserState('mod_flxmlforms.form_' . $mid . '.data', null);
		
		// Get sender
		$class = 'ModflxmlformsСontroller' . $module->params->controller;
		$file =  __DIR__ . '/controllers/'. $module->params->controller . '.php';
		
		if(!file_exists($file))
		{
			return new RuntimeException(JText::_('PHPMAILER_EXTENSION_MISSING') . $module->params->controller, 404);
		}
		
		JLoader::register('ModflxmlformsСontrollerBase', __DIR__ . '/controllers/_base.php');
		JLoader::register($class, $file);
		
		$data['subject'] = $form->getAttribute('subject', 'MOD_FLXMLFORMS_MESSAGE_SUBJECT_DEFAULT');
		
		// Send the message
		$sent = $class::send($data, $module->params);
		//$sent = false;
		
		// Set the success message if it was a success
		if($sent === true)
		{
			return JText::_($form->getAttribute('success', 'MOD_FLXMLFORMS_MESSAGE_SUCCESS'));
		}
		else if($sent === false)
		{
			$error_msg .= JText::_($form->getAttribute('error', 'MOD_FLXMLFORMS_MESSAGE_FUNCTION_OFFLINE'));
			return new RuntimeException($error_msg, 404);
		}
		else if($sent instanceof Exception)
		{
			return new RuntimeException($sent->getMessage(), 404);
		}
		else
		{
			return null;
		}
	}
	
	public static function setForm($id, $file)
	{
		$form = JForm::getInstance('mod_flxmlforms.form_' . $id, __DIR__ .'/forms/' . $file . '.xml', array('control' => 'jform'));

		$form->addFieldPath('modules/mod_flxmlforms/fields');
		$form->addRulePath('modules/mod_flxmlforms/rules');

		$form->label = JText::_($form->getAttribute('header', 'MOD_FLXMLFORMS_HEADER_DEFAULT'));
		$form->submit  = JText::_($form->getAttribute('submit', 'JSUBMIT'));
		$form->cancel  = JText::_($form->getAttribute('cancel', 'JCANCEL'));
		return $form;
	}
	
	public static function setJSStrings()
	{
		JText::script('ERROR');
		JText::script('MESSAGE');
		JText::script('NOTICE');
		JText::script('WARNING');
		
		JText::script('MOD_FLXMLFORMS_MESSAGE_WARNING');
		JText::script('MOD_FLXMLFORMS_MESSAGE_ERROR_UNKNOWN');
		JText::script('MOD_FLXMLFORMS_MESSAGE_ERROR_FAIL');
	}
	
	protected static function addJoomlaText($strings)
	{
		$result = array();
		
		if(count($strings))
		{
			$lang = JFactory::getLanguage();
			
			foreach($strings as $key => $string)
			{
				if ($string)
				{
					$result[strtoupper($key)] = $lang->_($string, false, true);
				}
			}
			JHtml::_('behavior.core');
			
			JFactory::getDocument()->addScriptOptions('joomla.jtext', $result, false);
		}
	}
}
