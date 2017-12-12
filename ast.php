<?php
require_once('token.php');

abstract class Expr
{
	abstract public function accept($visitor);
}

interface VisitorExpr
{
	public function visitAssignExpr(AssignExpr $expr);
	public function visitBinaryExpr(BinaryExpr $expr);
	public function visitCallExpr(CallExpr $expr);
	public function visitGetExpr(GetExpr $expr);
	public function visitGroupingExpr(GroupingExpr $expr);
	public function visitLiteralExpr(LiteralExpr $expr);
	public function visitLogicalExpr(LogicalExpr $expr);
	public function visitSetExpr(SetExpr $expr);
	public function visitSuperExpr(SuperExpr $expr);
	public function visitThisExpr(ThisExpr $expr);
	public function visitUnaryExpr(UnaryExpr $expr);
	public function visitVariableExpr(VariableExpr $expr);
}

class AssignExpr extends Expr
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

class BinaryExpr extends Expr
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

class CallExpr extends Expr
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

class GetExpr extends Expr
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

class GroupingExpr extends Expr
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

class LiteralExpr extends Expr
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

class LogicalExpr extends Expr
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

class SetExpr extends Expr
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

class SuperExpr extends Expr
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

class ThisExpr extends Expr
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

class UnaryExpr extends Expr
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

class VariableExpr extends Expr
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

abstract class Stmt
{
	abstract public function accept($visitor);
}

interface VisitorStmt
{
	public function visitBlockStmt(BlockStmt $stmt);
	public function visitClassStmt(ClassStmt $stmt);
	public function visitExpressionStmt(ExpressionStmt $stmt);
	public function visitFunctionStmt(FunctionStmt $stmt);
	public function visitIfStmt(IfStmt $stmt);
	public function visitPrintStmt(PrintStmt $stmt);
	public function visitReturnStmt(ReturnStmt $stmt);
	public function visitVarStmt(VarStmt $stmt);
	public function visitWhileStmt(WhileStmt $stmt);
}

class BlockStmt extends Stmt
{
	public function __construct(array $statements)
	{
		$this->statements = $statements;
	}

	public function accept($visitor)
	{
		return $visitor->visitBlockStmt($this);
	}

	public $statements;
}

class ClassStmt extends Stmt
{
	public function __construct(Token $name, $superclass, array $methods)
	{
		$this->name = $name;
		$this->superclass = $superclass;
		$this->methods = $methods;
	}

	public function accept($visitor)
	{
		return $visitor->visitClassStmt($this);
	}

	public $name;
	public $superclass;
	public $methods;
}

class ExpressionStmt extends Stmt
{
	public function __construct(Expr $expression)
	{
		$this->expression = $expression;
	}

	public function accept($visitor)
	{
		return $visitor->visitExpressionStmt($this);
	}

	public $expression;
}

class FunctionStmt extends Stmt
{
	public function __construct(Token $name, array $parameters, array $body)
	{
		$this->name = $name;
		$this->parameters = $parameters;
		$this->body = $body;
	}

	public function accept($visitor)
	{
		return $visitor->visitFunctionStmt($this);
	}

	public $name;
	public $parameters;
	public $body;
}

class IfStmt extends Stmt
{
	public function __construct(Expr $condition, Stmt $thenBranch, $elseBranch)
	{
		$this->condition = $condition;
		$this->thenBranch = $thenBranch;
		$this->elseBranch = $elseBranch;
	}

	public function accept($visitor)
	{
		return $visitor->visitIfStmt($this);
	}

	public $condition;
	public $thenBranch;
	public $elseBranch;
}

class PrintStmt extends Stmt
{
	public function __construct(Expr $expression)
	{
		$this->expression = $expression;
	}

	public function accept($visitor)
	{
		return $visitor->visitPrintStmt($this);
	}

	public $expression;
}

class ReturnStmt extends Stmt
{
	public function __construct(Token $keyword, Expr $value)
	{
		$this->keyword = $keyword;
		$this->value = $value;
	}

	public function accept($visitor)
	{
		return $visitor->visitReturnStmt($this);
	}

	public $keyword;
	public $value;
}

class VarStmt extends Stmt
{
	public function __construct(Token $name, Expr $initializer)
	{
		$this->name = $name;
		$this->initializer = $initializer;
	}

	public function accept($visitor)
	{
		return $visitor->visitVarStmt($this);
	}

	public $name;
	public $initializer;
}

class WhileStmt extends Stmt
{
	public function __construct(Expr $condition, Stmt $body)
	{
		$this->condition = $condition;
		$this->body = $body;
	}

	public function accept($visitor)
	{
		return $visitor->visitWhileStmt($this);
	}

	public $condition;
	public $body;
}

