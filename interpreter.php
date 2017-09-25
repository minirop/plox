<?php
require_once('ast.php');
require_once('environment.php');

class RuntimeError extends Exception
{
	private $token;

	public function __construct(Token $token, $message)
	{
		parent::__construct($message);
		$this->token = $token;
	}
}

class Interpreter implements VisitorExpr, VisitorStmt
{
	private $environment;

	public function __construct()
	{
		$this->environment = new Environment();
	}

	public function interpret(Array $statements)
	{
		try
		{
			foreach ($statements as $statement)
			{
				$this->execute($statement);
			}
		}
		catch (RuntimeError $error)
		{
			EPLox::runtimeError($error);
		}
	}

	public function print(Expr $expr)
	{
		echo "lol\n";
	}

	public function visitAssignExpr(AssignExpr $expr)
	{
		$value = $this->evaluate($expr->value);
		$this->environment->assign($expr->name, $value);

		return $value;
	}

	public function visitBinaryExpr(BinaryExpr $expr)
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

			case TOK_GREATER:
				return (double)$left > (double)$right;
			case TOK_GREATER_EQUAL:
				return (double)$left >= (double)$right;
			case TOK_LESS:
				return (double)$left < (double)$right;
			case TOK_LESS_EQUAL:
				return (double)$left <= (double)$right;
			case TOK_BANG_EQUAL: return !$this->isEqual($left, $right);
			case TOK_EQUAL_EQUAL: return $this->isEqual($left, $right);
		}

		return null;
	}

	public function visitCallExpr(CallExpr $expr)
	{
	}
	
	public function visitGetExpr(GetExpr $expr)
	{
	}
	
	public function visitGroupingExpr(GroupingExpr $expr)
	{
		return $this->evaluate($expr->expression);
	}
	
	public function visitLiteralExpr(LiteralExpr $expr)
	{
		return $expr->value;
	}
	
	public function visitLogicalExpr(LogicalExpr $expr)
	{
		$left = $this->evaluate($expr->left);

		if ($expr->operator->type == TOK_OR)
		{
			if ($this->isTruthy($left)) return $left;
		}
		else
		{
			if (!$this->isTruthy($left)) return $left;
		}

		return $this->evaluate($expr->right);
	}
	
	public function visitSetExpr(SetExpr $expr)
	{
	}
	
	public function visitSuperExpr(SuperExpr $expr)
	{
	}
	
	public function visitThisExpr(ThisExpr $expr)
	{
	}
	
	public function visitUnaryExpr(UnaryExpr $expr)
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
	
	public function visitVariableExpr(VariableExpr $expr)
	{
		return $this->environment->get($expr->name);
	}

	public function visitBlockStmt(BlockStmt $stmt)
	{
		$this->executeBlock($stmt->statements, new Environment($this->environment));
	}

	public function visitExpressionStmt(ExpressionStmt $stmt)
	{
		$this->evaluate($stmt->expression);
	}

	public function visitIfStmt(IfStmt $stmt)
	{
		if ($this->isTruthy($this->evaluate($stmt->condition)))
		{
			$this->execute($stmt->thenBranch);
		}
		else if ($stmt->elseBranch !== null)
		{
			$this->execute($stmt->elseBranch);
		}
	}

	public function visitPrintStmt(PrintStmt $stmt)
	{
		$value = $this->evaluate($stmt->expression);
		print($this->stringify($value)."\n");
	}

	public function visitVarStmt(VarStmt $stmt)
	{
		$value = null;
		if ($stmt->initializer !== null)
		{
			$value = $this->evaluate($stmt->initializer);
		}

		$this->environment->define($stmt->name->literal, $value);
	}

	public function visitWhileStmt(WhileStmt $stmt)
	{
		while ($this->isTruthy($this->evaluate($stmt->condition)))
		{
			$this->execute($stmt->body);
		}
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

	private function execute(Stmt $stmt)
	{
		$stmt->accept($this);
	}

	private function executeBlock(Array $statements, Environment $environment)
	{
		$previous = $this->environment;
		try
		{
			$this->environment = $environment;

			foreach ($statements as $statement)
			{
				$this->execute($statement);
			}
		}
		finally
		{
			$this->environment = $previous;
		}
	}
}
