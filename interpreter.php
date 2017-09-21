<?php
require_once('ast.php');

class RuntimeError extends Exception
{
	private $token;

	public function __construct(Token $token, $message)
	{
		parent::__construct($message);
		$this->token = $token;
	}
}

class Interpreter implements VisitorExpr
{
	public function print(Expr $expr)
	{
	}

	public function visitAssignExpr(Assign $expr)
	{
	}

	public function visitBinaryExpr(Binary $expr)
	{
		$left = $this->evaluate($expr->left);
		$right = $this->evaluate($expr->right);

		switch ($expr->operator->type)
		{
			case TOK_PLUS:
				if (is_double($left) && is_double($right))
				{
					return doubleval($left) + doubleval($right);
				}

				if (is_string($left) && is_string($right))
				{
					return $left.''.$right;
				}
				return null;
			case TOK_MINUS:
				$this->checkNumberOperand($expr->operator, $right);
				return doubleval($left) - doubleval($right);
			case TOK_SLASH:
				return doubleval($left) / doubleval($right);
			case TOK_STAR:
				return doubleval($left) * doubleval($right);

			case GREATER:
				return (double)$left > (double)$right;
			case GREATER_EQUAL:
				return (double)$left >= (double)$right;
			case LESS:
				return (double)$left < (double)$right;
			case LESS_EQUAL:
				return (double)$left <= (double)$right;
			case BANG_EQUAL: return !$this->isEqual($left, $right);
			case EQUAL_EQUAL: return $this->isEqual($left, $right);
		}

		return null;
	}

	public function visitCallExpr(Call $expr)
	{
	}
	
	public function visitGetExpr(Get $expr)
	{
	}
	
	public function visitGroupingExpr(Grouping $expr)
	{
		return $this->evaluate($expr->expression);
	}
	
	public function visitLiteralExpr(Literal $expr)
	{
		return $expr->value;
	}
	
	public function visitLogicalExpr(Logical $expr)
	{
	}
	
	public function visitSetExpr(Set $expr)
	{
	}
	
	public function visitSuperExpr(Super $expr)
	{
	}
	
	public function visitThisExpr(This $expr)
	{
	}
	
	public function visitUnaryExpr(Unary $expr)
	{
		$right = $this->evaluate($expr->right);

		switch ($right->operator->type)
		{
			case TOK_MINUS:
				return - doubleval($right);
			case TOK_BANG:
				return !$this->isTruthy($right);
		}

		return null;
	}
	
	public function visitVariableExpr(Variable $expr)
	{
	}

	private function stringify($object)
	{
		if ($object === null) return "nil";

		return (string)$object;
	}

	private function evaluate(Expr $expr)
	{
		return $expr->accept($this);
	}

	private function isTruthy($object)
	{
		if ($object === null) return false;
		if (is_bool($object)) return boolval($object);

		return true;
	}

	private function isEqual($a, $b)
	{
		if ($a === null) return ($b === null);
		return $a == $b;
	}

	private function checkNumberOperand(Token $operator, $operand)
	{
		if (is_double($operand)) return;
		throw new RuntimeError("Operand must be a number");
	}

	public function interpret(Expr $expression)
	{
		try
		{
			$value = $this->evaluate($expression);
			echo $this->stringify($value)."\n";
		}
		catch (RuntimeError $error)
		{
			EPLox::runtimeError($error);
		}
	}
}
