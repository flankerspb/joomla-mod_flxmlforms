<?php
defined('_JEXEC') or die;

// Set defaults
$isHTML = true;

// Set Headers
// $copy_headers = array(
	// 'Precedence' => 'bulk',
	// 'List-Id' => 'pppp',
	// 'List-Owner' => '<mailto:>',
	// 'List-Unsubscribe' => '<>'
// );

$lang = JFactory::getDocument()->language;
$page = ModflxmlformsСontrollerBase::getPage();
$site = ModflxmlformsСontrollerBase::getSite();

$date = JHtml::date('now', 'DATE_FORMAT_LC1');
$datetime = JHtml::date('now', 'DATE_FORMAT_LC2');

// Content
$subject = $data['subject'] ? JText::_($data['subject']) : JText::sprintf('MOD_FLXMLFORMS_MESSAGE_SUBJECT_DEFAULT', $site->host);

ob_start();
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $charset; ?>">
	<title><?php echo $subject; ?></title>
	<style>
	</style>
</head>
	<body>
	<?php if($data['message']) : ?>
	<h3><?php echo JText::_('MOD_FLXMLFORMS_MESSAGE'); ?></h3>
	<div style="border-left:3px solid #ddd; margin:10px; padding-left:10px;"><?php echo ModflxmlformsСontrollerBase::getHTML($data['message']); ?></div>
	<h3><?php echo JText::_('MOD_FLXMLFORMS_FROM'); ?></h3>
	<?php else : ?>
	<h3><?php echo JText::_('MOD_FLXMLFORMS_MESSAGE_FROM'); ?>:</h3>
	<?php endif; ?>
	<?php if($data['name']) : ?>
	<p><?php echo JText::sprintf('MOD_FLXMLFORMS_MESSAGE_NAME', ModflxmlformsСontrollerBase::getFullname($data['name'])); ?></p>
	<?php endif; ?>
	<?php if($data['email']) : ?>
	<p><?php echo JText::sprintf('MOD_FLXMLFORMS_MESSAGE_EMAIL', ModflxmlformsСontrollerBase::getEmail($data['email'])); ?></p>
	<?php endif; ?>
	<?php if($data['phone']) : ?>
	<p><?php echo JText::sprintf('MOD_FLXMLFORMS_MESSAGE_PHONE', ModflxmlformsСontrollerBase::getPhone($data['phone'])); ?></p>
	<?php endif; ?>
	<p>_____</p>
	<p><?php echo JText::sprintf('MOD_FLXMLFORMS_MESSAGE_HTML_SENT', $datetime, $page->link); ?></p>
	</body>
</html>
<?php
$body = ob_get_clean();

ob_start();
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $charset; ?>">
	<title><?php echo $subject; ?></title>
	<style>
	</style>
</head>
	<body>
	<p><?php echo JText::sprintf('MOD_FLXMLFORMS_MESSAGE_COPY', $site->host); ?></p>
	<div style="border-left:1px solid #555; margin:10px; padding-left:10px;">
		<?php if($data['message']) : ?>
		<h3><?php echo JText::_('MOD_FLXMLFORMS_MESSAGE'); ?></h3>
		<div style="border-left:3px solid #ddd; margin:10px; padding-left:10px;"><?php echo ModflxmlformsСontrollerBase::getHTML($data['message']); ?></div>
		<h3><?php echo JText::_('MOD_FLXMLFORMS_FROM'); ?></h3>
		<?php else : ?>
		<h3><?php echo JText::_('MOD_FLXMLFORMS_MESSAGE_FROM'); ?>:</h3>
		<?php endif; ?>
		<?php if($data['name']) : ?>
		<p><?php echo JText::sprintf('MOD_FLXMLFORMS_MESSAGE_NAME', ModflxmlformsСontrollerBase::getFullname($data['name'])); ?></p>
		<?php endif; ?>
		<?php if($data['email']) : ?>
		<p><?php echo JText::sprintf('MOD_FLXMLFORMS_MESSAGE_EMAIL', ModflxmlformsСontrollerBase::getEmail($data['email'])); ?></p>
		<?php endif; ?>
		<?php if($data['phone']) : ?>
		<p><?php echo JText::sprintf('MOD_FLXMLFORMS_MESSAGE_PHONE', ModflxmlformsСontrollerBase::getPhone($data['phone'])); ?></p>
		<?php endif; ?>
		<p>_____</p>
		<p><?php echo JText::sprintf('MOD_FLXMLFORMS_MESSAGE_HTML_SENT', $datetime, $page->link); ?></p>
	</div>
		<p>_____</p>
		<p><?php echo JText::_('MOD_FLXMLFORMS_MESSAGE_REGARDS'); ?><br><?php echo $site->link; ?></p>
	</body>
</html>
<?php
$copy_body = ob_get_clean();


$alt_body = '';
if($data['message'])
{
	$alt_body .= PHP_EOL . JText::_('MOD_FLXMLFORMS_MESSAGE') . PHP_EOL;
	$alt_body .= ModflxmlformsСontrollerBase::getPlain($data['message']) . PHP_EOL;
	$alt_body .= PHP_EOL . JText::_('MOD_FLXMLFORMS_FROM');
}
else
{
	$alt_body .= PHP_EOL . JText::_('MOD_FLXMLFORMS_MESSAGE_FROM');
}
if($data['name'])
{
	$alt_body .= PHP_EOL . JText::sprintf('MOD_FLXMLFORMS_MESSAGE_NAME', ModflxmlformsСontrollerBase::getFullname($data['name']));
}
if($data['email'])
{
	$alt_body .= PHP_EOL . JText::sprintf('MOD_FLXMLFORMS_MESSAGE_EMAIL', $data['email']);
}
if($data['phone'])
{
	$alt_body .= PHP_EOL . JText::sprintf('MOD_FLXMLFORMS_MESSAGE_PHONE', $data['phone']);
}

$alt_body .= PHP_EOL . PHP_EOL . '>' . JText::sprintf('MOD_FLXMLFORMS_MESSAGE_PLAIN_SENT', $datetime, $page->short, $page->url) . PHP_EOL;



$copy_alt_body = PHP_EOL . JText::sprintf('MOD_FLXMLFORMS_MESSAGE_COPY', $site->host) . PHP_EOL;
$copy_alt_body .= '--------' . PHP_EOL;
$copy_alt_body .= $body;
$copy_alt_body .= PHP_EOL . '--------' . PHP_EOL;
$copy_alt_body .= PHP_EOL . JText::_('MOD_FLXMLFORMS_MESSAGE_REGARDS');
$copy_alt_body .= PHP_EOL . $site->name . ' - ' . $site->url;



if($data['name'])
{
	$subject .= JText::sprintf('MOD_FLXMLFORMS_MESSAGE_SUBJECT_POSTFIX', ModflxmlformsСontrollerBase::getShortname($data['name']), $date);
}
else
{
	$subject .= JText::sprintf('MOD_FLXMLFORMS_MESSAGE_SUBJECT_DATE', $datetime);
}
