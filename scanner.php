<?php
require_once('token.php');
require_once('eplox.php');

class Scanner
{
	private $source;
	private $tokens = [];
	private $start = 0;
	private $current = 0;
	private $line = 1;

	private static $keywords = [
		'and'	=> TOK_AND,
		'class'	=> TOK_CLASS,
		'else'	=> TOK_ELSE,
		'false'	=> TOK_FALSE,
		'for'	=> TOK_FOR,
		'fun'	=> TOK_FUN,
		'if'	=> TOK_IF,
		'nil'	=> TOK_NIL,
		'or'	=> TOK_OR,
		'print'	=> TOK_PRINT,
		'return'=> TOK_RETURN,
		'super'	=> TOK_SUPER,
		'this'	=> TOK_THIS,
		'true'	=> TOK_TRUE,
		'var'	=> TOK_VAR,
		'while' => TOK_WHILE
	];

	public function __construct($source)
	{
		$this->source = $source;
	}

	public function scanTokens()
	{
		while (!$this->isAtEnd())
		{
			$this->start = $this->current;
			$this->scanToken();
		}

		$this->tokens[] = new Token(TOK_EOF, null, $this->line);

		return $this->tokens;
	}

	private function isAtEnd()
	{
		return $this->current >= strlen($this->source);
	}

	private function scanToken()
	{
		$c = $this->advance();
		switch ($c) {
			case '(': $this->addToken(TOK_LEFT_PAREN); break;
			case ')': $this->addToken(TOK_RIGHT_PAREN); break;
			case '{': $this->addToken(TOK_LEFT_BRACE); break;
			case '}': $this->addToken(TOK_RIGHT_BRACE); break;
			case ',': $this->addToken(TOK_COMMA); break;
			case '.': $this->addToken(TOK_DOT); break;
			case '-': $this->addToken(TOK_MINUS); break;
			case '+': $this->addToken(TOK_PLUS); break;
			case ';': $this->addToken(TOK_SEMICOLON); break;
			case '*': $this->addToken(TOK_STAR); break;

			case '!': $this->addToken($this->match('=') ? TOK_BANG_EQUAL : TOK_BANG); break;
			case '=': $this->addToken($this->match('=') ? TOK_EQUAL_EQUAL : TOK_EQUAL); break;
			case '<': $this->addToken($this->match('=') ? TOK_LESS_EQUAL : TOK_LESS); break;
			case '>': $this->addToken($this->match('=') ? TOK_GREATER_EQUAL : TOK_GREATER); break;

			case '/':
				if ($this->match('/'))
				{
					// A comment goes until the end of the line.
					while ($this->peek() != '\n' && !$this->isAtEnd()) $this->advance();
				}
				else
				{
					$this->addToken(TOK_SLASH);
				}
				break;

			case ' ':
			case "\r":
			case "\t":
				// Ignore whitespace.
				break;

			case "\n":
				$this->line++;
				break;

			case '"': $this->string(); break;

			default:
				if ($this->isDigit($c))
				{
					$this->number();
				}
				else if ($this->isAlpha($c))
				{
					$this->identifier();
				}
				else
				{
					EPlox::error($this->line, "Unexpected character '$c'.");
				}
		}
	}

	private function advance()
	{
		$this->current++;
		return $this->source[$this->current - 1];
	}

	private function addToken($type, $literal = null)
	{
		if ($literal === null) $literal = (string)$type;
		$this->tokens[] = new Token($type, $literal, $this->line);
	}

	private function match($expected) {
		if ($this->isAtEnd()) return false;
		if ($this->source[$this->current] != $expected) return false;

		$this->current++;
		return true;
	}

	private function peek() {
		if ($this->isAtEnd()) return '\0';
		return $this->source[$this->current];
	}

	private function string()
	{
		while ($this->peek() != '"' && !$this->isAtEnd())
		{
			if ($this->peek() == '\n') $this->line++;
			$this->advance();
		}

		// Unterminated string.
		if ($this->isAtEnd())
		{
			EPlox::error($this->line, "Unterminated string.");
			return;
		}

		// The closing ".
		$this->advance();

		// Trim the surrounding quotes.
		$value = substr($this->source, $this->start + 1, $this->current - $this->start - 2);
		$this->addToken(TOK_STRING, $value);
	}

	private function isDigit($c)
	{
		return $c >= '0' && $c <= '9';
	}

	private function number()
	{
		while ($this->isDigit($this->peek())) $this->advance();

		// Look for a fractional part.
		if ($this->peek() == '.' && $this->isDigit($this->peekNext()))
		{
			// Consume the "."
			$this->advance();

			while ($this->isDigit($this->peek())) $this->advance();
		}

		$this->addToken(TOK_NUMBER, floatval(substr($this->source, $this->start, $this->current - $this->start)));
	}

	private function peekNext()
	{
		if ($this->current + 1 >= strlen($this->source)) return '\0';
		return $this->source[$this->current + 1];
	}

	private function identifier()
	{
		while ($this->isAlphaNumeric($this->peek())) $this->advance();

		$type = TOK_IDENTIFIER;
		$str = substr($this->source, $this->start, $this->current - $this->start);
		if (isset(self::$keywords[$str]))
		{
			$type = self::$keywords[$str];
			$str = null;
		}

		$this->addToken($type, $str);
	}

	private function isAlpha($c)
	{
		return ($c >= 'a' && $c <= 'z') || ($c >= 'A' && $c <= 'Z') || $c == '_';
	}

	private function isAlphaNumeric($c)
	{
		return $this->isAlpha($c) || $this->isDigit($c);
	}
}
