<?php
class LoxClass implements LoxCallable
{
	private $name;
	private $superclass;
	private $methods;

	public function __construct($name, $superclass, array $methods)
	{
		$this->name = $name;
		$this->superclass = $superclass;
		$this->methods = $methods;
	}

	public function findMethod(LoxInstance $instance, $name)
	{
		if (isset($this->methods[$name]))
		{
			return $this->methods[$name]->bind($instance);
		}

		if ($this->superclass != null)
		{
			return $this->superclass->findMethod($instance, $name);
		}

		return null;
	}

	public function call(Interpreter $interpreter, array $arguments)
	{
		$instance = new LoxInstance($this);

		if (isset($this->methods["init"]))
		{
			$this->methods["init"]->bind($instance)->call($interpreter, $arguments);
		}

		return $instance;
	}

	public function arity()
	{
		if (isset($this->methods["init"]))
		{
			return $this->methods["init"]->arity();
		}
		return 0;
	}

	public function __toString()
	{
		return $this->name;
	}
}
