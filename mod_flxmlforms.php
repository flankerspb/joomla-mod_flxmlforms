<?php
/**
 * @package Joomla.Site
 * @subpackage  mod_flxmlforms
 *
 * @copyright   Copyright (C) 2017 - 2018 Vitaliy Moskalyuk. All rights reserved.
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */
defined( '_JEXEC' ) or die( );

$app = JFactory::getApplication();

// Fix module Captcha
$old_captcha = $app->getParams()->get('captcha', '');
$captcha = $params->get('captcha', $app->get('captcha'));
$app->getParams()->set('captcha', $captcha);

// Set additional params
$params->offsetSet('showtitle', $module->showtitle);
$module->showtitle = '0';

if ($params->get('jquery'))
{
	$no_conflict = $params->get('no_conflict') ? true : false;
	$migrate = $params->get('migrate') ? true : false;
	
	JHtml::_('jquery.framework', $no_conflict, null, $migrate);
}

if ($params->get('keepalive'))
{
	JHtml::_('behavior.keepalive');
}

if ($params->get('validator'))
{
	JHtml::_('behavior.formvalidator');
}
else
{
	// Add validate.js language strings
	JText::script('JLIB_FORM_FIELD_INVALID');
	JText::script('JLIB_FORM_VALIDATE_FIELD_INVALID');
}

JHtml::script('media/flxmlforms/ajax.js', array(), array('async' => 'async', 'defer' => 'defer'));

if (is_array($params->get('loadlangs')))
{
	$lang = JFactory::getLanguage();
	foreach($params->get('loadlangs') as $component)
	{
		$lang->load($component, JPATH_SITE);
	}
}

JLoader::register('ModFlxmlformsHelper', __DIR__ . '/helper.php');

$form = ModFlxmlformsHelper::setJSStrings();
$form = ModFlxmlformsHelper::setForm($module->id, $params->get('form'));

$moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx'));

switch($params->get('privacy_type'))
{
	case 'menuitem' :
		$privacy_uri = JRoute::_('index.php?Itemid='. $params->get('privacy_menuitem'));
		break;
	case 'article' :
		$privacy_uri = JRoute::_(ContentHelperRoute::getArticleRoute(...explode('|', $params->get('privacy_article'))));
		break;
	default :
		$privacy_uri = Juri::root();
}

$privacy_link = JText::sprintf('MOD_FLXMLFORMS_FORM_NOTICE_DEFAULT', $privacy_uri);

require JModuleHelper::getLayoutPath('mod_flxmlforms', $params->get('layout', 'default'));

// Revert Params Captcha
$app->getParams()->set('captcha', $old_captcha);
