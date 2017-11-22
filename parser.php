<?php
define('FUNC_MAX_ARGS', 8);

class Parser
{
	private $tokens = [];
	private $current = 0;

	public function __construct($tokens)
	{
		$this->tokens = $tokens;
	}

	public function parse()
	{
		$statements = [];
		while (!$this->isAtEnd())
		{
			$statements[] = $this->declaration();
		}

		return $statements;
	}

	private function declaration()
	{
		try {
			if ($this->match(TOK_CLASS)) return $this->classDeclaration();
			if ($this->match(TOK_FUN)) return $this->method("function");
			if ($this->match(TOK_VAR)) return $this->varDeclaration();
			return $this->statement();
		}
		catch (ParseError $error)
		{
			$this->synchronize();
			return null;
		}
	}

	private function classDeclaration()
	{
		$name = $this->consume(TOK_IDENTIFIER, "Expect class name.");
		$this->consume(TOK_LEFT_BRACE, "Expect '{' before class body.");

		$methods = [];
		while (!$this->check(TOK_RIGHT_BRACE) && !$this->isAtEnd())
		{
			$methods[] = $this->method("method");
		}

		$this->consume(TOK_RIGHT_BRACE, "Expect '}' after class body.");

		return new ClassStmt($name, $methods);
	}

	private function method($kind) {
		$name = $this->consume(TOK_IDENTIFIER, "Expect ".$kind." name.");

		$this->consume(TOK_LEFT_PAREN, "Expect '(' after ".$kind." name.");
		$parameters = [];
		if (!$this->check(TOK_RIGHT_PAREN))
		{
			do
			{
				if (count($parameters) >= FUNC_MAX_ARGS)
				{
					$this->error($this->peek(), "Cannot have more than 8 arguments.");
				}

				$parameters[] = $this->consume(TOK_IDENTIFIER, "Expect parameter name.");
			} while ($this->match(TOK_COMMA));
		}
		$this->consume(TOK_RIGHT_PAREN, "Expect ')' after parameters.");

		$this->consume(TOK_LEFT_BRACE, "Expect '{' before ".$kind." body.");
		$body = $this->block();
		return new FunctionStmt($name, $parameters, $body);
	}

	private function varDeclaration()
	{
		$name = $this->consume(TOK_IDENTIFIER, "Expect variable name.");

		$initializer = null;
		if ($this->match(TOK_EQUAL))
		{
			$initializer = $this->expression();
		}

		$this->consume(TOK_SEMICOLON, "Expect ';' after variable declaration.");
		return new VarStmt($name, $initializer);
	}

	private function block()
	{
		$statements = [];

		while (!$this->check(TOK_RIGHT_BRACE) && !$this->isAtEnd())
		{
			$statements[] = $this->declaration();
		}

		$this->consume(TOK_RIGHT_BRACE, "Expect '}' after block.");

		return $statements;
	}

	private function statement()
	{
		if ($this->match(TOK_FOR)) return $this->forStatement();

		if ($this->match(TOK_IF)) return $this->ifStatement();

		if ($this->match(TOK_PRINT)) return $this->printStatement();

		if ($this->match(TOK_RETURN)) return $this->returnStatement();

		if ($this->match(TOK_WHILE)) return $this->whileStatement();

		if ($this->match(TOK_LEFT_BRACE)) return new BlockStmt($this->block());

		return $this->expressionStatement();
	}

	public function returnStatement()
	{
		$keyword = $this->previous();
		$value = null;
		if (!$this->check(TOK_SEMICOLON))
		{
			$value = $this->expression();
		}

		$this->consume(TOK_SEMICOLON, "Expect ';' after return value.");
		return new ReturnStmt($keyword, $value);
	}

	private function whileStatement()
	{
		$this->consume(TOK_LEFT_PAREN, "Expect '(' after 'while'.");
		$condition = $this->expression();
		$this->consume(TOK_RIGHT_PAREN, "Expect ')' after condition.");
		$body = $this->statement();

		return new WhileStmt($condition, $body);
	}

	private function forStatement()
	{
		$this->consume(TOK_LEFT_PAREN, "Expect '(' after 'for'.");

		if ($this->match(TOK_SEMICOLON))
		{
			$initializer = null;
		}
		else if ($this->match(TOK_VAR))
		{
			$initializer = $this->varDeclaration();
		}
		else
		{
			$initializer = $this->expressionStatement();
		}

		$condition = null;
		if (!$this->check(TOK_SEMICOLON))
		{
			$condition = $this->expression();
		}
		$this->consume(TOK_SEMICOLON, "Expect ';' after loop condition.");

		$increment = null;
		if (!$this->check(TOK_RIGHT_PAREN))
		{
			$increment = $this->expression();
		}
		$this->consume(TOK_RIGHT_PAREN, "Expect ')' after for clauses.");

		$body = $this->statement();

		if ($increment !== null)
		{
			$body = new BlockStmt([
				$body,
				new ExpressionStmt($increment)
			]);
		}

		if ($condition === null) $condition = new LiteralExpr(true);
		$body = new WhileStmt($condition, $body);

		if ($initializer !== null)
		{
			$body = new BlockStmt([
				$initializer,
				$body
			]);
		}

		return $body;
	}

