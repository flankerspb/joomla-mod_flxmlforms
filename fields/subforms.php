<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2018 Vitaliy Moskalyuk. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

jimport('joomla.filesystem.path');

/**
 * The Field to load the forms inside current form
 *
 * @Example with all attributes:
 * <field
 *   name="field_name"
 *   type="subforms"
 *   directory="path/to/xml_forms"
 *   switch="another_field_name"
 *   prefix="prefix_"
 *   min="1"
 *   max="3"
 *   multiple="true"
 *   buttons="add,remove,move"
 *   layout="joomla.form.field.subform.repeatable-table"
 *   groupByFieldset="false"
 *   component="com_example"
 *   client="site"
 * />
 */
class JFormFieldSubforms extends JFormField
{
	/**
	 * The form field type.
	 * @var    string
	 */
	protected $type = 'Subforms';

	/**
	 * Forms directory source
	 * @var string
	 */
	protected $directory;

	/**
	 * Form field to switch subforms
	 * @var string
	 */
	protected $switch;

	/**
	 * Prefix for subform group fields
	 * @var string
	 */
	protected $prefix;
	
	/**
	 * Forms files
	 * @var array
	 */
	protected $subforms = array();

	/**
	 * Minimum items in repeat mode
	 * @var int
	 */
	protected $min = 0;

	/**
	 * Maximum items in repeat mode
	 * @var int
	 */
	protected $max = 1000;

	/**
	 * Whether group subform fields by it`s fieldset
	 * @var boolean
	 */
	protected $groupByFieldset = false;

	/**
	 * Which buttons to show in miltiple mode
	 * @var array $buttons
	 */
	protected $buttons = array('add' => true, 'remove' => true, 'move' => true);

	/**
	 * Layout to render the form
	 * @var  string
	 */
	protected $layout = 'joomla.form.field.subform.default';

	
	public function renderField($options = array())
	{
		$options['hiddenLabel'] = true;
		
		$options['class'] = isset($options['class']) ? $options['class'] . ' fl-subforms' : 'fl-subforms';
		
		if($this->switch)
		{
			$options['showonEnabled'] = true;
		}
		
		return parent::renderField($options);
	}
	
	/**
	 * Method to get certain otherwise inaccessible properties from the form field object.
	 *
	 * @param   string  $name  The property name for which to get the value.
	 *
	 * @return  mixed  The property value or null.
	 *
	 * @since   3.6
	 */
	public function __get($name)
	{
		switch ($name)
		{
			case 'directory':
			case 'subforms':
			case 'prefix':
			case 'switch':
			case 'min':
			case 'max':
			case 'layout':
			case 'groupByFieldset':
			case 'buttons':
				return $this->$name;
		}

		return parent::__get($name);
	}

	/**
	 * Method to set certain otherwise inaccessible properties of the form field object.
	 *
	 * @param   string  $name   The property name for which to set the value.
	 * @param   mixed   $value  The value of the property.
	 *
	 * @return  void
	 *
	 * @since   3.6
	 */
	public function __set($name, $value)
	{
		switch ($name)
		{
			case 'directory':
				$directory = JPath::clean(JPATH_ROOT . '/' . JPath::clean((string) $value));
				if(is_dir($directory))
				{
					$this->directory = $directory;
					
					$files = JFolder::files($this->directory, '.xml$', false, true);
					
					foreach ($files as $file)
					{
						$name = pathinfo($file, PATHINFO_FILENAME);
						
						$this->subforms[$name] = $file;
					}
				}
				
				break;
				
			case 'switch':
				$this->switch = trim((string)$value);
				
				break;
				
			case 'prefix':
				$this->prefix = trim((string)$value);
				
				break;
				
			case 'min':
				$this->min = (int) $value;
				
				break;
				
			case 'max':
				if ($value)
				{
					$this->max = max(1, (int) $value);
				}
				
				break;
				
			case 'groupByFieldset':
				if ($value !== null)
				{
					$value = (string) $value;
					$this->groupByFieldset = !($value === 'false' || $value === 'off' || $value === '0');
				}
				break;
				
			case 'layout':
				$this->layout = (string) $value;

				// Make sure the layout is not empty.
				if (!$this->layout)
				{
					// Set default value depend from "multiple" mode
					$this->layout = !$this->multiple ? 'joomla.form.field.subform.default' : 'joomla.form.field.subform.repeatable';
				}
				
				break;
				
			case 'buttons':
			
				if (!$this->multiple)
				{
					$this->buttons = array();
					break;
				}
				
				if ($value && !is_array($value))
				{
					$value = explode(',', (string) $value);
					$value = array_fill_keys(array_filter($value), true);
				}
				
				if ($value)
				{
					$value = array_merge(array('add' => false, 'remove' => false, 'move' => false), $value);
					$this->buttons = $value;
				}
				
				break;
				
			default:
				parent::__set($name, $value);
		}
	}

