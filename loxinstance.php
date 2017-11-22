<?php
class LoxInstance
{
	private $klass;
	private $fields = [];

	public function __construct(LoxClass $klass)
	{
		$this->klass = $klass;
	}

	public function get(Token $name)
	{
		if (isset($this->fields[$name->literal]))
		{
			return $this->fields[$name->literal];
		}

		$method = $this->klass->findMethod($this, $name->literal);
		if ($method !== null)
		{
			return $method;
		}

		throw new RuntimeError($name, "Undefined property '" . $name->literal . "'.");
	}

	public function set(Token $name, $value)
	{
		$this->fields[$name->literal] = $value;
	}

	public function __toString()
	{
		return $this->klass . " instance";
	}
}
