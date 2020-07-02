<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2019 Vitaliy Moskalyuk. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;


class JFormFieldCheckboxExtended extends JFormField
{
	protected $type = 'Checkboxextended';
	
	protected static $generated_fieldname = 'checkbox';
	
	protected $label = '';
	
	protected $checked = false;
	
	protected $article = null;
	
	protected $groupClass = '';
	
	public function __get($name)
	{
		switch ($name)
		{
			case 'label':
				return $this->checked;
			case 'checked':
				return $this->checked;
			case 'article':
				return $this->article;
			case 'groupClass':
				return $this->groupClass;
		}

		return parent::__get($name);
	}
	
	public function __set($name, $value)
	{
		switch ($name)
		{
			case 'label':
				$this->label = (string)$value;
				break;
			case 'checked':
				$value = (string)$value;
				$this->checked = ($value == 'true' || $value == 'checked' || $value == '1');
				break;
			case 'default':
				$value = $value ? htmlspecialchars((string)$value, ENT_COMPAT, 'UTF-8') : '1';
				$this->default = $value;
				break;
			case 'article':
				$value = (string)$value;
				if($value)
				{
					$this->article = $this->getArticle($value);
				}
				break;
			
			default:
				parent::__set($name, $value);
		}
	}
	
	
	public function setForm($form)
	{
		$this->form = $form;
		return $this;
	}
	
	
	public function setup(SimpleXMLElement $element, $value, $group = null)
	{
		if(isset($element['formControl']))
		{
			$v = (string)$element['formControl'];
			
			if($v != 'false' && $v != '0')
			{
				$this->formControl = $this->form->getFormControl();
			}
		}
		else
		{
			$this->formControl = $this->form->getFormControl();
		}
		
		if(!parent::setup($element, $value, $group))
		{
			return false;
		}
		
		$attribs = array('label', 'checked', 'default', 'groupClass', 'article');
		
		foreach($attribs as $k => $value)
		{
			$this->__set($value, $element[$value]);
		}
		
		return true;
	}
	
	
	protected function getInput()
	{
		// Initialize some field attributes.
		$name = ' name="' . $this->fieldname . '"';
		$id   = ' id="' . $this->fieldname . '"';
		
		$class     = !empty($this->class) ? ' class="' . $this->class . '"' : '';
		$disabled  = $this->disabled ? ' disabled' : '';
		$value     = ' value="'. $this->default . '"';
		$required  = $this->required ? ' required aria-required="true"' : '';
		$autofocus = $this->autofocus ? ' autofocus' : '';
		$checked   = $this->checked ? ' checked' : '';
		
		// Initialize JavaScript field attributes.
		$onclick  = !empty($this->onclick) ? ' onclick="' . $this->onclick . '"' : '';
		$onchange = !empty($this->onchange) ? ' onchange="' . $this->onchange . '"' : '';
		
		return '<input type="checkbox"' . $name . $id . $value . $class . $checked . $disabled . $onclick . $onchange . $required . $autofocus . ' />';
	}
	
	protected function getTitle()
	{
		return $this->getLabel();
	}
	
	protected function getLabel()
	{
		$id  = ' id="' . $this->fieldname . '-lbl"';
		$for = ' for="' . $this->fieldname . '"';
		
		$text = '';
		
		$classes = array();
		
		if($this->labelClass)
		{
			$classes[] = $this->labelClass;
		}
		
		if($this->required)
		{
			$classes[] = 'required';
			$required = ' required';
			
			$text = '<span class="star">&#160;*</span>';
		}
		
		$class = 'class="' . trim(implode(' ', $classes)) . '"';
		
		$url = $this->article ? $this->article->url : '#';
		
		if($this->label)
		{
			$text = JText::sprintf($this->label, $url) . $text;
		}
		else
		{
			$text = $this->fieldname . $text;
		}
		
		return '<label ' . $id . $for . $class . $required .'>' . $text . '</label>';
	}
	
	
	public function renderField($options = array())
	{
		$html = array();
		
		$classes = array();
		$classes[] = 'control-group';
		
		if($this->groupClass)
		{
			$classes[] = $this->groupClass;
		}
		
		if($options['class'])
		{
			$classes[] = $options['class'];
		}
		
		$html[] = '<div class="' . trim(implode(' ', $classes)) . '">';
		
		$html[] = '<span class="controls">';
		
		$html[] = $this->getInput();
		
		$html[] = '</span>';
		
		$html[] = ' <span class="control-label">';
		
		$html[] = $this->getLabel();
		
		$html[] = '</span>';
		
		$html[] = '</div>';
		
		return implode('', $html);
	}
	
	
	protected function getFieldName($fieldname)
	{
		if ($fieldname)
		{
			return $fieldname;
		}
		else
		{
			self::$count = self::$count + 1;
			
			return self::$generated_fieldname . self::$count;
		}
	}
	
	
	protected function getArticle($id)
	{
		static $articles = array();
		
		if($id)
		{
			if(isset($articles[$id]))
			{
				return $articles[$id];
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
		$query->where('id=' . $id);
		$query->where('state=1');
		
		$db->setQuery($query);
		
		$article = $db->loadObject();
		
		if(!$article)
		{
			$articles[$this->article] = null;
			
			return null;
		}
		
		$article->url = JRoute::_('index.php?option=com_content&view=article&id='.$article->id.'&catid='.$article->catid);
		
		$articles[$id] = $article;
		
		return $article;
	}
	
}
