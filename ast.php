<?php
require_once('token.php');

abstract class Expr
{
	abstract public function accept($visitor);
}

interface VisitorExpr
{
	public function visitAssignExpr(Assign $expr);
	public function visitBinaryExpr(Binary $expr);
	public function visitCallExpr(Call $expr);
	public function visitGetExpr(Get $expr);
	public function visitGroupingExpr(Grouping $expr);
	public function visitLiteralExpr(Literal $expr);
	public function visitLogicalExpr(Logical $expr);
	public function visitSetExpr(Set $expr);
	public function visitSuperExpr(Super $expr);
	public function visitThisExpr(This $expr);
	public function visitUnaryExpr(Unary $expr);
	public function visitVariableExpr(Variable $expr);
}

class Assign extends Expr
{
	public function __construct(Token $name, Expr $value)
	{
		$this->name = $name;
		$this->value = $value;
	}

	public function accept($visitor)
	{
		return $visitor->visitAssignExpr($this);
	}

	public $name;
	public $value;
}

class Binary extends Expr
{
	public function __construct(Expr $left, Token $operator, Expr $right)
	{
		$this->left = $left;
		$this->operator = $operator;
		$this->right = $right;
	}

	public function accept($visitor)
	{
		return $visitor->visitBinaryExpr($this);
	}

	public $left;
	public $operator;
	public $right;
}

class Call extends Expr
{
	public function __construct(Expr $callee, Token $paren, array $arguments)
	{
		$this->callee = $callee;
		$this->paren = $paren;
		$this->arguments = $arguments;
	}

	public function accept($visitor)
	{
		return $visitor->visitCallExpr($this);
	}

	public $callee;
	public $paren;
	public $arguments;
}

class Get extends Expr
{
	public function __construct(Expr $object, Token $name)
	{
		$this->object = $object;
		$this->name = $name;
	}

	public function accept($visitor)
	{
		return $visitor->visitGetExpr($this);
	}

	public $object;
	public $name;
}

class Grouping extends Expr
{
	public function __construct(Expr $expression)
	{
		$this->expression = $expression;
	}

	public function accept($visitor)
	{
		return $visitor->visitGroupingExpr($this);
	}

	public $expression;
}

class Literal extends Expr
{
	public function __construct($value)
	{
		$this->value = $value;
	}

	public function accept($visitor)
	{
		return $visitor->visitLiteralExpr($this);
	}

	public $value;
}

class Logical extends Expr
{
	public function __construct(Expr $left, Token $operator, Expr $right)
	{
		$this->left = $left;
		$this->operator = $operator;
		$this->right = $right;
	}

	public function accept($visitor)
	{
		return $visitor->visitLogicalExpr($this);
	}

	public $left;
	public $operator;
	public $right;
}

class Set extends Expr
{
	public function __construct(Expr $object, Token $name, Expr $value)
	{
		$this->object = $object;
		$this->name = $name;
		$this->value = $value;
	}

	public function accept($visitor)
	{
		return $visitor->visitSetExpr($this);
	}

	public $object;
	public $name;
	public $value;
}

class Super extends Expr
{
	public function __construct(Token $keyword, Token $method)
	{
		$this->keyword = $keyword;
		$this->method = $method;
	}

	public function accept($visitor)
	{
		return $visitor->visitSuperExpr($this);
	}

	public $keyword;
	public $method;
}

class This extends Expr
{
	public function __construct(Token $keyword)
	{
		$this->keyword = $keyword;
	}

	public function accept($visitor)
	{
		return $visitor->visitThisExpr($this);
	}

	public $keyword;
}

class Unary extends Expr
{
	public function __construct(Token $operator, Expr $right)
	{
		$this->operator = $operator;
		$this->right = $right;
	}

	public function accept($visitor)
	{
		return $visitor->visitUnaryExpr($this);
	}

	public $operator;
	public $right;
}

class Variable extends Expr
{
	public function __construct(Token $name)
	{
		$this->name = $name;
	}

	public function accept($visitor)
	{
		return $visitor->visitVariableExpr($this);
	}

	public $name;
}

