<?php
require_once('ast.php');

class AstPrinter implements VisitorExpr
{
	public function print(Expr $expr)
	{
		return $expr->accept($this);
	}

	public function visitAssignExpr(AssignExpr $expr)
	{
		return 'assign';
	}

	public function visitBinaryExpr(BinaryExpr $expr)
	{
		return $expr->left->accept($this).' '.$expr->operator.' '.$expr->right->accept($this);
	}

	public function visitCallExpr(CallExpr $expr)
	{
		return 'call';
	}
	
	public function visitGetExpr(GetExpr $expr)
	{
		return 'get';
	}
	
	public function visitGroupingExpr(GroupingExpr $expr)
	{
		return '('.$expr->expression->accept($this).')';
	}
	
	public function visitLiteralExpr(LiteralExpr $expr)
	{
		return $expr->value;
	}
	
	public function visitLogicalExpr(LogicalExpr $expr)
	{
		return 'logical';
	}
	
	public function visitSetExpr(SetExpr $expr)
	{
		return 'set';
	}
	
	public function visitSuperExpr(SuperExpr $expr)
	{
		return 'super';
	}
	
	public function visitThisExpr(ThisExpr $expr)
	{
		return 'this';
	}
	
	public function visitUnaryExpr(UnaryExpr $expr)
	{
		return $expr->operator.$expr->right->accept($this);
	}
	
	public function visitVariableExpr(VariableExpr $expr)
	{
		return 'var';
	}
}
