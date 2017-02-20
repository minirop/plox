<?php
define('TOK_LEFT_PAREN',    'TOK_LEFT_PAREN');
define('TOK_RIGHT_PAREN',   'TOK_RIGHT_PAREN');
define('TOK_LEFT_BRACE',    'TOK_LEFT_BRACE');
define('TOK_RIGHT_BRACE',   'TOK_RIGHT_BRACE');
define('TOK_COMMA',         'TOK_COMMA');
define('TOK_DOT',           'TOK_DOT');
define('TOK_MINUS',         'TOK_MINUS');
define('TOK_PLUS',          'TOK_PLUS');
define('TOK_SEMICOLON',     'TOK_SEMICOLON');
define('TOK_SLASH',         'TOK_SLASH');
define('TOK_STAR',          'TOK_STAR');
define('TOK_BANG',          'TOK_BANG');
define('TOK_BANG_EQUAL',    'TOK_BANG_EQUAL');
define('TOK_EQUAL',         'TOK_EQUAL');
define('TOK_EQUAL_EQUAL',   'TOK_EQUAL_EQUAL');
define('TOK_GREATER',       'TOK_GREATER');
define('TOK_GREATER_EQUAL', 'TOK_GREATER_EQUAL');
define('TOK_LESS',          'TOK_LESS');
define('TOK_LESS_EQUAL',    'TOK_LESS_EQUAL');
define('TOK_IDENTIFIER',    'TOK_IDENTIFIER');
define('TOK_STRING',        'TOK_STRING');
define('TOK_NUMBER',        'TOK_NUMBER');
define('TOK_AND',           'TOK_AND');
define('TOK_CLASS',         'TOK_CLASS');
define('TOK_ELSE',          'TOK_ELSE');
define('TOK_FALSE',         'TOK_FALSE');
define('TOK_FUN',           'TOK_FUN');
define('TOK_FOR',           'TOK_FOR');
define('TOK_IF',            'TOK_IF');
define('TOK_NIL',           'TOK_NIL');
define('TOK_OR',            'TOK_OR');
define('TOK_PRINT',         'TOK_PRINT');
define('TOK_RETURN',        'TOK_RETURN');
define('TOK_SUPER',         'TOK_SUPER');
define('TOK_THIS',          'TOK_THIS');
define('TOK_TRUE',          'TOK_TRUE');
define('TOK_VAR',           'TOK_VAR');
define('TOK_WHILE',         'TOK_WHILE');
define('TOK_EOF',           'TOK_EOF');

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
