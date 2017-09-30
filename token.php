<?php
class Token
{
	public $type = TOK_EOF;
	public $literal;
	public $line = 0;

	public function __construct($type, $literal, $line)
	{
		$this->type = $type;
		$this->literal = $literal;
		$this->line = $line;
	}

	public function __toString()
	{
		$s = "{$this->type}";
		if ($this->literal != null)
			$s .= " ({$this->literal})";
		return $s;
	}
}