	/**
	 * Method to attach a JForm object to the field.
	 *
	 * @param   SimpleXMLElement  $element  The SimpleXMLElement object representing the <field /> tag for the form field object.
	 * @param   mixed             $value    The form field value to validate.
	 * @param   string            $group    The field name group control value.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   3.6
	 */
	public function setup(SimpleXMLElement $element, $value, $group = null)
	{
		if(!parent::setup($element, $value, $group))
		{
			return false;
		}

		foreach (array('directory', 'switch', 'prefix', 'layout', 'groupByFieldset', 'buttons') as $k => $attributeName)
		{
			$this->__set($attributeName, $element[$attributeName]);
		}
		
		if (!$this->directory)
		{
			return false;
		}
		
		if ($this->value && is_string($this->value))
		{
			// Guess here is the JSON string from 'default' attribute
			$this->value = json_decode($this->value, true);
		}
		
		return true;
	}

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   3.6
	 */
	protected function getInput()
	{
		/**
		 * For each rendering process of a subform element, we want to have a
		 * separate unique subform id present to could distinguish the eventhandlers
		 * regarding adding/moving/removing rows from nested subforms from their parents.
		 */
		static $unique_subform_id = 0;
		
		$result = '';
		
		if(!$unique_subform_id)
		{
			$result .= '<style>.fl-subforms>.controls{margin-left: 0 !important;}</style>';
		}
		
		// Prepare data for renderer
		$data = parent::getLayoutData();
		$data['min']       = $this->min;
		$data['max']       = $this->max;
		$data['buttons']   = $this->buttons;
		$data['fieldname'] = $this->fieldname;
		$data['groupByFieldset'] = $this->groupByFieldset;
		
		foreach ($this->subforms as $name => $subform)
		{
			$tmpl = null;
			$forms = array();
			
			$control = $this->name . '[' . $this->prefix . $name . ']';
			
			if(isset($this->value[$name]) && $this->value[$name])
			{
				$value = (array) $this->value[$name];
			}
			else
			{
				$value = array();
			}
			
			try
			{
				// Prepare the form template
				$formname = 'subform.' . str_replace(array('jform[', '[', ']'), array('', '.', ''), $control);
				
				$tmplcontrol = !$this->multiple ? $control : $control . '[' . $this->fieldname . 'X]';
				$tmpl = JForm::getInstance($formname, $subform, array('control' => $tmplcontrol));

				// Prepare the forms for exiting values
				if ($this->multiple)
				{
					$value = array_values($value);
					$c = max($this->min, min(count($value), $this->max));
					for ($i = 0; $i < $c; $i++)
					{
						$itemcontrol = $control . '[' . $this->fieldname . $i . ']';
						$itemform    = JForm::getInstance($formname . $i, $subform, array('control' => $itemcontrol));

						if (!empty($value[$i]))
						{
							$itemform->bind($value[$i]);
						}
						
						$forms[] = $itemform;
					}
				}
				else
				{
					$tmpl->bind($value);
					$forms[] = $tmpl;
				}
			}
			catch (Exception $e)
			{
				return $e->getMessage();
			}
			
			$data['tmpl']      = $tmpl;
			$data['forms']     = $forms;
			$data['control']   = $control;
			$data['unique_subform_id'] = ('sr-' . ($unique_subform_id++));
			
			// Prepare renderer
			$renderer = $this->getRenderer($this->layout);
			
			// Allow to define some JLayout options as attribute of the element
			if ($this->element['component'])
			{
				$renderer->setComponent((string) $this->element['component']);
			}
			
			if ($this->element['client'])
			{
				$renderer->setClient((string) $this->element['client']);
			}
			
			// Render
			$html = $renderer->render($data);
			
			if($this->switch)
			{
				$switch = $this->switch . ':' . $name;
				
				$showon = ' data-showon=\'' .
				json_encode(JFormHelper::parseShowOnConditions($switch, $this->formControl, $this->group)) . '\'';
				
				$search = substr($html, 0, strpos($html, ' ', strpos($html, '<')));
				$replace = $search . $showon;
				
				$c = 1;
				
				$html = str_replace($search, $replace, $html, $c);
			}
			
			// Add hidden input on front of the subform inputs, in multiple mode
			// for allow to submit an empty value
			if ($this->multiple)
			{
				$html = '<input name="' . $this->name . '" type="hidden" value="" />' . $html;
			}
			
			$result .= $html;
		}
		
		return $result;
	}

	/**
	 * Method to get the name used for the field input tag.
	 *
	 * @param   string  $fieldName  The field element name.
	 *
	 * @return  string  The name to be used for the field input tag.
	 *
	 * @since   3.6
	 */
	protected function getName($fieldName)
	{
		$name = '';

		// If there is a form control set for the attached form add it first.
		if ($this->formControl)
		{
			$name .= $this->formControl;
		}

		// If the field is in a group add the group control to the field name.
		if ($this->group)
		{
			// If we already have a name segment add the group control as another level.
			$groups = explode('.', $this->group);

			if ($name)
			{
				foreach ($groups as $group)
				{
					$name .= '[' . $group . ']';
				}
			}
			else
			{
				$name .= array_shift($groups);

				foreach ($groups as $group)
				{
					$name .= '[' . $group . ']';
				}
			}
		}

		// If we already have a name segment add the field name as another level.
		if ($name)
		{
			$name .= '[' . $fieldName . ']';
		}
		else
		{
			$name .= $fieldName;
		}

		return $name;
	}
}
