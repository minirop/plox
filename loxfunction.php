<?php
class LoxFunction implements LoxCallable
{
	private $declaration;
	private $closure;
	private $isInitializer;

	public function __construct(FunctionStmt $declaration, Environment $closure, bool $isInitializer)
	{
		$this->declaration = $declaration;
		$this->closure = $closure;
		$this->isInitializer = $isInitializer;
	}

	public function bind(LoxInstance $instance)
	{
		$environment = new Environment($this->closure);
		$environment->define('this', $instance);
		return new LoxFunction($this->declaration, $environment, $this->isInitializer);
	}

	public function arity()
	{
		return count($this->declaration->parameters);
	}

	public function call(Interpreter $interpreter, array $arguments)
	{
		$environment = new Environment($this->closure);

		$i = 0;
		foreach ($this->declaration->parameters as $parameter)
		{
			$environment->define($parameter->literal, $arguments[$i]);
			$i++;
		}

		try {
			$interpreter->executeBlock($this->declaration->body, $environment);
		}
		catch (ReturnException $returnValue)
		{
			return $returnValue->value;
		}

		if ($this->isInitializer) return $this->closure->getAt(0, 'this');

		return null;
	}

	public function __toString()
	{
		return "<fn ".$this->declaration->name->literal.">";
	}
}
