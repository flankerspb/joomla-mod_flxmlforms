<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_flxmlforms
 *
 * @copyright   Copyright (C) 2017 - 2018 Vitaliy Moskalyuk. All rights reserved.
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Controller for single event view
 *
 * @since  1.5.19
 */
abstract class ModflxmlformsСontrollerBase
{
	abstract public static function send($data, $params);
	
	public static function getName($name)
	{
		if(is_array($name))
		{
			return trim($name['firstname']);
		}
		return trim($name);
	}
	
	public static function getFullname($name)
	{
		if(is_array($name))
		{
			return implode(' ', array_map('trim', $name));
		}
		return trim($name);
	}
	
	public static function getShortname($name)
	{
		if(is_array($name))
		{
			$parts = array();
			
			if(array_key_exists('prefix', $name))
			{
				$parts[] = trim($name['prefix']);
				unset($name['prefix']);
			}
			if(array_key_exists('lastname', $name))
			{
				$parts[] = trim($name['lastname']);
				unset($name['lastname']);
			}
			foreach($name as $part)
			{
				$parts[] = trim($part)[0] . '.';
			}
			
			return implode(' ', $parts);
		}
		
		return trim($name);
	}
	
	public static function getHTML($message, $translate = true, $level = 0)
	{
		$result = '';
		
		if(is_array($message))
		{
			$isNumeric = array_keys($message) === array_keys(array_keys($message));
			
			if($isNumeric)
			{
				$result .= '<ol>';
				
				foreach($message as $key => $value)
				{
					if(is_array($value))
					{
						$result .= '<li>' . self::getHTML($value, $translate, ++$level) . '</li>';
					}
					else
					{
						$result .= '<li>' . $value . '</li>';
					}
				}
				
				$result .= '</ol>';
			}
			else
			{
				$result .= '<ul>';
				
				foreach($message as $key => $value)
				{
					if(is_array($value))
					{
						$result .= '<li>' . $key . ':<br>' . self::getHTML($value, $translate, ++$level) . '</li>';
					}
					else
					{
						$result .= '<li>' . $key . ': ' . $value . '</li>';
					}
				}
				
				$result .= '</ul>';
			}
		}
		else if(is_string($message))
		{
			$result .= '<p>' . $message . '</p>';
		}
		
		return $result;
	}
	
	public static function getPlain($message, $translate = true, $level = 0)
	{
		$result = '';
		
		$pad = str_repeat('  ', $level++);
		
		if(is_array($message))
		{
			$isNumeric = array_keys($message) === array_keys(array_keys($message));
			
			$_ = $isNumeric ? '. ' : ': ';
			
			foreach($message as $key => $value)
			{
				$k = $isNumeric ? ($key + 1) : $key;
				
				if(is_array($value))
				{
					$result .= PHP_EOL . $pad . $k . $_ . self::getPlain($value, $translate, $level);
				}
				else
				{
					$result .= PHP_EOL . $pad . $k . $_ . $value;
				}
			}
		}
		else if(is_string($message))
		{
			$result .= PHP_EOL . $pad . $message;
		}
		
		return $result;
	}
	
	public static function getPhone($phone, $type = 'tel')
	{
		if(!$phone)
		{
			return null;
		}
		$page = new stdclass();
		
		$regExp = "~[^\d\+]~";
		
		return JHTML::link($type . ':' . preg_replace($regExp,'', $phone), $phone);
	}
	
	public static function getEmail($email)
	{
		if(!$email)
		{
			return null;
		}
		
		return JHTML::link('mailto:' . $email, $email);
	}
	
	public static function getPage($short = true, $min = 5, $max = 40)
	{
		$page = new stdclass();
		
		$page->url = JStringPunycode::urlToUTF8(Juri::current());
		$page->title = $page->short = JFactory::getDocument()->title;
		
		if(mb_strlen($page->short, 'UTF-8') > $max)
		{
			$page->short = mb_substr($page->short, 0, $max, 'UTF-8') . '...';
			
			$last_space = mb_strrpos($text, ' ');
			
			if($last_space >= $min)
			{
				$page->short = mb_substr($text, 0, $last_space, 'UTF-8') . '...';
			}
		}
		
		$title = $short ? $page->short : $page->title;
		
		$page->link = JHTML::link($page->url, $title, 'target="_blank"');
		
		return $page;
	}
	
	public static function getSite()
	{
		$site = new stdclass();
		
		$site->url = JStringPunycode::urlToUTF8(Juri::root());
		$site->host = JStringPunycode::urlToUTF8($_SERVER['HTTP_HOST']);
		$site->name = JFactory::getApplication()->get('sitename');
		
		$site->link = JHTML::link($site->url, $site->name, 'target="_blank"');
		
		return $site;
	}
	
	public static function getRecipients($params)
	{
		$controller = $params->controller;
		$options = $params->options->$controller;
		$types = array('to', 'replyto', 'cc', 'bcc');
		$recipients = array();
		$result = array();
		
		foreach($types as $type)
		{
			if($options->$type)
			{
				if(is_array($options->$type))
				{
					foreach($options->$type as $value)
					{
						$recipient = explode('_', $value);
						
						if(count($recipient) == 2)
						{
							$recipients[$recipient[0]][$recipient[1]] = $recipient[1];
						}
					}
				}
				else if(is_string($options->$type))
				{
					$recipient = explode('_', $options->$type);
					
					if(count($recipient) == 2)
					{
						$recipients[$recipient[0]][$recipient[1]] = $recipient[1];
					}
				}
			}
		}
		
		foreach($recipients as $type => $ids)
		{
			$method = 'get' . $type;
			
			$result += static::$method($ids);
		}
		
		return $result;
	}
	
	protected static function getContacts($ids)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query
			->select('id, name, email_to AS email')
			->from('#__contact_details')
			->where('id IN (' . implode(',', $ids) . ')')
			->where('!(email_to = "")');

		$db->setQuery($query);

		try
		{
			$list = $db->loadObjectList('id');
		}
		catch (RuntimeException $e)
		{
			// new InvalidArgumentException(JText::_('no contacts'), 404);
			return array();
		}
		
		$result = array();
		
		foreach($list as $id => $item)
		{
			$result['contacts_' . $id] = $item;
		}
		
		return $result;
	}
	
	protected static function getUsers($ids)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query
			->select('id, username AS name, email')
			->from('#__users')
			->where('id IN (' . implode(',', $ids) . ')')
			->where('block=0')
			->where('!(email="")');

		$db->setQuery($query);

		try
		{
			$list = $db->loadObjectList('id');
		}
		catch (RuntimeException $e)
		{
			// new InvalidArgumentException(JText::_('no users'), 404);
			return array();
		}
		
		$result = array();
		
		foreach($list as $id => $item)
		{
			$result['users_' . $id] = $item;
		}
		
		return $result;
	}
	
	protected static function getSiteContacts($ids)
	{
		$app = JFactory::getApplication();
		$result = array();
		
		foreach($ids as $id)
		{
			$item = new stdclass();
			$item->id = $id;
			
			switch ($id)
			{
				case 'mailfrom':
					$item->name = $app->get('fromname');
					$item->email = $app->get('mailfrom');
					break;
				case 'replyto':
					$item->name = $app->get('replytoname');
					$item->email = $app->get('replyto');
					break;
			}
			
			if($item->email)
			{
				$result['sitecontacts_' . $id] = $item;
			}
		}
		
		return $result;
	}
}
