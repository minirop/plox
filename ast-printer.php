<?php
require_once('ast.php');

class AstPrinter implements VisitorExpr, VisitorStmt
{
	public function print(Stmt $stmt)
	{
		return $stmt->accept($this);
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

	public function visitBlockStmt(BlockStmt $stmt)
	{
		return 'block';
	}

	public function visitClassStmt(ClassStmt $stmt)
	{
		return 'class';
	}
	
	public function visitExpressionStmt(ExpressionStmt $stmt)
	{
		return $stmt->expression->accept($this);
	}
	
	public function visitFunctionStmt(FunctionStmt $stmt)
	{
		return 'function';
	}
	
	public function visitIfStmt(IfStmt $stmt)
	{
		return 'if';
	}
	
	public function visitPrintStmt(PrintStmt $stmt)
	{
		return 'print';
	}
	
	public function visitReturnStmt(ReturnStmt $stmt)
	{
		return 'return';
	}
	
	public function visitVarStmt(VarStmt $stmt)
	{
		return 'var';
	}
	
	public function visitWhileStmt(WhileStmt $stmt)
	{
		return 'while';
	}
}
