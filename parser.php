<?php
require_once('token.php');
require_once('ast.php');
require_once('eplox.php');

class Parser
{
	private $tokens = [];
	private $current = 0;

	public function __construct($tokens)
	{
		$this->tokens = $tokens;
	}

	function parse()
	{
		try
		{
			return $this->expression();
		}
		catch (ParseError $err)
		{
			return null;
		}
	}

	private function expression()
	{
		return $this->equality();
	}

	private function equality()
	{
		$expr = $this->comparison();

		while ($this->match(TOK_BANG_EQUAL, TOK_EQUAL_EQUAL))
		{
			$operator = $this->previous();
			$right = $this->comparison();
			$expr = new Binary($expr, $operator, $right);
		}

		return $expr;
	}

	private function comparison()
	{
		$expr = $this->addition();

		while ($this->match(TOK_GREATER, TOK_GREATER_EQUAL, TOK_LESS, TOK_LESS_EQUAL))
		{
			$operator = $this->previous();
			$right = $this->addition();
			$expr = new Binary($expr, $operator, $right);
		}

		return $expr;
	}

	private function addition()
	{
		$expr = $this->multiplication();

		while ($this->match(TOK_MINUS, TOK_PLUS))
		{
			$operator = $this->previous();
			$right = $this->multiplication();
			$expr = new Binary($expr, $operator, $right);
		}

		return $expr;
	}

	private function multiplication()
	{
		$expr = $this->unary();

		while ($this->match(TOK_SLASH, TOK_STAR))
		{
			$operator = $this->previous();
			$right = $this->unary();
			$expr = new Binary($expr, $operator, $right);
		}

		return $expr;
	}

	private function unary()
	{
		if ($this->match(TOK_MINUS, TOK_BANG))
		{
			$operator = $this->previous();
			$right = $this->unary();
			return new Unary($operator, $right);
		}

		return $this->primary();
	}

	private function primary()
	{
		if ($this->match(TOK_FALSE)) return new Literal(false);
		if ($this->match(TOK_TRUE)) return new Literal(true);
		if ($this->match(TOK_NIL)) return new Literal(null);

		if ($this->match(TOK_NUMBER, TOK_STRING))
		{
			return new Literal($this->previous()->literal);
		}

		if ($this->match(TOK_LEFT_PAREN))
		{
			$expr = $this->expression();
			$this->consume(TOK_RIGHT_PAREN, "Expect ')' after expression.");
			return new Grouping($expr);
		}

		throw $this->error($this->peek(), "Expect expression.");
	}

	private function consume($tokenType, $message)
	{
		if ($this->check($tokenType)) return $this->advance();

		throw $this->error(peek(), $message);
		
	}

	private function synchronize()
	{
		$this->advance();

		while (!$this->isAtEnd())
		{
			if ($this->previous()->type == TOK_SEMICOLON) return;

			switch ($this->peek()->type)
			{
			case TOK_CLASS:
			case TOK_FUN:
			case TOK_VAR:
			case TOK_FOR:
			case TOK_IF:
			case TOK_WHILE:
			case TOK_PRINT:
			case TOK_RETURN:
				return;
			}

			$this->advance();
		}
	}

	private function match(...$tokenTypes)
	{
		foreach ($tokenTypes as $tokenType)
		{
			if ($this->check($tokenType))
			{
				$this->advance();
				return true;
			}
		}

		return false;
	}

	private function check($tokenType)
	{
		if ($this->isAtEnd()) return false;
		return $this->peek()->type == $tokenType;
	}

	private function advance()
	{
		if (!$this->isAtEnd()) $this->current++;
		return $this->previous();
	}

	private function isAtEnd()
	{
		return $this->peek()->type == TOK_EOF;
	}

	private function peek()
	{
		return $this->tokens[$this->current];
	}

	private function previous()
	{
		return $this->tokens[$this->current - 1];
	}

	private function error(Token $token, $message)
	{
		EPlox::error($token, $message);

		return new ParseError();
	}
}

//class ParseError extends ErrorException {}
