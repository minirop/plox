<?php
namespace Std;

class Clock implements \LoxCallable
{
	public function arity()
	{
		return 0;
	}

	public function call(\Interpreter $interpreter, array $arguments)
	{
		return time();
	}
}
