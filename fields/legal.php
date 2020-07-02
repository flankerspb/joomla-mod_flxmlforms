<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2018 Vitaliy Moskalyuk. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Form Field class for the Joomla Platform.
 * Provides spacer markup to be used in form layouts.
 *
 * @since  1.7.0
 */
class JFormFieldCheckboxExtended extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  1.7.0
	 */
	protected $type = 'Checkboxextended';
	
	protected $checked = false;
	
	protected $article = null;
	
	public function __get($name)
	{
		switch ($name)
		{
			case 'checked':
				return $this->checked;
		}

		return parent::__get($name);
	}
	
	public function __set($name, $value)
	{
		switch ($name)
		{
			case 'checked':
				$value = (string) $value;
				$this->checked = ($value == 'true' || $value == $name || $value == '1');
				break;

			default:
				parent::__set($name, $value);
		}
	}
	
	
	public function setup(SimpleXMLElement $element, $value, $group = null)
	{
		
		// var_dump($element->attributes());
		if(!parent::setup($element, $value, $group))
		{
			return false;
		}
		
		$attribs = array('checked', 'groupClass', 'article');
		
		foreach($attribs as $k => $value)
		{
			$this->__set($value, $element[$value]);
		}
		
	}
	
	
	protected function getInput()
	{
		// Initialize some field attributes.
		$name = $this->fieldname;
		
		$class     = !empty($this->class) ? ' class="' . $this->class . '"' : '';
		$disabled  = $this->disabled ? ' disabled' : '';
		$value     = !empty($this->default) ? htmlspecialchars($this->default, ENT_COMPAT, 'UTF-8') : '1';
		$required  = $this->required ? ' required aria-required="true"' : '';
		$autofocus = $this->autofocus ? ' autofocus' : '';
		$checked   = $this->checked ? ' checked' : '';
		
		// Initialize JavaScript field attributes.
		$onclick  = !empty($this->onclick) ? ' onclick="' . $this->onclick . '"' : '';
		$onchange = !empty($this->onchange) ? ' onchange="' . $this->onchange . '"' : '';
		
		$html = array();
		
		
		
		$html[] = '<input type="checkbox" id="'.$name.'" name="'.$name.'" class="' . $this->class . '"'.$required.'>';
		
		
		return '<input type="checkbox" name="' . $name . '" id="' . $name . '" value="'
			. $value . '"' . $class . $checked . $disabled . $onclick . $onchange
			. $required . $autofocus . ' />';
	}
	
	protected function getTitle()
	{
		return $this->getLabel();
	}
	
		protected function getLabel()
	{
		$for = $this->fieldname;
		$id = $for . '-lbl';
		
		$required = $this->required ? ' required' : '';
		
		$title = 'LEGAL';
		
		$html = array();
		
		$html[] = ' <span class="control-label">';
		
		$html[] = '<label id="'.$id.'" class="' . $this->class . '" for="' . $for . '"'.$required.'">'.$title.'</label>';
		
		$html[] = '</span>';
		
		
		return implode('', $html);
	}



	public function renderField($options = array())
	{
		
		
		
		
		$article = $this->getArticle();
		
		
		
		var_dump($this);
		var_dump($options);
		
		$options['class'] = empty($options['class']) ? 'field-spacer' : $options['class'] . ' field-spacer';
		
		
		
		
		
		$html[] = '<div class="control-group">';
		
		$html[] = '<span class="controls">';
		
		
		
		
		
		
		$html[] = '</span>';
		
		
		$html[] = $this->getLabel();
		
		
		
		// $html[] = '<style>.'.$id.':after {content: "aaaaaaaa<a href>aaaaaa</a>aaaaaaaaaaa"}    </style>';
		
		
		$html[] = '</div>';
		
		return implode('', $html);
	}
	
	protected function getArticle()
	{
		static $articles = array();
		
		if($this->article)
		{
			if(isset($articles[$this->article]))
			{
				return $articles[$this->article];
			}
		}
		else
		{
			return null;
		}
		
		// Get database
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		
		$query->select('id, catid, title, alias');
		$query->from('#__content');
		$query->where('id=' . $this->article);
		$query->where('state=1');
		
		$db->setQuery($query);
		
		$article = $db->loadObject();
		
		if(!$article)
		{
			$articles[$this->article] = null;
			
			return null;
		}
		
		$article->url = JRoute::_('index.php?option=com_content&view=article&id='.$article->id.'&catid='.$article->catid);
		
		$articles[$this->article] = $article;
		
		return $article;
	}
	
}