	private function expressionStatement()
	{
		$expr = $this->expression();
		$this->consume(TOK_SEMICOLON, "Expect ';' after expression.");
		return new ExpressionStmt($expr);
	}

	private function printStatement()
	{
		$value = $this->expression();
		$this->consume(TOK_SEMICOLON, "Expect ';' after value.");
		return new PrintStmt($value);
	}

	private function ifStatement()
	{
		$this->consume(TOK_LEFT_PAREN, "Expect '(' after 'if'.");
		$condition = $this->expression();
		$this->consume(TOK_RIGHT_PAREN, "Expect ')' after if condition."); 

		$thenBranch = $this->statement();
		$elseBranch = null;
		if ($this->match(TOK_ELSE))
		{
			$elseBranch = $this->statement();
		}

		return new IfStmt($condition, $thenBranch, $elseBranch);
	}

	private function expression()
	{
		return $this->assignment();
	}

	private function assignment()
	{
		$expr = $this->or();

		if ($this->match(TOK_EQUAL))
		{
			$equals = $this->previous();
			$value = $this->assignment();

			if ($expr instanceof VariableExpr)
			{
				$name = $expr->name;
				return new AssignExpr($name, $value);
			}
			else if ($expr instanceof GetExpr)
			{
				return new SetExpr($expr->object, $expr->name, $value);
			}
		}

		return $expr;
	}

	private function or()
	{
		$expr = $this->and();

		while ($this->match(TOK_OR))
		{
			$operator = $this->previous();
			$right = $this->and();
			$expr = new LogicalExpr($expr, $operator, $right);
		}
		
		return $expr;
	}

	private function and()
	{
		$expr = $this->equality();

		while ($this->match(TOK_AND))
		{
			$operator = $this->previous();
			$right = $this->equality();
			$expr = new LogicalExpr($expr, $operator, $right);
		}

		return $expr;
	}

	private function equality()
	{
		$expr = $this->comparison();

		while ($this->match(TOK_BANG_EQUAL, TOK_EQUAL_EQUAL))
		{
			$operator = $this->previous();
			$right = $this->comparison();
			$expr = new BinaryExpr($expr, $operator, $right);
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
			$expr = new BinaryExpr($expr, $operator, $right);
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
			$expr = new BinaryExpr($expr, $operator, $right);
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
			$expr = new BinaryExpr($expr, $operator, $right);
		}

		return $expr;
	}

	private function unary()
	{
		if ($this->match(TOK_MINUS, TOK_BANG))
		{
			$operator = $this->previous();
			$right = $this->unary();
			return new UnaryExpr($operator, $right);
		}

		return $this->call();
	}

	private function call()
	{
		$expr = $this->primary();

		while (true)
		{
			if ($this->match(TOK_LEFT_PAREN))
			{
				$expr = $this->finishCall($expr);
			}
			else if ($this->match(TOK_DOT))
			{
				$name = $this->consume(TOK_IDENTIFIER, "Expect property name after '.'.");
				$expr = new GetExpr($expr, $name);
			}
			else
			{
				break;
			}
		}

		return $expr;
	}

	private function finishCall(Expr $callee)
	{
		$arguments = [];

		if (!$this->check(TOK_RIGHT_PAREN))
		{
			do {
				if (count($arguments) >= FUNC_MAX_ARGS)
				{
					$this->error($this->peek(), "Cannot have more than 8 arguments.");
				}

				$arguments[] = $this->expression();
			} while ($this->match(TOK_COMMA));
		}

		$paren = $this->consume(TOK_RIGHT_PAREN, "Expect ')' after arguments.");

		return new CallExpr($callee, $paren, $arguments);
	}

	private function primary()
	{
		if ($this->match(TOK_FALSE)) return new LiteralExpr(false);
		if ($this->match(TOK_TRUE)) return new LiteralExpr(true);
		if ($this->match(TOK_NIL)) return new LiteralExpr(null);

		if ($this->match(TOK_NUMBER, TOK_STRING))
		{
			return new LiteralExpr($this->previous()->literal);
		}

		if ($this->match(TOK_THIS)) return new ThisExpr($this->previous());

		if ($this->match(TOK_IDENTIFIER))
		{
			return new VariableExpr($this->previous());
		}

		if ($this->match(TOK_LEFT_PAREN))
		{
			$expr = $this->expression();
			$this->consume(TOK_RIGHT_PAREN, "Expect ')' after expression.");
			return new GroupingExpr($expr);
		}

		throw $this->error($this->peek(), "Expect expression.");
	}

	private function consume($tokenType, $message)
	{
		if ($this->check($tokenType)) return $this->advance();

		throw $this->error($this->peek(), $message);
		
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
