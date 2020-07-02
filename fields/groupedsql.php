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
 * Supports a custom SQL select list
 *
 * @since  1.7.0
 */
class JFormFieldGroupedSQL extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  1.7.0
	 */
	protected $type = 'GroupedSQL';

	/**
	 * The value_field.
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $value_field = 'value';

	/**
	 * The title_field.
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $title_field = 'title';

	/**
	 * The translate.
	 *
	 * @var    boolean
	 * @since  3.2
	 */
	protected $translate = false;

	/**
	 * The items from querise.
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $items = array();

	/**
	 * Method to get certain otherwise inaccessible properties from the form field object.
	 *
	 * @param   string  $name  The property name for which to get the value.
	 *
	 * @return  mixed  The property value or null.
	 *
	 * @since   3.2
	 */
	public function __get($name)
	{
		switch ($name)
		{
			case 'value_field':
			case 'title_field':
			case 'translate':
			case 'items':
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
	 * @since   3.2
	 */
	public function __set($name, $value)
	{
		switch ($name)
		{
			case 'items':
				$this->$name = $value;
				break;
				
			case 'value_field':
				$this->$name = (string)$value ? (string)$value : 'value';
				break;
				
			case 'title_field':
				$this->$name = (string)$value ? (string)$value : 'title';
				break;
				
			case 'translate':
				$this->$name = ($value == 'true' || $value == 'on' || $value == '1');
				break;

			default:
				parent::__set($name, $value);
		}
	}

	/**
	 * Method to attach a JForm object to the field.
	 *
	 * @param   SimpleXMLElement  $element  The SimpleXMLElement object representing the `<field>` tag for the form field object.
	 * @param   mixed             $value    The form field value to validate.
	 * @param   string            $group    The field name group control value. This acts as an array container for the field.
	 *                                      For example if the field has name="foo" and the group value is set to "bar" then the
	 *                                      full field name would end up being "bar[foo]".
	 *
	 * @return  boolean  True on success.
	 *
	 * @see     JFormField::setup()
	 * @since   3.2
	 */
	public function setup(SimpleXMLElement $element, $value, $group = null)
	{
		
		// var_dump($element->attributes());
		if(!parent::setup($element, $value, $group))
		{
			return false;
		}
		
		foreach($this->element->attributes() as $attribute => $value)
		{
			$key = explode('_', $attribute);
			
			if($key[0] == 'query' && isset($key[1]))
			{
				// Get the database object.
				$db = JFactory::getDbo();

				// Set the query and get the result list.
				$db->setQuery($value);

				try
				{
					$this->items[$key[1]] = $db->loadObjectlist();
				}
				catch (JDatabaseExceptionExecuting $e)
				{
					JFactory::getApplication()->enqueueMessage(JText::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
				}
			}
			else
			{
				$this->__set($attribute, $value);
			}
		}
		
		return true;
	}
	
	
	protected function getGroups()
	{
		$label = 1;
		
		$value = $this->value_field;
		$title = $this->title_field;
		
		foreach ($this->element->children() as $element)
		{
			switch ($element->getName())
			{
				// The element is an <option />
				case 'option':
					// Initialize the group if necessary.
					if (!isset($groups[$label]))
					{
						$groups[$label] = array();
					}

					$disabled = (string) $element['disabled'];
					$disabled = ($disabled == 'true' || $disabled == 'disabled' || $disabled == '1');

					// Create a new option object based on the <option /> element.
					$tmp = JHtml::_(
						'select.option', ($element['value']) ? (string) $element['value'] : trim((string) $element),
						JText::alt(trim((string) $element), preg_replace('/[^a-zA-Z0-9_\-]/', '_', $this->fieldname)), 'value', 'text',
						$disabled
					);

					// Set some option attributes.
					$tmp->class = (string) $element['class'];

					// Set some JavaScript option attributes.
					$tmp->onclick = (string) $element['onclick'];

					// Add the option.
					$groups[$label][] = $tmp;
					break;

				// The element is a <group />
				case 'group':
					
					$q = (string)$element['query'];
					$query = is_array($this->items[$q]) ? $q : null;
					
					// Get the group label.
					if((string)$element['label'])
					{
						$groupLabel = JText::_((string)$element['label']);
					}
					else
					{
						$key = $query ? $query : $label;
						
						if($this->translate)
						{
							$string = 'FIELD_GROUPEDSQL_GROUP_' . $key;
							$baseLabel = JText::_('FIELD_GROUPEDSQL_GROUP_') . $key;
							$advansedLabel = JText::_('FIELD_GROUPEDSQL_GROUP_' . $key);
							
							if(strcasecmp($string, $advansedLabel) != 0)
							{
								$groupLabel = $advansedLabel;
							}
							else if(strcasecmp($string, $baseLabel) != 0)
							{
								$groupLabel = $baseLabel;
							}
							else
							{
								$groupLabel = 'Group: ' . $key;
							}
						}
						else
						{
							$groupLabel = 'Group: ' . $key;
						}
					}
					
					// Initialize the group if necessary.
					if(!isset($groups[$groupLabel]))
					{
						$groups[$groupLabel] = array();
					}

					// Iterate through the children and build an array of options.
					foreach ($element->children() as $option)
					{
						// Only add <option /> elements.
						if ($option->getName() != 'option')
						{
							continue;
						}

						$disabled = (string) $option['disabled'];
						$disabled = ($disabled == 'true' || $disabled == 'disabled' || $disabled == '1');

						// Create a new option object based on the <option /> element.
						$tmp = JHtml::_(
							'select.option', ($option['value']) ? (string) $option['value'] : JText::_(trim((string) $option)),
							JText::_(trim((string) $option)), 'value', 'text', $disabled
						);

						// Set some option attributes.
						$tmp->class = (string) $option['class'];

						// Set some JavaScript option attributes.
						$tmp->onclick = (string) $option['onclick'];

						// Add the option.
						$groups[$groupLabel][] = $tmp;
					}
					
					
					// хуй
					if($query)
					{
						foreach ($this->items[$query] as $item)
						{
							$disabled = (isset($item->disabled) && $item->disabled);
							
							// Create a new option object based on the <option /> element.
							$tmp = JHtml::_('select.option', (string)$item->$value, $item->$title, 'value', 'text', $disabled);

							// Add the option.
							$groups[$groupLabel][] = $tmp;
						}
						
						unset($this->items[$query]);
					}
					
					$label++;
					
					break;
				
				// Unknown element type.
				default:
					throw new UnexpectedValueException(sprintf('Unsupported element %s in JFormFieldGroupedList', $element->getName()), 500);
			}
		}
		
		foreach ($this->items as $key => $group)
		{
			if($this->translate)
			{
				$string = 'FIELD_GROUPEDSQL_GROUP_' . $key;
				$baseLabel = JText::_('FIELD_GROUPEDSQL_GROUP_') . $key;
				$advansedLabel = JText::_('FIELD_GROUPEDSQL_GROUP_' . $key);
				
				if(strcasecmp($string, $advansedLabel) != 0)
				{
					$groupLabel = $advansedLabel;
				}
				else if(strcasecmp($string, $baseLabel) != 0)
				{
					$groupLabel = $baseLabel;
				}
				else
				{
					$groupLabel = 'Group: ' . $key;
				}
			}
			else
			{
				$groupLabel = 'Group: ' . $key;
			}
			
			if (!isset($groups[$groupLabel]))
			{
				$groups[$groupLabel] = array();
			}
			
			
			foreach($group as $item)
			{
				$disabled = (isset($item->disabled) && $item->disabled);
				
				// Create a new option object based on the <option /> element.
				$tmp = JHtml::_('select.option', (string)$item->$value, $item->$title, 'value', 'text', $disabled);
				
				$groups[$groupLabel][] = $tmp;
			}
			
			$label++;
		}
		
		return $groups;
	}
	
	/**
	 * Method to get the field input markup fora grouped list.
	 * Multiselect is enabled by using the multiple attribute.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   1.7.0
	 */
	protected function getInput()
	{
		$html = array();
		$attr = '';

		// Initialize some field attributes.
		$attr .= !empty($this->class) ? ' class="' . $this->class . '"' : '';
		$attr .= !empty($this->size) ? ' size="' . $this->size . '"' : '';
		$attr .= $this->multiple ? ' multiple' : '';
		$attr .= $this->required ? ' required aria-required="true"' : '';
		$attr .= $this->autofocus ? ' autofocus' : '';

		// To avoid user's confusion, readonly="true" should imply disabled="true".
		if ($this->readonly || $this->disabled)
		{
			$attr .= ' disabled="disabled"';
		}

		// Initialize JavaScript field attributes.
		$attr .= !empty($this->onchange) ? ' onchange="' . $this->onchange . '"' : '';

		// Get the field groups.
		$groups = (array) $this->getGroups();

		// Create a read-only list (no name) with a hidden input to store the value.
		if ($this->readonly)
		{
			$html[] = JHtml::_(
				'select.groupedlist', $groups, null,
				array(
					'list.attr' => $attr, 'id' => $this->id, 'list.select' => $this->value, 'group.items' => null, 'option.key.toHtml' => false,
					'option.text.toHtml' => false,
				)
			);

			// E.g. form field type tag sends $this->value as array
			if ($this->multiple && is_array($this->value))
			{
				if (!count($this->value))
				{
					$this->value[] = '';
				}

				foreach ($this->value as $value)
				{
					$html[] = '<input type="hidden" name="' . $this->name . '" value="' . htmlspecialchars($value, ENT_COMPAT, 'UTF-8') . '"/>';
				}
			}
			else
			{
				$html[] = '<input type="hidden" name="' . $this->name . '" value="' . htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') . '"/>';
			}
		}

		// Create a regular list.
		else
		{
			$html[] = JHtml::_(
				'select.groupedlist', $groups, $this->name,
				array(
					'list.attr' => $attr, 'id' => $this->id, 'list.select' => $this->value, 'group.items' => null, 'option.key.toHtml' => false,
					'option.text.toHtml' => false,
				)
			);
		}
		
		return implode($html);
	}
}
