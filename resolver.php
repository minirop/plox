<?php
require_once('ast.php');

define('TYPE_NONE', 0);
define('TYPE_FUNCTION', 1);
define('TYPE_METHOD', 2);
define('TYPE_INITIALIZER', 3);

define('TYPE_CLASS', 100);

class Resolver implements VisitorExpr, VisitorStmt
{
	private $interpreter;
	private $scopes;
	private $currentFunction = TYPE_NONE;
	private $currentClass = TYPE_NONE;

	public function __construct(Interpreter $interpreter) {
		$this->interpreter = $interpreter;
		$this->scopes = [];
	}
	public function visitAssignExpr(AssignExpr $expr)
	{
		$this->resolve($expr->value);
		$this->resolveLocal($expr, $expr->name);
	}

	public function visitBinaryExpr(BinaryExpr $expr)
	{
		$this->resolve($expr->left);
		$this->resolve($expr->right);
	}

	public function visitCallExpr(CallExpr $expr)
	{
		$this->resolve($expr->callee);

		foreach ($expr->arguments as $argument)
		{
			$this->resolve($argument);
		}
	}

	public function visitGetExpr(GetExpr $expr)
	{
		$this->resolve($expr->object);
	}

	public function visitGroupingExpr(GroupingExpr $expr)
	{
		$this->resolve($expr->expression);
	}

	public function visitLiteralExpr(LiteralExpr $expr)
	{
	}

	public function visitLogicalExpr(LogicalExpr $expr)
	{
		$this->resolve($expr->left);
		$this->resolve($expr->right);
	}

	public function visitSetExpr(SetExpr $expr)
	{
		$this->resolve($expr->value);
		$this->resolve($expr->object);
	}

	public function visitSuperExpr(SuperExpr $expr)
	{
		
	}

	public function visitThisExpr(ThisExpr $expr)
	{
		if ($this->currentClass == TYPE_NONE)
		{
			Plox::error($expr->keyword, "Cannot use 'this' outside of a class.");
			return null;
		}

		$this->resolveLocal($expr, $expr->keyword);
	}

	public function visitUnaryExpr(UnaryExpr $expr)
	{
		$this->resolve($expr->right);
	}

	public function visitVariableExpr(VariableExpr $expr)
	{
		if (count($this->scopes) && isset($this->scopes[0][$expr->name->literal]) && $this->scopes[0][$expr->name->literal] === false)
		{
			EPLox::error($expr->name, "Cannot read local variable in its own initializer.");
		}

		$this->resolveLocal($expr, $expr->name);
	}

	public function visitBlockStmt(BlockStmt $stmt)
	{
		$this->beginScope();
		$this->resolve($stmt->statements);
		$this->endScope();
	}

	public function visitClassStmt(ClassStmt $stmt)
	{
		$this->declare($stmt->name);
		$this->define($stmt->name);

		$enclosingClass = $this->currentClass;
		$this->currentClass = TYPE_CLASS;

		$this->beginScope();
		$this->scopes[count($this->scopes)]["this"] = true;

		foreach ($stmt->methods as $method)
		{
			$declaration = TYPE_METHOD;
			if ($method->name->literal === 'init')
			{
				$declaration = TYPE_INITIALIZER;
			}
			$this->resolveFunction($method, $declaration);
		}

		$this->endScope();

		$this->currentClass = $enclosingClass;
	}

	public function visitExpressionStmt(ExpressionStmt $stmt)
	{
		$this->resolve($stmt->expression);
	}

	public function visitFunctionStmt(FunctionStmt $stmt)
	{
		$this->declare($stmt->name);
		$this->define($stmt->name);

		$this->resolveFunction($stmt, TYPE_FUNCTION);
	}

	public function visitIfStmt(IfStmt $stmt)
	{
		$this->resolve($stmt->condition);
		$this->resolve($stmt->thenBranch);

		if ($stmt->elseBranch !== null)
			$this->resolve($stmt->elseBranch);
	}

	public function visitPrintStmt(PrintStmt $stmt)
	{
		$this->resolve($stmt->expression);
	}

	public function visitReturnStmt(ReturnStmt $stmt)
	{
		if ($this->currentFunction === TYPE_NONE)
		{
			EPlox::error($stmt->keyword, "Cannot return from top-level code.");
		}

		if ($stmt->value !== null)
		{
			if ($this->currentFunction === TYPE_INITIALIZER)
			{
				Plox::error($stmt->keyword, "Cannot return a value from an initializer.");
			}

			$this->resolve($stmt->value);
		}
	}

	public function visitVarStmt(VarStmt $stmt)
	{
		$this->declare($stmt->name);

		if ($stmt->initializer !== null)
		{
			$this->resolve($stmt->initializer);
		}

		$this->define($stmt->name);
	}

	public function visitWhileStmt(WhileStmt $stmt)
	{
		$this->resolve($stmt->condition);
		$this->resolve($stmt->body);
	}

	public function resolve($value)
	{
		if (is_array($value))
		{
			foreach ($value as $statement)
			{
				$this->resolve($statement);
			}
		}
		else if ($value instanceof Stmt || $value instanceof Expr)
		{
			$value->accept($this);
		}
	}

	private function resolveLocal(Expr $expr, Token $name)
	{
		$depth = 0;
		foreach ($this->scopes as $scope)
		{
			if (isset($scope[$name->literal]))
			{
				$this->interpreter->resolve($expr, $depth);
				return;
			}
			$depth++;
		}
	}

	private function resolveFunction(FunctionStmt $function, $functionType)
	{
		$enclosingFunction = $this->currentFunction;
		$this->currentFunction = $functionType;
		$this->beginScope();
		foreach ($function->parameters as $param)
		{
			$this->declare($param);
			$this->define($param);
		}

		$this->resolve($function->body);
		$this->endScope();

		$this->currentFunction = $enclosingFunction;
	}

	private function beginScope()
	{
		array_unshift($this->scopes, []);
	}

	private function endScope()
	{
		array_shift($this->scopes);
	}

	private function declare(Token $name)
	{
		if (count($this->scopes) == 0) return;

		if (isset($this->scopes[0][$name->literal]))
		{
			EPLox::error($name, "Variable with this name already declared in this scope.");
		}

		$this->scopes[0][$name->literal] = false;
	}

	private function define(Token $name)
	{
		if (count($this->scopes) == 0) return;
		$this->scopes[0][$name->literal] = true;
	}
}
