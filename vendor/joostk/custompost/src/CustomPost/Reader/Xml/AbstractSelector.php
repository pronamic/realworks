<?php namespace CustomPost\Reader\Xml;

use Closure;
use SimpleXMLElement;
use ReflectionMethod;
use ReflectionFunction;
use CustomPost\Fields\Field;

abstract class AbstractSelector implements XmlSelector
{
	protected $parser;

	protected $field;

	protected $multiple = false;

	public function multiple()
	{
		$this->multiple = true;

		return $this;
	}

	public function parser($parser)
	{
		$this->parser = $parser;

		return $this;
	}

	public function setField(Field $field)
	{
		$this->field = $field;

		return $this;
	}

	// PHP <= 5.3.8: Fatal error: Can't inherit abstract function
	// CustomPost\Reader\XmlSelector::perform() (previously
	// declared abstract in CustomPost\Reader\Xml\AbstractSelector)
	// abstract public function perform(SimpleXMLElement $element, AbstractReader $reader);
}
