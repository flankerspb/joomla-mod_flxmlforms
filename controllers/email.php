<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_flxmlforms
 *
 * @copyright   Copyright (C) 2017 - 2018 Vitaliy Moskalyuk. All rights reserved.
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;


class ModflxmlformsСontrollerEmail extends ModflxmlformsСontrollerBase
{
	public static function send($data, $params)
	{
		// return true;
		
		$recipients = static::getRecipients($params);
		
		if(!count($recipients))
		{
			return new InvalidArgumentException(JText::_('no Recipients'), 404);
		}
		
		$app = JFactory::getApplication();
		
		// Get controller options
		$controller = $params->controller;
		$options = $params->options->$controller;
		
		// Set defaults
		$encoding = '8bit';
		$charset = 'UTF-8';
		$isHTML = false;
		$headers = array();
		$copy_headers = array();
		
		// Get Mailer
		$mailer = JFactory::getMailer();
		
		// Content
		$subject = '';
		$body = '';
		$alt_body = '';
		$copy_subject = ''; //used $subject if empty
		$copy_body = ''; //used $body if empty
		$copy_alt_body = ''; //used $alt_body if empty
		
		// Include template: set content, headers and change defaults
		require(__DIR__ . '/email/' . $options->tmpl . '.php');
		
		// Mailer Options
		$mailer->Encoding = $encoding;
		$mailer->CharSet = $charset;
		$mailer->isHTML($isHTML);
		
		// Set Content
		$mailer->setSubject($subject);
		$mailer->Body = $body;
		if($isHTML & $alt_body)
		{
			$mailer->AltBody = $alt_body;
		}
		
		// Set Headers
		if(is_array($headers) && count($headers))
		{
			foreach($headers as $name => $value)
			{
				$mailer->AddCustomHeader($name, $value);
			}
		}
		
		// Set Sender & From
		$mailer->setFrom($app->get('mailfrom', ''), $app->get('fromname', ''));
		
		// Set To
		$to = $recipients[$options->to];
		$mailer->addAddress($to->email, $to->name);
		
		// Set ReplyTo User
		if($data['email'])
		{
			$mailer->addReplyTo($data['email'], $data['name']);
		}
		
		// Set CC
		if(is_array($options->cc))
		{
			foreach($options->cc as $item)
			{
				$cc = $recipients[$item];
				$mailer->addCC($cc->email, $cc->name);
			}
		}
		
		// Set BCC
		if(is_array($options->bcc))
		{
			foreach($options->bcc as $item)
			{
				$bcc = $recipients[$item];
				$mailer->addBCC($bcc->email, $bcc->name);
			}
		}
		
		$sent = $mailer->Send();
		// $sent = false;
		
		switch($options->user_copy)
		{
			case '0':
				$isCopy = false;
				break;
			case '1':
				$isCopy = true;
				break;
			case '2':
				$isCopy = isset($data['copy']) ? $data['copy'] : false;
				break;
			default:
				$isCopy = false;
		}
		
		// Send copy to user is enabled and user email exists
		if ($isCopy && $data['email'])
		{
			// Get Mailer
			$mailer = JFactory::getMailer();
			
			// Mailer Options
			$mailer->Encoding = $encoding;
			$mailer->CharSet = $charset;
			$mailer->isHTML($isHTML);
			
			// Set Content
			$copy_subject = $copy_subject ? $copy_subject : $subject;
			$copy_body = $copy_body ? $copy_body : $body;
			$copy_alt_body = $copy_alt_body ? $copy_alt_body : $alt_body;
			
			$mailer->setSubject($copy_subject);
			$mailer->Body = $copy_body;
			if($isHTML & $copy_alt_body)
			{
				$mailer->AltBody = $copy_alt_body;
			}
			
			// Set Headers
			if(is_array($copy_headers) && count($copy_headers))
			{
				foreach($copy_headers as $name => $value)
				{
					$mailer->AddCustomHeader($name, $value);
				}
			}
			
			// Set Sender & From
			$mailer->setFrom($app->get('mailfrom', ''), $app->get('fromname', ''));
			
			// Set To User
			$mailer->addRecipient($data['email'], $data['name']);
			
			// Set ReplyTo Recipient
			$mailer->addReplyTo($to->email, $to->name);
			
			$sent = $mailer->Send();
			// $sent = false;
		}
		
		return $sent;
	}
}

