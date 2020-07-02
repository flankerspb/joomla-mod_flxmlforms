<?php
defined( '_JEXEC' ) or die;

jimport('joomla.form.formfield');

class JFormFieldLoadlang extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 */
	protected $type = 'loadlang';
	/**
	 * The allowable extensions whose language file will be loaded.
	 *
	 * @var    string
	 */
	protected $extensions = array();
	/**
	 * Path to language file. Should be "site" or "admin". Defaults to JPATH_BASE. [optional].
	 *
	 * @var    string
	 */
	protected $path = JPATH_BASE;
	/**
	 * This is the locale string. Language files for this locale will be loaded. Defaults to the one set in backend. [optional].
	 *
	 * @var    string
	 */
	protected $lang = null;
	/**
	 * Flag that will force a language to be reloaded if set to true. Default [optional].
	 *
	 * @var    boolean
	 */
	// protected $reload = false;
	
	
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
			case 'extensions':
			case 'path':
			case 'lang':
			// case 'reload':
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
			case 'extensions':
				$extensions = (string) $value;
				$this->extensions = array_map('trim', explode($extensions));
				break;

			case 'path':
				$this->path = (string) $value;
				break;

			case 'lang':
				$this->lang = (string) $value;
				break;
				
			// case 'reload':
				// $this->reload = (string) $value;
				//$this->reload = ($value === 'true' || $value === 'on' || $value === '1');
				// break;

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
		
		$result = parent::setup($element, $value, $group);
		
		if ($result == true)
		{
			$extensions = (string) $this->element['extensions'];
			if($extensions)
				$this->extensions = array_map('trim', explode(',', $extensions));
			
			$path = (string) $this->element['path'];
			
			switch ($path)
			{
				case 'admin':
					$this->path = JPATH_ADMINISTRATOR;
					break;

				case 'site':
					$this->path = JPATH_SITE;
					break;

				default:
					$this->path = JPATH_BASE;
			}
			
			$lang = (string) $this->element['lang'];
			
			if (JFactory::getLanguage()->exists($lang, $this->path))
				$this->lang = $lang;
			
		}
		return $result;
	}
	
	protected function getInput()
	{
		return;
	}
	
	protected function getLabel()
	{
		return;
	}
	
	public function renderField($options = array())
	{
		$extensions = $this->extensions;
		$path = $this->path;
		$lang = $this->lang;

		if ($extensions)
		{
			foreach ($extensions as $extension)
			{
				JFactory::getLanguage()->load($extension, $path, $lang);
			}
		}
		return;
	}
}
