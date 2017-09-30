<?php
interface LoxCallable
{
	function arity();
	function call(Interpreter $interpreter, array $arguments);
}
