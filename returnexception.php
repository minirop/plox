<?php
class ReturnException extends Exception
{
	public $value;

	public function __construct($value)
	{
		parent::__construct();
		$this->value = $value;
	}
}
