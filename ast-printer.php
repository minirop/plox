<?php
require_once('ast.php');

class AstPrinter implements VisitorExpr
{
	public function print(Expr $expr)
	{
		return $expr->accept($this);
	}

	public function visitAssignExpr(Assign $expr)
	{
		return 'assign';
	}

	public function visitBinaryExpr(Binary $expr)
	{
		return $expr->left->accept($this).' '.$expr->operator.' '.$expr->right->accept($this);
	}

	public function visitCallExpr(Call $expr)
	{
		return 'call';
	}
	
	public function visitGetExpr(Get $expr)
	{
		return 'get';
	}
	
	public function visitGroupingExpr(Grouping $expr)
	{
		return '('.$expr->expression->accept($this).')';
	}
	
	public function visitLiteralExpr(Literal $expr)
	{
		return $expr->value;
	}
	
	public function visitLogicalExpr(Logical $expr)
	{
		return 'logical';
	}
	
	public function visitSetExpr(Set $expr)
	{
		return 'set';
	}
	
	public function visitSuperExpr(Super $expr)
	{
		return 'super';
	}
	
	public function visitThisExpr(This $expr)
	{
		return 'this';
	}
	
	public function visitUnaryExpr(Unary $expr)
	{
		return $expr->operator.$expr->right->accept($this);
	}
	
	public function visitVariableExpr(Variable $expr)
	{
		return 'var';
	}
}
