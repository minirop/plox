<?php
class Environment
{
	private $values = [];
	private $enclosing = null;

	public function __construct(Environment $enclosing = null)
	{
		$this->enclosing = $enclosing;
	}

	public function define($name, $value)
	{
		$this->values[$name] = $value;
	}

	public function get(Token $name)
	{
		if (array_key_exists($name->literal, $this->values))
		{
			return $this->values[$name->literal];
		}

		if ($this->enclosing != null)
		{
			return $this->enclosing->get($name);
		}

		throw new ParseError("Undef ".$name->literal);
	}

	public function assign(Token $name, $value)
	{
		if (array_key_exists($name->literal, $this->values))
		{
			$this->values[$name->literal] = $value;
			return;
		}

		if ($this->enclosing != null)
		{
			$this->enclosing->assign($name, $value);
			return;
		}

		throw new ParseError("Undef ".$name->literal);
	}
}
