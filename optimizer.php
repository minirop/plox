<?php
require_once('ast.php');

class Optimizer implements VisitorExpr, VisitorStmt
{
	public function __construct()
	{
	}

	public function optimize(Array $statements)
	{
		try
		{
			$optimized = [];
			foreach ($statements as $statement)
			{
				$optimized[] = $this->execute($statement);
			}
			return $optimized;
		}
		catch (RuntimeError $error)
		{
			EPLox::runtimeError($error);
		}
	}

	public function visitAssignExpr(AssignExpr $expr)
	{
		return $expr;
	}

	public function visitBinaryExpr(BinaryExpr $expr)
	{
		$left = $expr->left->accept($this);
		$right = $expr->right->accept($this);

		if ($left instanceof LiteralExpr && $right instanceof LiteralExpr)
		{
			switch ($expr->operator->type)
			{
				case TOK_PLUS:
					return new LiteralExpr($left->value + $right->value);
				case TOK_MINUS:
					return new LiteralExpr($left->value - $right->value);
				case TOK_STAR:
					return new LiteralExpr($left->value * $right->value);
				case TOK_SLASH:
					return new LiteralExpr($left->value / $right->value);
			}
		}

		$expr->left = $left;
		$expr->right = $right;

		return $expr;
	}

	public function visitCallExpr(CallExpr $expr)
	{
		return $expr;
	}

	public function visitGetExpr(GetExpr $expr)
	{
		return $expr;
	}

	public function visitGroupingExpr(GroupingExpr $expr)
	{
		$expression = $expr->expression->accept($this);

		if ($expression instanceof LiteralExpr)
		{
			return $expression;
		}

		return new GroupingExpr($expression);
	}

	public function visitLiteralExpr(LiteralExpr $expr)
	{
		return $expr;
	}

	public function visitLogicalExpr(LogicalExpr $expr)
	{
		return $expr;
	}

	public function visitSetExpr(SetExpr $expr)
	{
		return $expr;
	}

	public function visitSuperExpr(SuperExpr $expr)
	{
		return $expr;
	}

	public function visitThisExpr(ThisExpr $expr)
	{
		return $expr;
	}

	public function visitUnaryExpr(UnaryExpr $expr)
	{
		$right = $expr->right->accept($this);

		if ($right instanceof LiteralExpr && $expr->operator->type === TOK_MINUS)
		{
			$expr = new LiteralExpr(-$right->value);
		}
		
		return $expr;
	}

	public function visitVariableExpr(VariableExpr $expr)
	{
		return $expr;
	}

	public function visitBlockStmt(BlockStmt $stmt)
	{
		return $stmt;
	}

	public function visitClassStmt(ClassStmt $stmt)
	{
		return $stmt;
	}

	public function visitExpressionStmt(ExpressionStmt $stmt)
	{
		return new ExpressionStmt($stmt->expression->accept($this));
	}

	public function visitFunctionStmt(FunctionStmt $stmt)
	{
		return $stmt;
	}

	public function visitIfStmt(IfStmt $stmt)
	{
		return $stmt;
	}

	public function visitPrintStmt(PrintStmt $stmt)
	{
		return $stmt;
	}

	public function visitReturnStmt(ReturnStmt $stmt)
	{
		return $stmt;
	}

	public function visitVarStmt(VarStmt $stmt)
	{
		return $stmt;
	}

	public function visitWhileStmt(WhileStmt $stmt)
	{
		return $stmt;
	}

	private function execute(Stmt $stmt)
	{
		return $stmt->accept($this);
	}
}
