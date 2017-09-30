<?php
class LoxFunction implements LoxCallable
{
	private $declaration;
	private $closure;

	public function __construct(FunctionStmt $declaration, Environment $closure)
	{
		$this->declaration = $declaration;
		$this->closure = $closure;
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
	}

	public function __toString()
	{
		return "<fn ".$this->declaration->name->literal.">";
	}
}
